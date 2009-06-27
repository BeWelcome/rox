#!/usr/bin/php
<?php

if (count($_SERVER['argv']) < 2 || !in_array(strtolower($_SERVER['argv'][1]), array('bull', 'mule')))
{
    die("Script needs input - call as follows: <script> mule|bull\n");
}

$table = strtoupper($_SERVER['argv'][1]);

$wiki_page = file_get_contents('http://www.bevolunteer.org/wiki/Listening_servers');
$info = substr($wiki_page,strpos($wiki_page, "START {$table} SERVER TABLE"), (strpos($wiki_page, "END {$table} SERVER TABLE") - strpos($wiki_page, "START {$table} SERVER TABLE")));

if (trim($info) == '')
{
    die('Could not get contents of http://www.bevolunteer.org/wiki/Listening_servers');
}

$info = str_replace(array("\r\n","\n", "\r"), '', $info);
$info = strip_tags(str_replace(array('</tr>','</td>'),array("\n",'---'), $info));

$wiki_array = explode("\n", $info);
array_shift($wiki_array);
array_pop($wiki_array);
foreach ($wiki_array as $key => $value)
{
    $fields = explode('---', $value);
    foreach ($fields as &$field)
    {
        $field = trim($field);
    }
    $wiki_array[$key] = $fields;
}

if (!exec('/bin/netstat -e --ip -vlp --numeric-hosts --numeric-ports', $output))
{
    die('Could not execute netstat command');
}

$ports = array();
foreach ($output as $line)
{
    if (preg_match('/((?:\d{1,3}\.){3}\d{1,3}):(\d+)\s*[0-9\.]+\:\*\s+(?:LISTEN)?\s+(\w+)\s+\d+\s+(\d+)\/(.+)$/', $line, $match))
    {
        array_shift($match);
        $ports[] = $match;
    }
}
$scream = false;

foreach ($wiki_array as $line)
{
    foreach ($ports as $port)
    {

        if ($line[0] == $port[2] && $line[1] == $port[4] && in_array($port[1], explode(',', $line[2])) && (($port[0] == '127.0.0.1' && strtolower($line[3]) == 'internal') ||($port[0] != '127.0.0.1' && strtolower($line[3]) == 'external')))
        {
            continue 2;
        }
    }
    echo "{$line[0]} is supposed to be running {$line[1]} (listening {$line[3]} on port {$line[2]}) on {$table}.bewelcome.org but the process does not appear in netstat.\n";
    $scream = true;
}

foreach ($ports as $port)
{
    foreach ($wiki_array as $line)
    {
        if (count(explode(',', $line[2])) > 1)
        {
            $p = explode(',', $line[2]);
            foreach ($p as $pea)
            {
                array_push($wiki_array, array($line[0], $line[1], $pea, $line[3]));
            }
            continue;
        }
        if ($line[0] == $port[2] && $line[1] == $port[4] && in_array($port[1], explode(',', $line[2])) && (($port[0] == '127.0.0.1' && strtolower($line[3]) == 'internal') ||($port[0] != '127.0.0.1' && strtolower($line[3]) == 'external')))
        {
            continue 2;
        }
    }
    echo "{$port[2]} is running {$port[4]} (listening on {$port[0]}.{$port[1]}) on {$table}.bewelcome.org but the process is not listed in the wiki.\n";
    $scream = true;
}
if ($scream)
{
    $location = realpath(dirname($_SERVER['PHP_SELF'])) . '/' . basename($_SERVER['PHP_SELF']);
    echo <<<TXT
Script uses the following input:
- wiki: http://www.bevolunteer.org/wiki/Listening_servers
- server: {$_SERVER['argv'][1]}

Script is running from: {$location}

TXT;
}
