<?php

include_once 'bootstrap/autoload.php';

return [
    'migration_base_class' => \Rox\Tools\RoxMigration::class,
    'seeds_base_class' => \Rox\Tools\RoxSeed::class,
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => $container->getParameter('database_host'),
            'name' => $container->getParameter('database_name'),
            'user' => $container->getParameter('database_user'),
            'pass' => $container->getParameter('database_password'),
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
];
