<?php

namespace Rox\Core\Factory;

use Illuminate\Database\Capsule\Manager;
use PDO;

class DatabaseFactory
{
    public function __invoke()
    {
        $params = parse_ini_file(getcwd() . '/rox_local.ini');

        $parts = explode('=', $params['dsn']);

        $host     = substr($parts[1], 0, strpos($parts[1], ';'));
        $database = $parts[2];
        $user     = $params['user'];
        $password = $params['password'];

        // Setup database connection with Eloquent
        $capsule = new Manager();

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $host,
            'database'  => $database,
            'username'  => $user,
            'password'  => $password,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'options'   => [
                PDO::MYSQL_ATTR_INIT_COMMAND
                    => "SET NAMES 'UTF8', time_zone = '+00:00', sql_mode='NO_ENGINE_SUBSTITUTION';",
            ],
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // TODO this should only be set when debug mode enable.
        $capsule->getConnection()->enableQueryLog();

        return $capsule->getConnection();
    }
}
