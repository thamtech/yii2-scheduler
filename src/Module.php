<?php
namespace thamtech\scheduler;

use thamtech\scheduler\models\SchedulerLog;
use thamtech\scheduler\models\SchedulerTask;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the main Yii2 Scheduler module class.
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var array task definitions
     */
    private $_taskDefinitions = [];

    /**
     * @var Task[] array of instantiate tasks
     */
    private $_taskInstances = [];

    /**
     * Bootstrap the console controllers.
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@scheduler', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

        if ($app instanceof \yii\console\Application && !isset($app->controllerMap[$this->id])) {
            $app->controllerMap[$this->id] = [
                'class' => 'thamtech\scheduler\console\SchedulerController',
            ];
        }
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

        // establish task instances that are defined but not yet instantiated
        $taskDefinitions = array_diff_key($this->_taskDefinitions, $this->_taskInstances);
        foreach ($taskDefinitions as $name=>$task) {
            if (!($task instanceof Task)) {
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
