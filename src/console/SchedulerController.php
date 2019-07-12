<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler\console;

use thamtech\scheduler\events\SchedulerEvent;
use thamtech\scheduler\models\base\SchedulerLog;
use thamtech\scheduler\models\SchedulerTask;
use thamtech\scheduler\Task;
use thamtech\scheduler\TaskRunner;
use Yii;
use yii\base\InvalidParamException;
use yii\console\Controller;
use yii\helpers\Console;


/**
 * Scheduled task runner for Yii2
 *
 * You can use this command to manage scheduler tasks
 *
 * ```
 * $ ./yii scheduler/run-all
 * ```
 *
 */
class SchedulerController extends Controller
{
    /**
     * @var bool Force pending tasks to run.
     */
    public $force = false;

    /**
     * @var null|string Name of the task to run
     */
    public $taskName;

    /**
     * @var \thamtech\scheduler\Module the scheduler module
     */
    public $scheduler;

    /**
     * @var array Colour map for SchedulerTask status ids
     */
    private $_statusColors = [
        SchedulerTask::STATUS_PENDING => Console::FG_BLUE,
        SchedulerTask::STATUS_DUE => Console::FG_YELLOW,
        SchedulerTask::STATUS_OVERDUE => Console::FG_RED,
        SchedulerTask::STATUS_RUNNING => Console::FG_GREEN,
        SchedulerTask::STATUS_ERROR => Console::FG_RED,
    ];

    /**
     * @param string $actionId
     *
     * @return array
     */
    public function options($actionId)
    {
        $options = parent::options($actionId);

        switch ($actionId) {
            case 'run-all':
                $options[] = 'force';
                break;
            case 'run':
                $options[] = 'force';
                $options[] = 'taskName';
                break;
        }

        return $options;
    }

    /**
     * List all tasks
     */
    public function actionIndex()
    {
        // Update task index
        $this->scheduler->getTasks();

        $models = SchedulerTask::find()->all();

        echo $this->ansiFormat('Scheduled Tasks', Console::UNDERLINE).PHP_EOL;

        foreach ($models as $model) { /* @var SchedulerTask $model */
            $row = sprintf(
                "%s\t%s\t%s\t%s\t%s",
                $model->name,
                $model->schedule,
                is_null($model->last_run) ? 'NULL' : $model->last_run,
                $model->next_run,
                $model->getStatus()
            );

            $color = isset($this->_statusColors[$model->status_id]) ? $this->_statusColors[$model->status_id] : null;
            echo $this->ansiFormat($row, $color).PHP_EOL;
        }
    }

    /**
     * Run all due tasks
     */
    public function actionRunAll()
    {
        $tasks = $this->scheduler->getTasks();

        echo 'Running Tasks:'.PHP_EOL;

        $event = new SchedulerEvent([
            'tasks' => $tasks,
            'success' => true,
        ]);

        $this->trigger(SchedulerEvent::EVENT_BEFORE_RUN, $event);
        foreach ($tasks as $task) {
            $this->runTask($task);
            if ($task->exception) {
                $event->success = false;
                $event->exceptions[] = $task->exception;
            }
        }
        $this->trigger(SchedulerEvent::EVENT_AFTER_RUN, $event);

        echo PHP_EOL;
    }

    /**
     * Run the specified task (if due)
     */
    public function actionRun()
    {
        if (null === $this->taskName) {
            throw new InvalidParamException('taskName must be specified');
        }

        /* @var Task $task */
        $task = $this->scheduler->loadTask($this->taskName);

        if (!$task) {
            throw new InvalidParamException('Invalid taskName');
        }

        $event = new SchedulerEvent([
            'tasks' => [$task],
            'success' => true,
        ]);

        $this->trigger(SchedulerEvent::EVENT_BEFORE_RUN, $event);
        $this->runTask($task);
        if ($task->exception) {
            $event->success = false;
            $event->exceptions = [$task->exception];
        }
        $this->trigger(SchedulerEvent::EVENT_AFTER_RUN, $event);
    }

    /**
     * @param Task $task
     */
    private function runTask(Task $task)
    {
        if (!$task->active) {
            echo sprintf("\tSkipping inactive %s", $task->getDisplayName()).PHP_EOL;
            return;
        }

        echo sprintf("\tRunning %s...", $task->getDisplayName());
        if ($task->shouldRun($this->force)) {
            $runner = new TaskRunner();
            $runner->setTask($task);
            $runner->setLog(new SchedulerLog());
            $runner->runTask($this->force);
            echo $runner->error ? 'error' : 'done'.PHP_EOL;
        } else {
            echo "Task is not due, use --force to run anyway".PHP_EOL;
        }
    }
}
