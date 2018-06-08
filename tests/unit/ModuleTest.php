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
            'tasks' => [
                'AlphabetTask' => [
                    'class' => 'thamtech\scheduler\tests\tasks\AlphabetTask',
                    'name' => 'AlphabetTask',
                ],
                'NumberTask' => [
                    'class' => 'thamtech\scheduler\tests\tasks\NumberTask',
                    // name not set to test default getName() response
                ],
                'error-task' => [
                    'class' => 'thamtech\scheduler\tests\tasks\ErrorTask',
                    'name' => 'ErrorTask',
                ],
            ],
        ], ['scheduler']);

        $tasks = $module->getTasks();

        $this->assertEquals(3, count($tasks));

        $this->assertEquals('AlphabetTask', $tasks['AlphabetTask']->getName());
        $this->assertEquals('thamtech\scheduler\tests\tasks\NumberTask', $tasks['NumberTask']->getName());
        $this->assertEquals('ErrorTask', $tasks['error-task']->getName());
    }
}
