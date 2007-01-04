<?php
/**
 * Configuration
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright &copy; 2005, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: config.inc.php.example 161 2006-10-02 07:51:26Z david $
 */
// database dsn (see: http://www.php.net/manual/en/ref.pdo.php)
$db['dsn']  = 'mysql:host=localhost;dbname=mtb';
// username
$db['user']     = 'mtbkey';
// password
$db['password'] = 'masubito';

// SMTP server config
$smtp['backend']  = 'mail'; // "mail", "smtp" or "sendmail"
$smtp['host']     = 'mail.server.example';
$smtp['port']     = 25;
$smtp['auth']     = true;
$smtp['username'] = 'mailuser';
$smtp['password'] = 'mailpassword';

// mail addresses
$mailAddresses['registration'] = 'registration@bewelcome.org';

$request['prefix'] = '';

$env['baseuri']       = 'http://ns20516.ovh.net/travelbook/framework/htdocs/';
$env['cookie_prefix'] = 'mytravelbook_';
$env['session_name']  = 'sid';
$env['suspend_url']   = 'http://www.path.to.my.page.example/suspendinfo.html';

// Your Google Maps API Key (only valid per domain/directory)
$google['maps_api_key'] = 'ABQIAAAA-eKVrlUbv2I1NZ-U4kXTbBR0Wi6UD1Nm3nBw2fbF9VRK5P5NLBSD6XpJkYsnthi8dduo_G3FyT-FFQ';

$google['geonames_webservice'] = 'http://ecommunity.ifi.unizh.ch/webservice/?name={query}&maxRows={rows}';

//*******************************************************
// PLEASE DO NOT EDIT BEYOND THIS LINE
//*******************************************************

PVars::register('config_rdbms', $db);
unset($db);
PVars::register('config_smtp', $smtp);
unset($smtp);
PVars::register('config_mailAddresses', $mailAddresses);
unset($mailAddresses);
PVars::register('config_request', $request);
unset($request);
PVars::register('config_google', $google);
unset($google);
PVars::register('env', $env);
define('SESSION_NAME', $env['session_name']);
unset($env);
?>