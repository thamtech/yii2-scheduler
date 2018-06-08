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
                    'displayName' => 'AlphabetTask',
                ],
                'NumberTask' => [
                    'class' => 'thamtech\scheduler\tests\tasks\NumberTask',
                    // displayName not set to test default getName() response
                ],
                'error-task' => [
                    'class' => 'thamtech\scheduler\tests\tasks\ErrorTask',
                    'displayName' => 'ErrorTask',
                ],
            ],
        ], ['scheduler']);

        $tasks = $module->getTasks();

        $this->assertEquals(3, count($tasks));

        $this->assertEquals('AlphabetTask', $tasks['AlphabetTask']->getDisplayName());
        $this->assertEquals('thamtech\scheduler\tests\tasks\NumberTask', $tasks['NumberTask']->getDisplayName());
        $this->assertEquals('ErrorTask', $tasks['error-task']->getDisplayName());
    }
}
