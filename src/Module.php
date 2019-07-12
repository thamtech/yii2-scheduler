<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler;

use thamtech\scheduler\models\SchedulerLog;
use thamtech\scheduler\models\SchedulerTask;
use yii\base\BootstrapInterface;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\mutex\Mutex;
use Yii;

/**
 * This is the main Yii2 Scheduler module class.
 *
 * @property Task[] $tasks definitions of tasks to be performed
 *
 * @property Mutex $mutex a mutex used to ensure that the task scheduler only
 * has one instance running at a time.
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var string name of mutex to acquire before executing scheduled tasks.
     * This is only relevant if a mutex component has been set.
     */
    public $mutexName = self::class;

    /**
     * @var int time (in seconds) to wait for a lock to be released.
     * @see [[Mutex::acquire()]]
     */
    public $mutexTimeout = 5;

    /**
     * @var array task definitions
     */
    private $_taskDefinitions = [];

    /**
     * @var Task[] array of instantiate tasks
     */
    private $_taskInstances = [];

    /**
     * @var Mutex a mutex used to ensure that the task scheduler only has one
     * instance running at a time.
     */
    private $_mutex;

    /**
     * Bootstrap the console controllers.
     *
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@scheduler', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

        if ($app instanceof \yii\console\Application && !isset($app->controllerMap[$this->id])) {
            $app->controllerMap[$this->id] = [
                'class' => 'thamtech\scheduler\console\SchedulerController',
                'scheduler' => $this,
            ];
        }
    }

    /**
     * Sets the mutex component.
     *
     * @param Mutex|string|array|null $mutex a Mutex or reference to a Mutex. You may
     * specify the mutex in terms of a component ID, an Instance object, or
     * a configuration array for creating the Mutex component. If the "class"
     * value is not specified in the configuration array, it will use the value
     * of `yii\mutex\Mutex`.
     * Set null (default) if you would not like to require acquiring a mutex
     * before running scheduled tasks.
     */
    public function setMutex($mutex)
    {
        $this->_mutex = ($mutex === null) ? null : Instance::ensure($mutex, Mutex::class);
    }

    /**
     * Gets the mutex component.
     *
     * @return Mutex|null
     */
    public function getMutex()
    {
        return $this->_mutex;
    }

    /**
     * Acquires a lock to run scheduled tasks.
     *
     * @return bool lock acquiring result.
     *
     * @see [[Mutex::acquire()]]
     */
    public function acquireLock()
    {
        if (empty($this->mutex) || empty($this->mutexName)) {
            return true;
        }

        return $this->mutex->acquire($this->mutexName, $this->mutexTimeout);
    }

    /**
     * Releases acquired lock.
     *
     * @return bool lock release result: false in case named lock was not found.
     *
     * @see [[Mutex::release()]]
     */
    public function releaseLock()
    {
        if (empty($this->mutex) || empty($this->mutexName)) {
            return true;
        }

        return $this->mutex->release($this->mutexName);
    }

    /**
     * Sets the tasks for a list of Task configuration arrays or Task objects.
     *
     * @param array $taskDefinitions array of Task configurations or Task objects
     */
    public function setTasks(array $taskDefinitions)
    {
        $this->_taskDefinitions = ArrayHelper::merge($this->_taskDefinitions, $taskDefinitions);
    }

    /**
     * Gets Task instances.
     *
     * @return Task[]
     *
     * @throws \yii\base\ErrorException
     */
    public function getTasks()
    {
        $this->ensureTaskInstances();
        $this->cleanTasks();
        return $this->_taskInstances;
    }

    /**
     * Removes any records of tasks that no longer exist.
     */
    public function cleanTasks()
    {
        $this->ensureTaskInstances();

        foreach (SchedulerTask::find()->indexBy('name')->all() as $name => $task) { /* @var SchedulerTask $task */
            if (!array_key_exists($name, $this->_taskInstances)) {
                SchedulerLog::deleteAll(['scheduler_task_id' => $task->id]);
                $task->delete();
            }
        }
    }

    /**
     * Given the key of a task, it will return that task.
     * If the task doesn't exist, null will be returned.
     *
     * @param $name
     *
     * @return null|object
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function loadTask($name)
    {
        $tasks = $this->tasks;
        return isset($tasks[$name]) ? $tasks[$name] : null;
    }

    /**
     * Makes sure that the defined tasks have been instantiated
     */
    private function ensureTaskInstances()
    {
        // remove instances that are no longer in
        $staleInstanceKeys = array_keys(array_diff_key($this->_taskInstances, $this->_taskDefinitions));
        foreach ($staleInstanceKeys as $name) {
            unset($this->_taskInstances[$name]);
        }

        $defaultTaskConfig = [
            'scheduler' => $this,
        ];

        // establish task instances that are defined but not yet instantiated
        $taskDefinitions = array_diff_key($this->_taskDefinitions, $this->_taskInstances);
        foreach ($taskDefinitions as $name=>$task) {
            if (!($task instanceof Task)) {
                $task = ArrayHelper::merge($defaultTaskConfig, $task);
                $task = Yii::createObject($task);
            }

            if (!($task instanceof Task)) {
                throw new InvalidConfigException('The task definition must define an instance of \thamtech\scheduler\Task.');
            }

            $task->setModel(SchedulerTask::createTaskModel($name, $task));
            $this->_taskInstances[$name] = $task;
        }
    }
}
