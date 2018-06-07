Yii2 Scheduler
==============

A configurable scheduled task manager for [Yii2](http://www.yiiframework.com).

This is adapted from [webtoolsnz/yii2-scheduler](https://github.com/webtoolsnz/yii2-scheduler)
to provide a more configuration-driven approach.

A few differences:

* webtoolsnz/yii2-scheduler
   * automatically picks up Task classes in the `@app/tasks` folder
   * once tasks have been established in the database table, the `active` value
     from the database controls whether the task is enabled, not the `active`
     property in the Task class
* thamtech/yii2-scheduler
   * tasks are defined explicitly as part of the scheduler module config
   * the `active` property of the Task instance controls whether the task is
     enabled (so that it may be controlled programmatically instead of only via
     the scheduler's databse table)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Install using the following command.

```bash
$ composer require thamtech/yii2-scheduler
```

Now that the  package has been installed you need to configure the module in your application

The `config/console.php` file should be updated to reflect the changes below
```php
    'bootstrap' => ['log', 'scheduler'],
    'modules' => [
        'scheduler' => ['class' => 'thamtech\scheduler\Module'],
    ],
    'components' => [
        'errorHandler' => [
            'class' => 'thamtech\scheduler\ErrorHandler'
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\EmailTarget',
                    'mailer' =>'mailer',
                    'levels' => ['error', 'warning'],
                    'message' => [
                        'to' => ['wt.alerts@webtools.co.nz'],
                        'from' => [$params['adminEmail']],
                        'subject' => 'Scheduler Error - ####SERVERNAME####'
                    ],
                    'except' => [
                    ],
                ],
            ],
        ],
    ]
```

also add this to the top of your `config/console.php` file
```php
\yii\base\Event::on(
    \thamtech\scheduler\console\SchedulerController::className(),
    \thamtech\scheduler\events\SchedulerEvent::EVENT_AFTER_RUN,
    function ($event) {
        if (!$event->success) {
            foreach($event->exceptions as $exception) {
                throw $exception;
            }
        }
    }
);
```

To implement the GUI for scheduler also add the following to your `config/web.php`
```php
    'bootstrap' => ['log', 'scheduler'],
    'modules' => [
        'scheduler' => ['class' => 'thamtech\scheduler\Module'],
    ],
```

After the configuration files have been updated, a `tasks` directory will need to be created in the root of your project.


Run the database migrations, which will create the necessary tables for `scheduler`
```bash
php yii migrate up --migrationPath=vendor/thamtech/yii2-scheduler/src/migrations
```

Add a controller
```php
<?php

namespace app\modules\admin\controllers;

use yii\web\Controller;

/**
 * Class SchedulerController
 * @package app\modules\admin\controllers
 */
class SchedulerController extends Controller
{
    public function actions()
    {
        return [
            'index' => [
                'class' => 'thamtech\scheduler\actions\IndexAction',
                'view' => '@scheduler/views/index',
            ],
            'update' => [
                'class' => 'thamtech\scheduler\actions\UpdateAction',
                'view' => '@scheduler/views/update',
            ],
            'view-log' => [
                'class' => 'thamtech\scheduler\actions\ViewLogAction',
                'view' => '@scheduler/views/view-log',
            ],
        ];
    }
}
```

## Example Task

You can now create your first task using scheduler, create the file `AlphabetTask.php` inside the `tasks` directory in your project root.

Paste the below code into your task:
```php
<?php
namespace app\tasks;

/**
 * Class AlphabetTask
 * @package app\tasks
 */
class AlphabetTask extends \thamtech\scheduler\Task
{
    public $description = 'Prints the alphabet';
    public $schedule = '0 * * * *';
    public function run()
    {
        foreach (range('A', 'Z') as $letter) {
            echo $letter;
        }
    }
}
```

The above code defines a simple task that runs at the start of every hour, and prints the alphabet.

The `$schedule` property of this class defines how often the task will run, these are simply [Cron Expression](http://en.wikipedia.org/wiki/Cron#Examples)


### Running the tasks

Scheduler provides an intuitive CLI for executing tasks, below are some examples

```bash
 # list all tasks and their status
 $ php yii scheduler

 # run the task if due
 $ php yii scheduler/run --taskName=AlphabetTask

 # force the task to run regardless of schedule
 $ php yii scheduler/run --taskName=AlphabetTask --force

 # run all tasks
 $ php yii scheduler/run-all

 # force all tasks to run
 $ php yii scheduler/run-all --force
```

In order to have your tasks run automatically simply setup a crontab like so

```bash
*/5 * * * * admin php /path/to/my/app/yii scheduler/run-all > /dev/null &
```

### Events & Errors

Events are thrown before and running individual tasks as well as at a global level for multiple tasks

Task Level

```php
Event::on(AlphabetTask::className(), AlphabetTask::EVENT_BEFORE_RUN, function ($event) {
    Yii::trace($event->task->className . ' is about to run');
});
Event::on(AlphabetTask::className(), AlphabetTask::EVENT_AFTER_RUN, function ($event) {
    Yii::trace($event->task->className . ' just ran '.($event->success ? 'successfully' : 'and failed'));
});
```

or at the global level, to throw errors in `/yii`

```php
$application->on(\thamtech\scheduler\events\SchedulerEvent::EVENT_AFTER_RUN, function ($event) {
    if (!$event->success) {
        foreach($event->exceptions as $exception) {
            throw $exception;
        }
    }
});
```

You could throw the exceptions at the task level, however this will prevent further tasks from running.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
