XactCommandScheduler
===============

This bundle allows you to provide job scheduling via a list of jobs configured within a DBAL store.

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

### 5) Add a scheduled job via code
```php
use Xact\CommandScheduler\Scheduler\CommandScheduler;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Cron\CronExpression;
...

public function myControllerAction(CommandScheduler $scheduler)
{
    $scheduledCommand = new ScheduledCommand();
    $scheduledCommand->setCronExpression( CronExpression::factory('@daily') );
    $scheduledCommand->setCommand( 'app:my-daily-command' );
    $scheduler->add( $scheduledCommand );
}
```
Credits
-------

* Ian Foulds as the creator of this package.
* Piotr Nowak (https://github.com/noofaq) for inspiration when trying to find a replacement for jms/job-queue-bundle - https://github.com/schmittjoh/JMSJobQueueBundle/issues/208#issuecomment-393069592.

License
-------

This bundle is released under the MIT license. See the complete license in the
bundle:

[LICENSE](https://github.com/xactsystems/command-scheduler/blob/master/LICENSE)