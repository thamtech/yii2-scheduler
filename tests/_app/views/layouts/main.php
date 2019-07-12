<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

if (Yii::$app->user->getIsGuest()) {
    echo \yii\helpers\Html::a('Login', ['/user/security/login']);
    echo \yii\helpers\Html::a('Registration', ['/user/registration/register']);
} else {
    echo \yii\helpers\Html::a('Logout', ['/user/security/logout']);
}

echo $content;
