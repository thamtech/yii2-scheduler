<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

return [
    'id' => 'yii2-test--console',
    'basePath' => dirname(__DIR__),
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => __DIR__.'/../../../../src/migrations',
        ],
    ],
    'components' => [
        'log'   => null,
        'cache' => null,
        'db'    => require __DIR__.'/db.php',
    ],
];
