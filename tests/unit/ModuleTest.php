<?php

namespace thamtech\scheduler\tests;

use Yii;
use \yii\codeception\TestCase;


class ModuleTest extends TestCase
{
    public $appConfig = '@tests/config/unit.php';

    public function testGetTasks()
    {
        $module = Yii::createObject([
            'class' => '\thamtech\scheduler\Module',
            'taskPath' => '@tests/tasks',
            'taskNameSpace' => '\thamtech\scheduler\tests\tasks'
        ], ['scheduler']);

        $tasks = $module->getTasks();

        $this->assertEquals(3, count($tasks));

        $this->assertEquals('AlphabetTask', $tasks[0]->getName());
        $this->assertEquals('NumberTask', $tasks[2]->getName());
        $this->assertEquals('ErrorTask', $tasks[1]->getName());
    }

    public function testGetTaskInvalidPath()
    {
        $this->setExpectedException('ErrorException');

        $module = Yii::createObject([
            'class' => '\thamtech\scheduler\Module',
            'taskPath' => '@tests/some/random/path',
        ], ['scheduler']);

        $module->getTasks();
    }
}
