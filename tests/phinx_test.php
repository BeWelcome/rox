<?php
include_once 'vendor/autoload.php';
include_once 'tools/roxmigration/roxmigration.php';

$config = [
    "migration_base_class" => "Rox\\Tools\\RoxMigration",
    "seeds_base_class" => "Rox\\Tools\\RoxSeed",
    "paths" => [
        "migrations" => "%%PHINX_CONFIG_DIR%%/migrations",
        "seeds" => "%%PHINX_CONFIG_DIR%%/seeds",
    ],
    "environments" => [
        "default_migration_table" => "phinxlog",
        "default_database" => "testing",
        "testing" => [
            "adapter" => "mysql",
            "host" => 'localhost',
            "name" => 'bewelcome_test',
            "user" => 'bewelcome',
            "pass" => 'bewelcome',
            "port" => "3306",
            "charset" => "utf8",
        ],
    ]
];

return $config;
