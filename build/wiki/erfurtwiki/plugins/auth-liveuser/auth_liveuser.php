<?php

/**
 * Copyright (c) 2003, The Burgiss Group, LLC
 * This source code is part of eWiki LiveUser Plugin.
 *
 * eWiki LiveUser Plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * eWiki LiveUser Plugin is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Wiki LiveUser Plugin; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * ewiki: liveuser authentication plugin
 *
 * @author andy fundinger <afundinger@burgiss.com>
 * @author alex wan <alex@burgiss.com>
 * @author jeremy mikola <jmikola@arsjerm.net>
 *
 * Without auth_perm_ring or perm_liveuser this plugin will merely require 
 * authentication to edit (wherein ring is set to two) and hide all control 
 * links from users who are not logged in if EWIKI_PROTECTED_MODE_HIDING is set.
 * 
 * A login form will be displayed if you try to edit without logging in.
 *
 * This plugin opens it's own database and must be included BEFORE the ewiki 
 * database is opened.
 *
 * @contributer jeffrey engleman
     -added ewiki_check_passwd
 */
 
require_once(dirname(__FILE__).'/liveuser_aux.php');

define('EWIKI_LOGGEDIN_RING', 1);
define('EWIKI_MIN_DICT_WORD_LENGTH', 3);
//Path to dictionary file, passdict is included for this 
//define('EWIKI_PASS_DICT_PATH', '/path/to/dictionary');
define('EWIKI_PASSWORD_COMPLEXITY', 56); 
//Maximum delay before shutting down the system (in seconds)
define('EWIKI_LIVEUSER_LOGIN_SHUTDOWN_DELAY', 30);

// Set passwords to expire in 180 days, actually expiring passwords requires:
//  * a uservars plugin
//  * /tools/scripts/passwd_expiration to run daily (not yet released)
//  * the /plugins/passwd_expire plugin (not yet released)
define('EWIKI_PASSWD_LIFETIME',180);
/*
log logins for guess rate limiter, table structure should be:

CREATE TABLE `liveweb_login_log` (
  `auth_user_handle` varchar(32) NOT NULL default '',
  `php_session_id` varchar(40) NOT NULL default '',
  `ssl_session_id` varchar(16) default '',
  `ip_address` varchar(16) NOT NULL default '',
  `time` timestamp(14) NOT NULL,
  `delay` tinyint(1) NOT NULL default '0',
  `success` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`time`,`auth_user_handle`)
) TYPE=MyISAM; 

in the LiveUser database

if you allow only SSL connections set ssl_session_id to "NOT NULL"

When enabled a failure to successfully log will result in a die() call.
*/

@define('EWIKI_AUTH_DEFAULT_RING', 3);
define('EWIKI_NOT_LOGGEDIN_RING', EWIKI_AUTH_DEFAULT_RING);

// ewiki callbacks for auth query, login, logout, and changePassword methods
$ewiki_plugins['auth_query'][0]          = 'ewiki_auth_query_liveuser';
$ewiki_plugins['page']['LogIn']          = 'ewiki_page_liveuser_login';
$ewiki_plugins['page']['LogOut']         = 'ewiki_page_liveuser_logout';
$ewiki_plugins['page']['ChangePassword'] = 'ewiki_page_liveuser_chpw';

/* 
Policies for the guess rate limiter, name entry descriptively for alerts:
array(
    'where' => Where clause for interesting records "?" is current username
    'count' => Empty entry, will store count of respondant records when queried
    'threshold' => Minimum records before adding delay
    'coefficient' => delay to add per record
    'alert_mod' => alerts will be sent when count%alert_mod==0
)
*/

//Slow down if 50 bad passwords have been entered this hour.
$liveuser_delay_policies['Hourly_bad_PWs']=array(
    'where' => 'time> DATE_SUB(NOW(), INTERVAL 1 HOUR)',
    'count' => 0,
    'threshold' => 50,
    'coefficient' => 5/120,
    'alert_mod' => 50
);
//Slow down if 200 bad passwords have been entered today.
$liveuser_delay_policies['Daily_bad_PWs']=array(
    'where' => 'time> DATE_SUB(NOW(), INTERVAL 1 DAY)',
    'count' => 0,
    'threshold' => 200,
    'coefficient' => .0357,
    'alert_mod' => 200
);
//Send alert if one account gets 10 bad passwords in a day
$liveuser_delay_policies['Acct_bad_PWs']=array(
    'where' => 'time> DATE_SUB(NOW(), INTERVAL 1 DAY)  AND auth_user_handle=?',
    'count' => 0,
    'threshold' => 0,
    'coefficient' => 0,
    'alert_mod' => 10
);
	//Who to notify
    define('ALERT_RECIPIENTS', 'webmaster@127.0.0.1');
    define('ALERT_SUBJECT',"LU IDS ALERT");

/* ignore username and password form data if cancelling a login process */
/* otherwise calculate and ennact a login delay.*/
if (isset($_REQUEST['username'])&&!isset($_REQUEST['cancel_login'])){ 
    $username=$_REQUEST['username'];    
    $authlog="LOGIN ATTEMPT USERNAME:".$username." cancel_login==".$_REQUEST['cancel_login'];
    
    liveweb_query_delay_data($liveuser_delay_policies);
    //var_dump($liveuser_delay_policies);
    
    $totalDelay=liveweb_get_total_delay($liveuser_delay_policies);
    
    //echo(" total delay $totalDelay");

    if($totalDelay>EWIKI_LIVEUSER_LOGIN_SHUTDOWN_DELAY){
        $liveuserConfig['login']['username'] = ''; 
        $liveuserConfig['login']['password'] = '';        
    }else{
        //using usleep rather than sleep() because sleep() is integer seconds only.
        usleep(1000000*$totalDelay);    
    }
        
}else{    
    $liveuserConfig['login']['username'] = ''; 
    $liveuserConfig['login']['password'] = '';
}


/*  Setting $liveuserConfig['login']['username'] and $liveuserConfig['login']['password']
*   to '' causes the login to be ignored by the LiveUser system.
*   In Liveuser.php during the tryLogin function on line 665,  
*   it sees the handle is empty.  It then tries to login based on a cookie,
*   but in line 171 that _options['cookie'] is not set so it goes to line 693
*   sees that _options['login']['username'] and _options['login']['password'] 
*   are empty, tries to run _options['login']['function'] which is also set to ''
*   so it fails out of the if and hits line 715 where it returns false negating the login.
*/

// instantiate a LiveUser object from the config array
$liveuser =& LiveUser::factory($liveuserConfig);

if (isset($_REQUEST['username'])&&!isset($_REQUEST['cancel_login'])){ 
    if($totalDelay>EWIKI_LIVEUSER_LOGIN_SHUTDOWN_DELAY){
        $liveuser->logout();
    }else{
        //Get data as we would for logging        
        $loginData=ewiki_liveuser_get_login_data();
        liveuser_loglogin(); 
        //Tests login, updates $username
        if ($username=$liveuser->getHandle()){
            //Clear delay flags with matching handle, php session, ssl session, and ip
            // (today only)
            $liveuserDB->query('
                UPDATE `liveweb_login_log` set delay=0 
                WHERE time> DATE_SUB(NOW(), INTERVAL 1 DAY) 
                AND auth_user_handle=? AND php_session_id=?
                AND ssl_session_id=? AND ip_address=?',
                array($username,$loginData['php_session_id'],
                $loginData['ssl_session_id'],$loginData['ip_address']));
        }else{//Failed login attempt with logins enabled
            $authlog.="LOGIN FAILED USERNAME:".$_REQUEST['username']." PASSWORD:".$_REQUEST['password'];
            ewiki_liveuser_IDS_alerts($liveuser_delay_policies,$loginData['auth_user_handle']);
        }
    }
}

//Tests login, updates $username NB: $username may or may not have been set above.
if($username=$liveuser->getHandle()) {
    //Reset username based on the autheticated username, should be no change.
    $GLOBALS['ewiki_author'] = $username;
    $GLOBALS['ewiki_auth_user'] = $username;
} else {
    $GLOBALS['ewiki_author'] = null;
    $GLOBALS['ewiki_auth_user'] = null;
    define('EWIKI_AUTO_EDIT',0);
}            
         
// html page output response messages
$ewiki_t['en']['LOGINSHUTDOWN']     = "<p>We're sorry, but logins are temporarily disabled, please try again later.</p>";
$ewiki_t['en']['CANNOTCHANGEPAGE']  = '<p>This page cannot be changed. Perhaps you can <a href="'.EWIKI_SCRIPT.'LogIn">LogIn</a> and change it then.</p>';
$ewiki_t['en']['NOTLOGGEDIN']       = '<p>You are not logged in. You must <a href="'.EWIKI_SCRIPT.'LogIn">LogIn</a> to access some features of this site.</p>';
$ewiki_t['en']['LOGGEDINALREADY']   = '<p>You are already logged in.</p>';
$ewiki_t['en']['LOGGEDIN']          = '<p>You have logged in as '.$GLOBALS['ewiki_author'].'.';
$ewiki_t['en']['LOGGEDOUT']         = '<p>You have been logged out.</p>';
$ewiki_t['en']['LOGINFAILED']       = '<p>You supplied an invalid username or password while logging in.</p>';
$ewiki_t['en']['LOGINFORM']         = '
    <p>Please identify yourself with a username and a password:</p>
    
    <form action="" method="post">
    <table border="0" bgcolor="#eeeeee" cellspacing="0" cellpadding="4">
    <tr valign="top" align="left">
        <td>Username:</td>
        <td><input type="text" name="username" size="32" maxlength="32"></td>
    </tr>
    <tr valign="top" align="left">
        <td>Password:</td>
        <td><input type="password" name="password" size="32" maxlength="32"></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td align="right">
            <input type="submit" name="submit_login" value="Login now">
            <input type="submit" name="cancel_login" value="Cancel">            
        </td>
    </tr>
    </table>
    </form>';
    

$ewiki_t['en']['CHPW_BADNEW_COMPLEXITY']= '<p>Your password has been deemed insecure because it did not meet the complexity requirements.</p>
<p>Suggested Remedies:</p><ul><li>Increase the length of your new password.</li><li>Combine upper and lower case letters</li>
<li>Use special characters and numbers.</li></ul>';
$ewiki_t['en']['CHPW_BADNEW_USERNAME']='<p>Your password has been deemed insecure because it matches your username too closley.</p><p>Suggested Remedy:</p><ul><li>Do not use your username
in your password in any way.</li></ul>';
$ewiki_t['en']['CHPW_BADNEW_DICTIONARY']='<p>Your password has been deemed insecure because part of it can be found on our list of common passwords.</p><p>Suggested Remedy:</p>
<ul><li>Remove all dictionary words from your password.</li><li>Remove common sequences from your password.</li></ul>';
$ewiki_t['en']['CHPW_BADOLD'] = '<p>You have misentered your old password.</p>';
$ewiki_t['en']['CHPW_SUCCESS']  = '<p>Your password has been changed.</p>';
$ewiki_t['en']['CHPW_NOMATCH']  = '<p>Your new passwords did not match.  Please re-enter them.</p>';
$ewiki_t['en']['CHPW_SAMEOLD'] = '<p>Your new password is the same as your old password.</p>';
$ewiki_t['en']['CHPW_ERROR']  = '<p>An error occurred while attempting to change your password.</p>';
$ewiki_t['en']['CHPW_FORM'] = '
    <p>Please enter your old password once and your new password twice in the blanks below:</p>

    <form action="?id=ChangePassword" method="post">
    <table border="0" bgcolor="#eeeeee" align="center" cellspacing="0" cellpadding="4">
    <tr valign="top" align="left">
        <td>Old Password:</td>
        <td><input type="password" name="oldpassword" size="32" maxlength="32"></td>
    </tr>
    <tr valign="top" align="left">
        <td>New Password:</td>
        <td><input type="password" name="newpassword1" size="32" maxlength="32"></td>
    </tr>
    <tr valign="top" align="left">
        <td>Repeat New Password:</td>
        <td><input type="password" name="newpassword2" size="32" maxlength="32"></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td align=right>
            <input type="submit" name="submit" value="Change Password">
            <input type="button" name="cancel_pwchng" value="Cancel">            
        </td>
    </tr>
    </table>
    </form>';
$ewiki_t['en']['PASS_DICTIONARY_READ_ERROR'] = "<p>An error was encountered while validating your password.</p>";
/**
 * processes login for the current user.
 *
 * @param mixed id
 * @param mixed data
 * @return string page output response to login attempt
 */
function ewiki_page_liveuser_login($id, $data)
{
    global $liveuser,$liveuser_delay_policies;

    liveweb_query_delay_data($liveuser_delay_policies);
    //var_dump($liveuser_delay_policies);
    
    $totalDelay=liveweb_get_total_delay($liveuser_delay_policies);

    $o=ewiki_make_title($id, $id, 2);
    if($totalDelay > EWIKI_LIVEUSER_LOGIN_SHUTDOWN_DELAY){
        $o.=ewiki_t('LOGINSHUTDOWN');
        return($o);
    }else{
        if (isset($_REQUEST['submit_login']) or isset($_REQUEST['submit_login_img_x'])) {
            // login form submission, print form response
            return $o.($liveuser->isLoggedIn() ? ewiki_t('LOGGEDIN') : ewiki_t('LOGINFAILED').ewiki_t('LOGINFORM'));
        } else {
            // all other calls, print login status or form output
            return $o.($liveuser->isLoggedIn() ? ewiki_t('LOGGEDINALREADY') : ewiki_t('LOGINFORM'));
        }    
    }
}

/**
 * logs out the current user.
 *
 * @param mixed id
 * @param mixed data
 * @return string page output for logout message
 */
function ewiki_page_liveuser_logout($id, $data)
{
    global $liveuser;
    
    $liveuser->logout();
    return ewiki_make_title($id, $id, 2).ewiki_t('LOGGEDOUT');
}

/**
 * changes current user's password based on form input
 *
 * @param mixed id
 * @param mixed data
 * @return mixed
 */
function ewiki_page_liveuser_chpw($id, $data)
{ 
    global $liveuser, $liveuserAuthAdmin;

    // if form was not submitted, return page output for form
    if (!isset($_REQUEST['oldpassword'])) {
        return ewiki_make_title($id, $id, 2).ewiki_t('CHPW_FORM');
    }
        
    // ensure that original password is valid, and that new passwords match
    if ($liveuser->getProperty('passwd') != $liveuserAuthAdmin->encryptPW($_REQUEST['oldpassword'])) {
        return ewiki_make_title($id, $id, 2).ewiki_t('CHPW_BADOLD').ewiki_t('CHPW_FORM');
    } else if ($_REQUEST['newpassword1'] != $_REQUEST['newpassword2']) {
        return ewiki_make_title($id, $id, 2).ewiki_t('CHPW_NOMATCH').ewiki_t('CHPW_FORM');
    } else if ($_REQUEST['newpassword1'] == $_REQUEST['oldpassword']){
        return ewiki_make_title($id, $id, 2).ewiki_t('CHPW_SAMEOLD').ewiki_t('CHPW_FORM');
    }
    //$time=getmicrotime();
    $password_status=ewiki_check_passwd($_REQUEST['newpassword1'],$liveuser->getHandle());
    //$end=getmicrotime();
    //echo($end-$time);
    if ($password_status!='good passwd') {
      if($password_status=='read error'){
        return ewiki_make_title($id, $id, 2).ewiki_t('PASS_DICTIONARY_READ_ERROR');
      } else {
        return ewiki_make_title($id, $id, 2).ewiki_t($password_status).'<!--'.$password_status.'-->'.ewiki_t('CHPW_FORM');
      }
    }
    
    // return success
    if ($liveuserAuthAdmin->updateUser($liveuser->getProperty('authUserId'), $liveuser->getHandle(), $_REQUEST['newpassword2']) === true) {
        ewiki_set_uservar("passwdstatus", 'good', $GLOBALS['ewiki_auth_user']);
        ewiki_set_uservar("passwdexpiredate", time()+(60*60*24*EWIKI_PASSWD_LIFETIME),$GLOBALS['ewiki_auth_user']);
        return ewiki_make_title($id, $id, 2).ewiki_t('CHPW_SUCCESS');
    } else {
        return ewiki_make_title($id, $id, 2).ewiki_t('CHPW_ERROR');
    }
}


/**
 * checks if the current user is logged in. this function will alter the output
 * parameter if the cancel login form event is being processed. 
 *
 * @param string data page data (not used)
 * @param string ewiki_author
 * @param int ewiki_ring
 * @param int force_query
 * @global string ewiki_errmsg returns error message for user on failure
 * @return boolean true if the current user is logged in, false otherwise
 */
function ewiki_auth_query_liveuser(&$data, $force_query = 0)
{
    global $liveuser, $ewiki_config, $ewiki_errmsg, $ewiki_ring;
    
    if ($liveuser->isLoggedIn()) {
        $ewiki_ring = EWIKI_LOGGEDIN_RING;
        return true;
    } else {    
        if ($force_query){
            if (isset($_REQUEST['cancel_login'])) {
                $ewiki_errmsg = ewiki_t('FORBIDDEN');
            } else if (isset($_REQUEST['submit_login'])) {
                $ewiki_errmsg = ewiki_t('LOGINFAILED').ewiki_t('LOGINFORM');
            } else {
                $ewiki_errmsg = ewiki_t('LOGINFORM');
            }
        }
	
        $liveuser->logout();
        $ewiki_ring = EWIKI_NOT_LOGGEDIN_RING;
	
        return false;
    }
}

//Gets general data about login for loging etc.
function ewiki_liveuser_get_login_data(){
    global $liveuser;

    $success=$liveuser->isLoggedIn();

    $requestInfo=array(
        'auth_user_handle'  =>  $_REQUEST['username'],
        'php_session_id'    =>  session_id(),
        'ssl_session_id'    =>  ($uu = $_ENV['SSL_SESSION_ID']?$uu:'SSL not enabled'),
        'ip_address'        =>  $_SERVER['REMOTE_ADDR'],
        'delay'             =>  !$success,
        'success'           =>  $success);
        
    return($requestInfo);
}

//logs current login
function liveuser_loglogin(){
    global $liveuserDB;

    if (!isset($_REQUEST['username'])){
        return;
    }
    
    $requestInfo= ewiki_liveuser_get_login_data();
  
    //store ip and sslid in session variables so we can check them later.
    $_SESSION['loginInfo']['ip_address']=$requestInfo['ip_address'];
    $_SESSION['loginInfo']['ssl_session_id']=$requestInfo['ssl_session_id'];
    
    //var_dump($requestInfo);
    
    // You must create a new array to pass to this function, passing 
    //    $requestInfo does not work
    if($liveuserDB->query('INSERT INTO '.LW_PREFIX.'_login_log (auth_user_handle, '.
        'php_session_id, ssl_session_id,ip_address,delay,success,time) '. 
        'VALUES (?, ?, ?, ?, ?, ?, NOW())',
        array($requestInfo['auth_user_handle'],$requestInfo['php_session_id'],
            $requestInfo['ssl_session_id'],$requestInfo['ip_address'],
            $requestInfo['delay'],$requestInfo['success'] ))!=DB_OK)
        {
            die('Failure in database connection, unable to continue');
        }
    //*/      
}

// Calculates delay, utility function
function liveweb_delay_calc($count, $threshold, $coeff){
    return(max($count-$threshold,0)*$coeff);
}
function liveweb_get_total_delay($liveuser_delay_policies){
    $delay=0;

    foreach($liveuser_delay_policies as $currPolicy){
        $delay += liveweb_delay_calc($currPolicy['count'], $currPolicy['threshold'], $currPolicy['coefficient']);
    }
    
    return($delay);
}

//Populates 'count' with the number of respondant records
function liveweb_query_delay_data(&$liveuser_delay_policies){
    global $liveuserDB;

    foreach ($liveuser_delay_policies as $polNum => $currPolicy){
        $liveuser_delay_policies[$polNum]['count']=0+$liveuserDB->getOne('SELECT sum(delay) as delay_count from  '.
        '`'.LW_PREFIX.'_login_log` WHERE '.$currPolicy['where'],array($_REQUEST['username']));
    }
}

//Sends alerts if need be
function ewiki_liveuser_IDS_alerts($liveuser_delay_policies,$username){
    $send_alert=0;
    foreach ($liveuser_delay_policies as $polName => $currPolicy){
        if((++$liveuser_delay_policies[$polName]['count'])%$currPolicy['alert_mod']==0){
            $send_alert=1;
        }            
    }
            
    if(!$send_alert){   
      return;
    }
    $m_text = "";
        
    foreach ($liveuser_delay_policies as $polName => $currPolicy){
        $m_text.="$polName:  {$currPolicy['count']}(mod {$currPolicy['alert_mod']})\n";
    }
    
    ($server = $_SERVER["HTTP_HOST"]) or
    ($server = $_SERVER["SERVER_NAME"]);
    
    $m_text.="$username attempting to login\n";
    
    $m_text.= "\n(".EWIKI_PAGE_INDEX." on http://$server/)\n";                
    $m_text.=  $_SERVER["SERVER_ADMIN"]."\n";
    
    //$m_from = EWIKI_NOTIFY_SENDER."@$server";
    $m_from = "Alerts@$server";
    $m_subject = ALERT_SUBJECT;            
    $m_to = ALERT_RECIPIENTS;
    
    //echo("mail($m_to, $m_subject, $m_text, 'From: \'$s_2\' <$m_from>\nX-Mailer: ErfurtWiki/'.EWIKI_VERSION);");            
    mail($m_to, $m_subject, $m_text, "From: \"$s_2\" <$m_from>\nX-Mailer: ErfurtWiki/".EWIKI_VERSION);    

}

?>