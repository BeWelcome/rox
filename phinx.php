<?php
include_once 'vendor/autoload.php';
include_once 'tools/roxmigration/roxmigration.php';

function parseDSN($dsn)
{
    $dsn = preg_split('%[:;=]%', $dsn, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($dsn) || count($dsn) % 2 != 1) {
        throw new PException('DSN parse error!');
    }
    $args = array();
    for ($i = 1; $i < count($dsn); $i++) {
        if ($i % 2 == 0)
            continue;
        $args[$dsn[$i]] = $dsn[$i + 1];
    }
    return array("provider" => $dsn[0], "dsn" => $args);
}

$config = array();
$inifile = parse_ini_file('rox_local.ini', true);
if (!empty($inifile)) {

    $parsedDSN = parseDSN($inifile['db']['dsn']);
    $provider = $parsedDSN['provider'];;
    $dsn = $parsedDSN['dsn'];
    $host = $dsn['host'];
    $dbname = $dsn['dbname'];
    $user = $inifile['db']['user'];
    $password = $inifile['db']['password'];
    $production = false;
    if (isset($inifile['syshcvol']['SiteName'])) {
        $site = $inifile['syshcvol']['SiteName'];
        if (($site == 'www.bewelcome.org') | ($site == 'beta.bewelcome.org')) {
            $production = true;
        }
    }
    $config = array(
        "migration_base_class" => "Rox\\Tools\\RoxMigration",
        "paths" => array(
            "migrations" => "%%PHINX_CONFIG_DIR%%/migrations",
        ),
        "migration_base_class" => "Rox\\Tools\\RoxMigration",
        "environments" => array(
            "default_migration_table" => "phinxlog",
            "default_database" => "testing",
        )
    );
    if ($production) {
        $prodarray = array(
            "adapter" => $provider,
            "host" => $host,
            "name" => "BW_MAIN",
            "user" => $user,
            "pass" => $password,
            "port" => "3306",
            "charset" => "utf8"
        );
        $config['environments']['production'] = $prodarray;
    }
    $devarray = array(
        "adapter" => $provider,
        "host" => $host,
        "name" => "ALPHA_MAIN",
        "user" => $user,
        "pass" => $password,
        "port" => "3306",
        "charset" => "utf8"
    );
    $testarray = array(
        "adapter" => "mysql",
        "host" => $host,
        "name" => $dbname,
        "user" => $user,
        "pass" => $password,
        "port" => "3306",
        "charset" => "utf8"
    );
    $config['environments']['development'] = $devarray;
    $config['environments']['testing'] = $testarray;
}

return $config;
