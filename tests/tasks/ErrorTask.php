<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler\tests\tasks;

/**
 * Class ErrorTask
 * @package thamtech\scheduler\tests\tasks
 */
class ErrorTask extends \thamtech\scheduler\Task
{
    public $description = 'Throws an Error';
    public $schedule = '*/1 * * * *';

    public function run()
    {
        trigger_error('this is an error');
    }
}
