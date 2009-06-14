#!/usr/bin/php
<?php

if (count($_SERVER['argv']) < 2)
{
    die("Script needs input - call as follows: <script> mule|bull\n");
}

switch(strtolower($_SERVER['argv'][1]))
{
    case "mule":
        $column = 2;
        break;
    case "bull":
        $column = 3;
        break;
    default:
        die('Bad input. Call with mule|bull');
}

$wiki_page = file_get_contents('http://www.bevolunteer.org/wiki/Accounts_and_groups_on_our_servers');
$info = substr($wiki_page,strpos($wiki_page, 'START ACCESS TABLE'), (strpos($wiki_page, 'END ACCESS TABLE') - strpos($wiki_page, 'START ACCESS TABLE')));
$info = str_replace(array("\r\n","\n", "\r"), '', $info);
$info = strip_tags(str_replace(array('</tr>','</td>'),array("\n",'---'), $info));

$wiki_array = explode("\n", $info);
array_shift($wiki_array);
array_pop($wiki_array);
foreach ($wiki_array as $key => $user)
{
    $fields = explode('---', $user);
    foreach ($fields as &$field)
    {
        $field = trim($field);
    }
    $wiki_array[$key] = $fields;
}

if(!exec("cd /home;find . -type d -name '\.ssh'", $output))
{
    die('Could not execute find');
}

$homes = array();
foreach ($output as $line)
{
    $parts = explode('/', $line);
    $homes[] = $parts[1];
}

$passwd = file_get_contents('/etc/passwd');
$users = explode("\n", $passwd);
$actual_users = array();
foreach ($users as $user)
{
    if (!trim($user))
    {
        continue;
    }
    $fields = explode(':', $user);
    $shell = array_pop($fields);
    if (strstr($shell, 'false') || strstr($shell, 'nologin'))
    {
        continue;
    }
    $actual_users[] = array_shift($fields);
}

$current_users = array();
foreach ($homes as $home)
{
    foreach ($actual_users as $user)
    {
        if ($home == $user)
        {
            $current_users[] = $user;
        }
    }
}

foreach ($current_users as $user)
{
    foreach ($wiki_array as $line)
    {
        if ($line[0] == $user && strtolower($line[$column]) == 'yes')
        {
            continue 2;
        }
    }
    echo "{$user} has login access to {$_SERVER['argv'][1]} but is not listed in the BV wiki.\n";
}

foreach ($wiki_array as $user)
{
    foreach ($current_users as $name)
    {
        if (($user[0] == $name && strtolower($user[$column]) == 'yes') || strtolower($user[$column]) == 'no')
        {
            continue 2;
        }
    }
    echo "{$user[0]} is listed in BV wiki but has no login access to {$_SERVER['argv'][1]}.\n";
}
