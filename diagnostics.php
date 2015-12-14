<?php

$files = array('rox_local.ini',
               'localhost.ini',
               'rox_default.ini',
               'inc/config.inc.php',
               'base.xml',
               'htdocs/.htaccess',
              );
$dirs = array('htdocs',
              'build',
              'archive',
              'data',
              'extensions',
              'inc',
              'lib',
              'pthacks',
              'templates',
              'modules',
              'roxlauncher',
              'setup',
              'test',
              'testdb',
              'tools',
              'update_script',
             );

// check files - stick errors in $file_errors
$file_errors = array();
foreach ($files as $file)
{
    if (!file_exists($file) || !is_file($file))
    {
        $file_errors[] = "{$file}";
        continue;
    }
    $dummy = file_get_contents($file);
    if (!$dummy)
    {
        $file_errors[] = "{$file}";
    }
}

// check dirs - stick errors in $dir_errors
$dir_errors = array();
foreach ($dirs as $dir)
{
    if (!file_exists($dir) || !is_dir($dir))
    {
        $dir_errors[] = "{$dir}";
        continue;
    }
}

// output result
echo <<<XHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="dk" lang="dk">
<head>
<title>bwrox diagnostics</title>
</head>
<body>
XHTML;

if (empty($file_errors))
{
    echo "<p>All core files found.</p>";
}
else
{
    echo "<p>Core files missing:\n";
    echo "<ul>\n";
    foreach ($file_errors as $error)
    {
        echo "<li>{$error}</li>\n";
    }
    echo "</ul>\n";
}

if (empty($dir_errors))
{
    echo "<p>All core diagnostics found.</p>";
}
else
{
    echo "<p>Core directories missing:\n";
    echo "<ul>\n";
    foreach ($dir_errors as $error)
    {
        echo "<li>{$error}</li>\n";
    }
    echo "</ul>\n";
}

echo <<<XHTML
</body>
</html>
XHTML;
