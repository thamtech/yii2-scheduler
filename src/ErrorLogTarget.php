<?php
/**
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler;

use yii\log\Target;

/**
 * ErrorLogTarget is a custom log target meant to capture a fatal scheduler
 * error and pass it to the current task runner.
 *
 * This target is typically not enabled. It is enabled by a
 * Task::EVENT_BEFORE_RUN error handler set in [[Module::bootstrap()]], and
 * disabled by a Task::EVENT_AFTER_RUN error handler set in the same place.
 *
 * The primary purpose of this log target is to pass a fatal error on to the
 * [[taskRunner]].
 *
 * @author Tyler Ham <tyler@thamtech.com>
 */
class ErrorLogTarget extends Target
{
    /**
     * @var TaskRunner
     */
    public $taskRunner;

    /**
     * @var bool whether [[collect()]] has been called with $final=true yet.
     */
    public $final = false;

    /**
     * @var array exportable messages
     */
    protected $exportableMessages = [];

    /**
     * {{@inheritdoc}}
     */
    public function export()
    {
        // we only care about the most recent error
        $lastMessage = array_pop($this->messages);
        $this->exportableMessages = [$lastMessage];

        if (!$this->final) {
            // We will wait until final to handle the error in case more errors
            // are logged.
            return;
        }

        $error = $lastMessage[0];
        if ($this->taskRunner) {
            $this->taskRunner->handleError(
                $error->getCode(),
                $error->getMessage(),
                $error->getFile(),
                $error->getLine());
        }
    }

    /**
     * {{@inheritdoc}}
     */
    public function collect($messages, $final)
    {
        $this->final = $this->final || $final;
        $messages = array_merge($this->exportableMessages, $messages);
        parent::collect($messages, $final);
    }

    /**
     * {{@inheritdoc}}
     */
    protected function getContextMessage($message)
    {
        // we don't want a context message to be appended, so we just return
        // an empty string
        return '';
    }

    /**
     * {{@inheritdoc}}
     *
     * Only include ErrorExceptions.
     */
    public static function filterMessages($messages, $levels = 0, $categories = [], $except = [])
    {
        $messages = parent::filterMessages($messages, $levels, $categories, $except);
        foreach ($messages as $i => $message) {
            if (!($message[0] instanceof \yii\base\ErrorException)) {
                unset($messages[$i]);
            }
        }
        return $messages;
    }
}
