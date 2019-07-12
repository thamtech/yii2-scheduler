<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler\events;

use yii\base\Event;

class TaskEvent extends Event
{
    public $task;
    public $taskRunner;
    public $exception;
    public $success;

    public $cancel = false;
}
