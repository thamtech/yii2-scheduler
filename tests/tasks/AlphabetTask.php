<?php
namespace thamtech\scheduler\tests\tasks;

/**
 * Class AlphabetTask
 * @package thamtech\scheduler\tests\tasks
 */
class AlphabetTask extends \thamtech\scheduler\Task
{
    public $description = 'Prints the alphabet';
    public $schedule = '*/1 * * * *';

    public function run()
    {
        foreach (range('A', 'Z') as $letter) {
            echo $letter;
        }
    }
}
