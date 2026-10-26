<?php

namespace App\Tests\PHPUnit;

use DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension;
use PHPUnit\Event\TestRunner\ChildProcessStarted;
use PHPUnit\Event\TestRunner\ChildProcessStartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * The DAMA bundle rolls back a test's transaction only when the next test starts. A test that runs in a
 * process of its own (#[RunTestsInSeparateProcesses]) starts in the child process, so the parent would keep
 * the previous test's transaction, and its row locks, open while the child runs. The child then waits for
 * those locks until the lock wait timeout. This rolls back in the parent before the child starts.
 *
 * @infection-ignore-all
 */
final class RollBackBeforeChildProcessExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements ChildProcessStartedSubscriber {
            public function notify(ChildProcessStarted $event): void
            {
                PHPUnitExtension::rollBack();
            }
        });
    }
}
