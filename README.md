Yii2 Scheduler
==============

A configuration-driven scheduled task manager for [Yii2](http://www.yiiframework.com).

This is adapted from [webtoolsnz/yii2-scheduler](https://github.com/webtoolsnz/yii2-scheduler)
to provide a more configuration-driven approach.

The main differences:

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

The preferred way to install this extension is through
[composer](http://getcomposer.org/download/).

Install using the following command.

```bash
$ composer require thamtech/yii2-scheduler
```

Now that the package has been installed you need to configure the module in
your application.

The `config/console.php` file (or the equivalent console config file if you are
using a different Yii project template) should be updated to reflect the changes
below:

```php
<?php
[
    'bootstrap' => ['log', 'scheduler'],
    'modules' => [
        'scheduler' => [
            'class' => 'thamtech\scheduler\Module',
            
            // optional: use the 'mutex' application component to acquire a lock
            // while executing tasks so only one execution of schedule tasks
            // can be running at a time.
            'mutex' => 'mutex', 
        ],
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
    ],
];
```

also add this to the top of your `config/console.php` file:

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

Run the database migrations, which will create the necessary tables:

```bash
php yii migrate up --migrationPath=vendor/thamtech/yii2-scheduler/src/migrations
```

To implement the GUI for reviewing scheduled tasks and logs, add a web
controller:

```php
<?php
namespace app\modules\admin\controllers;

use yii\web\Controller;

/**
 * SchedulerController has a set of actions for viewing scheduled tasks and
 * their logs.
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
            'view' => [
                'class' => 'thamtech\scheduler\actions\ViewAction',
                'view' => '@scheduler/views/view',
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

You can now create your first task using scheduler. In this example, we will
create `ConcatStringsTask.php` inside the `tasks` directory of your app:

```php
<?php
namespace app\tasks;

/**
 * Task to print a concatenation of the specified strings.
 */
class ConcatStringsTask extends \thamtech\scheduler\Task
{
    /**
     * @var string task description
     */
    public $description = 'Prints a concatenation of the specified strings';
    
    /**
     * @var string[] the strings to be concatenated
     */
    public $strings = [];
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        echo join('', $this->strings);
    }
}
```

In your `scheduler` module config within your console app, define the task:

```php
<?php
'modules' => [
    'scheduler' => [
        'class' => 'thamtech\scheduler\Module',
        'tasks' => [
            'hello-world' => [
                'class' => 'app\tasks\ConcatStringsTask',
                'displayName' => 'Hello World Task',
                'schedule' => '0 * * * *',
                'strings' => ['Hello', ' ', 'World'],
            ],
        ],
    ],
],
```

The above code defines a simple task that runs at the start of every hour, and
prints "Hello World". An alternative approach would be to hard-code some or all
of the properties in your task class (like `schedule`). However, this example
is more in line with the project's goal of being a more configuration-driven
approach to defining tasks.

The `$schedule` property of this class defines how often the task will run,
these are simply [Cron Expression](http://en.wikipedia.org/wiki/Cron#Examples)

You can define multiple task instances in the `scheduler` module config, and it
is ok to reuse the same Task class for multiple instances. Here is an example
with an additional "Foo Bar" task that runs every hour on the half-hour:

```php
<?php
'modules' => [
    'scheduler' => [
        'class' => 'thamtech\scheduler\Module',
        'tasks' => [
            'hello-world' => [
                'class' => 'app\tasks\ConcatStringsTask',
                'displayName' => 'Hello World Task',
                'schedule' => '0 * * * *',
                'strings' => ['Hello', ' ', 'World'],
            ],
            'foo-bar' => [
                'class' => 'app\tasks\ConcatStringsTask',
                'displayName' => 'Foo Bar Task',
                'schedule' => '30 * * * *',
                'strings' => ['Foo', ' ', 'Bar'],
            ],
        ],
    ],
],
```

### Running the tasks

Scheduler provides an intuitive CLI for executing tasks, below are some examples

```bash
 # list all tasks and their status
 $ php yii scheduler

 # run the task if due
 $ php yii scheduler/run --taskName=hello-world

 # force the task to run regardless of schedule
 $ php yii scheduler/run --taskName=hello-world --force

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

Events are thrown before and running individual tasks as well as at a global
level for multiple tasks

Task Level

```php
<?php
Event::on(AlphabetTask::className(), AlphabetTask::EVENT_BEFORE_RUN, function ($event) {
    Yii::trace($event->task->className . ' is about to run');
});
Event::on(AlphabetTask::className(), AlphabetTask::EVENT_AFTER_RUN, function ($event) {
    Yii::trace($event->task->className . ' just ran '.($event->success ? 'successfully' : 'and failed'));
});
```

or at the global level, to throw errors in `/yii`

```php
<?php
$application->on(\thamtech\scheduler\events\SchedulerEvent::EVENT_AFTER_RUN, function ($event) {
    if (!$event->success) {
        foreach($event->exceptions as $exception) {
            throw $exception;
        }
    }
});
```

You could throw the exceptions at the task level, however this will prevent
further tasks from running.

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
