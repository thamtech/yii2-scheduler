<?php
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
