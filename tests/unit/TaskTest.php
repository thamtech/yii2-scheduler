<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler\tests;

use \thamtech\scheduler\tests\tasks\AlphabetTask;
use \thamtech\scheduler\models\SchedulerTask;
use Codeception\TestCase\Test as TestCase;
use AspectMock\Test as test;


class TaskTest extends TestCase
{
    public $appConfig = '@tests/config/unit.php';

//    public function testStartStop()
//    {
//        $model = test::double(new SchedulerTask(), ['save' => function () {
//            return true;
//        }]);
//
//        $task = new AlphabetTask;
//        $task->setModel($model);
//
//        $start = date('Y-m-d H:i:s');
//        $nextRun = date('Y-m-d H:i:00', strtotime('+1 minute'));
//
//        $task->start();
//        $this->assertEquals($start, $model->started_at);
//
//        $task->stop();
//        $this->assertNull($model->started_at);
//        $this->assertEquals($start, $model->last_run);
//        $this->assertEquals($nextRun, $model->next_run);
//    }

    public function testGetName()
    {
        $task = new AlphabetTask();
        $this->assertEquals('thamtech\scheduler\tests\tasks\AlphabetTask', $task->getDisplayName());
    }

    /**
     * @dataProvider runDateProvider
     */
    public function testGetNextRunDate($expression, $currentTime, $nextRun)
    {
        $task = new AlphabetTask();
        $task->schedule = $expression;

        $this->assertEquals($nextRun, $task->getNextRunDate($currentTime));
    }

    public function runDateProvider()
    {
        return [
            ['*/5 * * * *', new \DateTime('1987-11-15 05:25:00'), '1987-11-15 05:30:00'],
            ['0 */1 * * *', new \DateTime('2015-05-03 16:45:46'), '2015-05-03 17:00:00'],
            ['0 0 * * *', new \DateTime('2015-05-03 16:45:46'), '2015-05-04 00:00:00']
        ];
    }
//
//    /**
//     * @dataProvider shouldRunProvider
//     */
//    public function testShouldRun($expected, $status_id, $active, $force)
//    {
//        $model = test::double(new SchedulerTask(), ['save' => function () {
//            return true;
//        }]);
//
//        $model->status_id = $status_id;
//        $model->active = $active;
//
//        $task = new AlphabetTask();
//        $task->setModel($model);
//
//        $this->assertEquals($expected, $task->shouldRun($force));
//
//    }
//
//    public function shouldRunProvider()
//    {
//        return [
//            [false, SchedulerTask::STATUS_PENDING, 1, false],
//            [true, SchedulerTask::STATUS_DUE, 1, false],
//            [false, SchedulerTask::STATUS_RUNNING, 1, false],
//            [false, SchedulerTask::STATUS_DUE, 0, true],
//            [true, SchedulerTask::STATUS_PENDING, 1, true],
//            [true, SchedulerTask::STATUS_OVERDUE, 1, false],
//        ];
//    }
//
    public function testWriteLine()
    {
        $task = new AlphabetTask();

        ob_start();
        $task->writeLine('test');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("test\n", $output);
    }

    public function testGetSetModel()
    {
        $task = new AlphabetTask();
        $model = new \stdClass();

        $task->setModel($model);
        $this->assertEquals($model, $task->getModel());
    }


}
