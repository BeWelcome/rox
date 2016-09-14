<?php

include_once 'bootstrap/autoload.php';

$base = '';

if (getenv('APP_ENV') === 'testing') {
    $base = 'tests/';
}

return [
    'migration_base_class' => \Rox\Tools\RoxMigration::class,
    'seeds_base_class' => \Rox\Tools\RoxSeed::class,
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/' . $base . 'migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/' . $base . 'seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => getenv('DB_HOST'),
            'name' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'pass' => getenv('DB_PASS'),
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
];
