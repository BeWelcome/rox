<?php
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env', __DIR__.'/.env.local');

$config = [
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
            'host' => $_ENV['DB_HOST'],
            'name' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'pass' => $_ENV['DB_PASS'],
            'port' => $_ENV['DB_PORT'],
            'charset' => 'utf8mb4',
        ],
    ],
];

echo print_r($config, true);

return $config;
