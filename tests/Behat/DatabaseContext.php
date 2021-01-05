<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;

final class DatabaseContext implements Context
{
    /**
     * @BeforeSuite
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function disableAutocommit()
    {
        StaticDriver::setKeepStaticConnections(true);
    }

    /**
     * @BeforeScenario
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function beginTransaction()
    {
        StaticDriver::beginTransaction();
    }

    /**
     * @AfterScenario
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function rollbackTransaction()
    {
        StaticDriver::rollBack();
    }
}
