<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler;

use Yii;
use thamtech\scheduler\events\TaskEvent;
use thamtech\scheduler\models\SchedulerLog;
use thamtech\scheduler\models\SchedulerTask;

/**
 * Class TaskRunner
 *
 * @property \thamtech\scheduler\Task $task
 */
class TaskRunner extends \yii\base\Component
{

    /**
     * @var bool Indicates whether an error occured during the executing of the task.
     */
    public $error;

    /**
     * @var \thamtech\scheduler\Task The task that will be executed.
     */
    private $_task;

    /**
     * @var \thamtech\scheduler\models\SchedulerLog
     */
    private $_log;

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @param Task $task
     */
    public function setTask(Task $task)
    {
        $this->_task = $task;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->_task;
    }

    /**
     * @param \thamtech\scheduler\models\SchedulerLog $log
     */
    public function setLog($log)
    {
        $this->_log = $log;
    }

    /**
     * @return SchedulerLog
     */
    public function getLog()
    {
        return $this->_log;
    }

    /**
     * @param bool $forceRun
     */
    public function runTask($forceRun = false)
    {
        $task = $this->getTask();

        if ($task->shouldRun($forceRun)) {
            $event = new TaskEvent([
                'task' => $task,
                'taskRunner' => $this,
                'success' => true,
            ]);
            $this->trigger(Task::EVENT_BEFORE_RUN, $event);
            if (!$event->cancel) {
                $this->initLog();
                $task->setLog($this->getLog());
                $task->start();
                ob_start();
                try {
                    $this->running = true;
                    $task->run();
                    $this->running = false;
                    $output = ob_get_contents();
                    ob_end_clean();
                    $this->log($output);
                    $task->stop();
                } catch (\Exception $e) {
                    $this->running = false;
                    $task->exception = $e;
                    $event->exception = $e;
                    $event->success = false;
                    $this->handleError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
                }
                $this->trigger(Task::EVENT_AFTER_RUN, $event);
            }
        }
        $task->getModel()->save();
    }

    /**
     * @param $code
     *
     * @param $message
     *
     * @param $file
     *
     * @param $lineNumber
     */
    public function handleError($code, $message, $file, $lineNumber)
    {
        echo sprintf('ERROR: %s %s', $code, PHP_EOL);
        echo sprintf('ERROR FILE: %s %s', $file, PHP_EOL);
        echo sprintf('ERROR LINE: %s %s', $lineNumber, PHP_EOL);
        echo sprintf('ERROR MESSAGE: %s %s', $message, PHP_EOL);

        // if the failed task was mid transaction, rollback so we can save.
        if (null !== ($tx = \Yii::$app->db->getTransaction())) {
            $tx->rollBack();
        }

        $output = ob_get_contents();
        ob_end_clean();

        $this->error = true;
        $this->log($output);
        $this->getTask()->getModel()->status_id = SchedulerTask::STATUS_ERROR;
        $this->getTask()->stop();
    }

    /**
     * @param string $output
     */
    public function log($output)
    {
        $model = $this->getTask()->getModel();
        $log = $this->getLog();

        $log->started_at = $model->started_at;
        $log->ended_at = date('Y-m-d H:i:s');
        $log->error = $this->error ? 1 : 0;
        $log->output = $output;
        $log->save(false);
    }

    /**
     * Initialize the log record.
     */
    protected function initLog()
    {
        $model = $this->getTask()->getModel();
        $log = $this->getLog();

        $log->scheduler_task_id = $model->id;
        $log->started_at = date('Y-m-d H:i:s');
        $log->save(false);
        $log->refresh();
    }
}
