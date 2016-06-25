<?php

namespace Rox\Core\Factory;

use Illuminate\Database\Capsule\Manager;
use PDO;

class DatabaseFactory
{
    public function __invoke()
    {
        // Setup database connection with Eloquent
        $capsule = new Manager();

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'options' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8', time_zone = '+00:00', sql_mode='NO_ENGINE_SUBSTITUTION';",
            ],
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // TODO this should only be set when debug mode enable.
        $capsule->getConnection()->enableQueryLog();

        return $capsule->getConnection();
    }
}
