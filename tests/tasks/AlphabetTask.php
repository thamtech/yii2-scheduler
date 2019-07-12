<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

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
