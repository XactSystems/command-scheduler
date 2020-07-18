XactCommandScheduler
===============

This bundle allows you to provide job scheduling via a list of jobs configured within a DBAL store.

Jobs can be created for once-only jobs as well as repeating jobs based on a cron expression. The bundle features an admin interface for management of the scheduled commands as well as via the CommandScheduler class.

Documentation
-------------
### 1) Add XactCommandScheduler to your project

```bash
composer require xactsystems/job-scheduler
```

### 2) Create the scheduler table
```bash
php bin/console doctrine:schema:create
```

### 3) Add the routes for the scheduler admin views
config/routes.yaml
```yaml
command_scheduler:
    resource: "@XactCommandSchedulerBundle/Resources/config/routing.yaml"
```

### 4) Use the admin views
Browse to http://my-project/command-scheduler/list


### 5) Run the command scheduler
```bash
php bin/console xact:command-scheduler
```

The command accepts the following options:
    --max-runtime=nnn       Sets the maximum length in seconds the scheduler will run for. 0 (default) runs forever.
    --idle-time=nnn         Sets the number of seconds the scheduler sleeps for when the command queue is empty. Defaults to 5.

Manage the scheduler via code
-----------------------------

### Add a scheduled command
```php
use Xact\CommandScheduler\Scheduler\CommandScheduler;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Cron\CronExpression;
...

public function myControllerAction(CommandScheduler $scheduler)
{
    $scheduledCommand = new ScheduledCommand();
    $scheduledCommand->setDescription('My daily command');
    $scheduledCommand->setCommand( 'app:my-daily-command' );
    $scheduledCommand->setCronExpression( CronExpression::factory('@daily') );
    $scheduledCommand->setPriority(5);  // Defaults to 1
    $scheduler->add( $scheduledCommand );
}
```

### Disable a command
```php
use Xact\CommandScheduler\Scheduler\CommandScheduler;
use Xact\CommandScheduler\Entity\ScheduledCommand;
...

public function myControllerAction(int $commandId, CommandScheduler $scheduler)
{
    $scheduler->disable($commandId);
}
```

### Run a command immediately
```php
use Xact\CommandScheduler\Scheduler\CommandScheduler;
use Xact\CommandScheduler\Entity\ScheduledCommand;
...

public function myControllerAction(int $commandId, CommandScheduler $scheduler)
{
    $scheduler->runImmediately($commandId);
}
```

### Delete a command
```php
use Xact\CommandScheduler\Scheduler\CommandScheduler;
use Xact\CommandScheduler\Entity\ScheduledCommand;
...

public function myControllerAction(int $commandId, CommandScheduler $scheduler)
{
    $scheduler->delete($commandId);
}
```

### Get a list of active commands
```php
use Xact\CommandScheduler\Scheduler\CommandScheduler;
use Xact\CommandScheduler\Entity\ScheduledCommand;
...

public function myControllerAction(CommandScheduler $scheduler)
{
    $commands = $scheduler->getActive();
    foreach ($commands as $command) {
        ...
    }
}
```

Cron notes
----------
The bundle uses dragonmantank/cron-expression CronExpression class to determine run times and you can use the non-standard pre-defined scheduling definitions. See [Cron Format](https://en.wikipedia.org/wiki/Cron#Format) for more details.

Credits
-------

* Ian Foulds as the creator of this package.
* Piotr Nowak (https://github.com/noofaq) for inspiration when trying to find a replacement for jms/job-queue-bundle - https://github.com/schmittjoh/JMSJobQueueBundle/issues/208#issuecomment-393069592.
* Julien Guyon (https://github.com/j-guyon) for inspiration with his command scheduler bundle.

License
-------

This bundle is released under the MIT license. See the complete license in the
bundle:

[LICENSE](https://github.com/xactsystems/command-scheduler/blob/master/LICENSE)