<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

/**
 * Application configuration for unit tests.
 */
return yii\helpers\ArrayHelper::merge(
    require(__DIR__.'/../_app/config/web.php'),
    []
);
