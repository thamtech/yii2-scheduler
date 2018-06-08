<?php
namespace thamtech\scheduler;

use thamtech\scheduler\models\SchedulerLog;
use Yii;
use yii\base\BootstrapInterface;
use thamtech\scheduler\models\SchedulerTask;
use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package thamtech\scheduler
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var Task[] explicitly configured tasks
     */
    private $_tasks = [];

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
        foreach ($taskDefinitions as $key=>$taskDefinition) {
            if ($taskDefinition instanceof Task) {
                $this->_tasks[$key] = $taskDefinition;
                continue;
            }

            $task = Yii::createObject($taskDefinition);
            if (!($task instanceof Task)) {
                throw new InvalidConfigException('The task definition must define an instance of \thamtech\scheduler\Task.');
            }

            $this->_tasks[$key] = $task;
        }
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
        $tasks = [];

        foreach ($this->_tasks as $key=>$task) {
            $task->setModel(SchedulerTask::createTaskModel($task));
            $tasks[$key] = $task;
        }

        $this->cleanTasks($tasks);

        return $tasks;
    }

    /**
     * Removes any records of tasks that no longer exist.
     *
     * @param Task[] $tasks
     */
    public function cleanTasks($tasks)
    {
        $currentTasks = ArrayHelper::map($tasks, function ($task) {
            return $task->getName();
        }, 'description');

        foreach (SchedulerTask::find()->indexBy('name')->all() as $name => $task) { /* @var SchedulerTask $task */
            if (!array_key_exists($name, $currentTasks)) {
                SchedulerLog::deleteAll(['scheduler_task_id' => $task->id]);
                $task->delete();
            }
        }
    }

    /**
     * Given the key of a task, it will return that task.
     * If the task doesn't exist, null will be returned.
     *
     * @param $key
     *
     * @return null|object
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function loadTask($key)
    {
        if (!isset($this->tasks[$key])) {
            return null;
        }

        $task = $this->tasks[$key];
        $task->setModel(SchedulerTask::createTaskModel($task));
        return $task;
    }
}
