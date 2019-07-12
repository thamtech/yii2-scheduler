<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler;

use thamtech\scheduler\models\SchedulerTask;
use yii\base\Component;
use Cron\CronExpression;

/**
 * Class Task
 * @package thamtech\scheduler
 */
abstract class Task extends Component
{
    const EVENT_BEFORE_RUN = 'TaskBeforeRun';
    const EVENT_AFTER_RUN = 'TaskAfterRun';

    /**
     * @var Module the scheduler module.
     */
    public $scheduler;

    /**
     * @var \Exception|null Exception raised during run (if any)
     */
    public $exception;

    /**
     * @var String Task description
     */
    public $description;

    /**
     * @var String The cron expression that determines how often this task
     *     should run.
     */
    public $schedule;

    /**
     * @var bool Active flag allows you to set the task to inactive (meaning it
     *     will not run)
     */
    public $active = true;

    /**
     * @var int How many seconds after due date to wait until the task becomes
     *     overdue and is re-run. This should be set to at least 2x the amount
     *     of time the task takes to run as the task will be restarted.
     */
    public $overdueThreshold = 3600;

    /**
     * @var null|SchedulerTask
     */
    private $_model;

    /**
     * @var string the task name
     */
    private $_displayName;

    /**
     * @var \thamtech\scheduler\models\SchedulerLog
     */
    private $_log;

    /**
     * The main method that gets invoked whenever a task is ran, any errors that occur
     * inside this method will be captured by the TaskRunner and logged against the task.
     *
     * @return mixed
     */
    abstract public function run();

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
     * @param string|\DateTime $currentTime
     *
     * @return string
     */
    public function getNextRunDate($currentTime = 'now')
    {
        return CronExpression::factory($this->schedule)
            ->getNextRunDate($currentTime)
            ->format('Y-m-d H:i:s');
    }

    /**
     * Sets the task display name.
     *
     * @param string $name the task name
     */
    public function setDisplayName($name)
    {
        $this->_displayName = $name;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->_displayName ?: get_class($this);
    }

    /**
     * @param SchedulerTask $model
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * @return SchedulerTask
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param $str
     */
    public function writeLine($str)
    {
        echo $str.PHP_EOL;
    }

    /**
     * Mark the task as started
     */
    public function start()
    {
        $model = $this->getModel();
        $model->started_at = date('Y-m-d H:i:s');
        $model->save(false);}

    /**
     * Mark the task as stopped.
     */
    public function stop()
    {
        $model = $this->getModel();
        $model->last_run = $model->started_at;
        $model->next_run = $this->getNextRunDate();
        $model->started_at = null;
        $model->save(false);
    }

    /**
     * @param bool $forceRun
     *
     * @return bool
     */
    public function shouldRun($forceRun = false)
    {
        $model = $this->getModel();
        $isDue = in_array($model->status_id, [SchedulerTask::STATUS_DUE, SchedulerTask::STATUS_OVERDUE, SchedulerTask::STATUS_ERROR]);
        $isRunning = $model->status_id == SchedulerTask::STATUS_RUNNING;
        $overdue = false;

        if((strtotime($model->started_at) + $this->overdueThreshold) <= strtotime("now")) {
            $overdue = true;
        }

        return ($this->active && ((!$isRunning && ($isDue || $forceRun)) || ($isRunning && $overdue)));
    }

}
