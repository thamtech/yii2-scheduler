<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler\events;

use yii\base\Event;

class SchedulerEvent extends Event
{
    const EVENT_BEFORE_RUN = 'SchedulerBeforeRun';
    const EVENT_AFTER_RUN = 'SchedulerAfterRun';

    public $tasks;
    public $exceptions;
    public $success;
}
