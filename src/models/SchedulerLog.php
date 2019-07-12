<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler\models;

use Yii;

/**
 * This is the model class for table "scheduler_log".
 */
class SchedulerLog extends \thamtech\scheduler\models\base\SchedulerLog
{
    public function __toString()
    {
        return Yii::$app->formatter->asDatetime($this->started_at);
    }

    public function getDuration()
    {
        $start = new \DateTime($this->started_at);
        $end = new \DateTime($this->ended_at);
        $diff = $start->diff($end);

        return $diff->format('%hh %im %Ss');
    }

}
