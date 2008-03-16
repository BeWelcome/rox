<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * Access to system settings and environment variables
 * The module allows to keep the config file safe and brief.
 * We might decide to reject the use of this object..
 * 
 * @author Andreas (lemon-head)
 */
class MOD_env
{
    /**
     * Singleton instance
     * 
     * @var MOD_geo
     * @access private
     */
    private static $_instance;
    
    private $_baseuri;
    private $_dao;
    private $_local_settings;
    
	private function __construct()
    {
        if ($this->_loadConfig_ini()) {
            $this->_setGlobals();
        } else if (is_file(SCRIPT_BASE.'inc/config.inc.php')) {
            // load from inc/config.inc.php
            global $_SYSHCVOL;
            $_SYSHCVOL = array();
            require SCRIPT_BASE.'inc/config.inc.php';
        } else {
            echo '<pre>
    No config file found.
    
    Please create either a file
    <b>'.SCRIPT_BASE.'inc/config.inc.php</b>
    as a modified copy of
    '.SCRIPT_BASE.'inc/config.inc.php.example
    
    or a file
    <b>'.SCRIPT_BASE.'rox_local.ini</b>
    as a modified copy of
    '.SCRIPT_BASE.'rox_local.example.ini
            </pre>';
            PPHP::PExit();
        }
        /*
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->_dao =& $dao;
        */
    }
    
    
    /**
     * loads the setup from 'inc/config.inc.php'
     * This happens only one time, when the singleton instance of MOD_env is created.
     *
     */
    private function _loadConfig_ini()
    {
        if (!is_file(SCRIPT_BASE.'config.ini')) return false;
        $this->_local_settings = parse_ini_file(SCRIPT_BASE.'config.ini', true);
        return true;
    }
    
    private function _setGlobals()
    {
        foreach (array(
            'db' => 'config_rdbms',
            'smtp' => 'config_smtp',
            'mailAddresses' => 'config_mailAddresses',
            'request' => 'config_request',
            'google' => 'config_google',
            'chat' => 'config_chat',
            'env' => 'env',
        ) as $key => $value) {
            if(isset($this->_local_settings[$key])) {
                PVars::register($value, $this->_local_settings[$key]);
            }
        }
        define('SESSION_NAME', $this->getConfig('env', 'session_name'));
        
        global $_SYSHCVOL;
        $_SYSHCVOL = array();
        
        //********************************************************
        // LEGACY CODE FROM TRADITIONAL BW CONFIGURATION
        //********************************************************
        
        // TODO: remove this when the old bw part is no longer needed.
        
        $_SYSHCVOL['MYSQLServer'] = "localhost";
        $_SYSHCVOL['MYSQLUsername'] = PVars::getObj('config_rdbms')->user;
        $_SYSHCVOL['MYSQLPassword'] = PVars::getObj('config_rdbms')->password;
        $_SYSHCVOL['MYSQLDB'] = substr(strstr(PVars::getObj('config_rdbms')->dsn,"dbname="),strlen("dbname=")); // name of the main DB
        
        // We want autoupdates
        $_SYSHCVOL['NODBAUTOUPDATE'] = 0;
        
        // Full path here since it has to work from any directory!
        // $_SYSHCVOL['SessionDirectory'] = "/var/www/html/sessiondir";
        
        // Leave these empty for test environment
        $_SYSHCVOL['ARCH_DB'] = ""; // name of the archive DB
        $_SYSHCVOL['CRYPT_DB'] = ""; // name of the crypted DB
        
        // This parameter if set to True will force each call to HasRight to look in
        // the database, this is usefull when a right is update to force it to be used 
        // immediately, of course in the long run it slow the server 
        $_SYSHCVOL['ReloadRight'] = 'False';
        
        // This parameter if the name of the database with (a dot) where are stored crypted data, there is no cryptation it it is left blank
        $_SYSHCVOL['Crypted'] = $_SYSHCVOL['CRYPT_DB'].'.';  
        
        $_SYSHCVOL['SiteName'] = substr(substr(PVars::getObj('env')->baseuri,strlen("http://")),0,strpos(substr(PVars::getObj('env')->baseuri,strlen("http://")),'/')); // This is the name of the web site
        $_SYSHCVOL['MainDir'] = substr(substr(PVars::getObj('env')->baseuri,strlen("http://")),strpos(substr(PVars::getObj('env')->baseuri,strlen        ("http://")),'/')) . "bw/"; // This is the name of the web site
        $_SYSHCVOL['IMAGEDIR'] = "/var/www/upload/images/";
        $_SYSHCVOL['WWWIMAGEDIR'] = "http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir']."/memberphotos";
        
        // this is the e-mail domain; we might use "bewelcome.org" on our productive system, but while development it is probably "localhost"
        $_SYSHCVOL['EmailDomainName'] = "example.org";
        $_SYSHCVOL['MessageSenderMail'] = 'message@' . $_SYSHCVOL['EmailDomainName']; // This is the default mail used as mail sender
        $_SYSHCVOL['CommentNotificationSenderMail'] = 'admincomment@' . $_SYSHCVOL['EmailDomainName']; // This is the mail which receive notification about Bad comments
        $_SYSHCVOL['NotificationMail'] = 'comment@' . $_SYSHCVOL['EmailDomainName']; // This is the default mail used to notify a member about a comment
        $_SYSHCVOL['ferrorsSenderMail'] = 'ferrors@' . $_SYSHCVOL['EmailDomainName']; // This is the mail in case of mail error
        $_SYSHCVOL['SignupSenderMail'] = 'signup@' . $_SYSHCVOL['EmailDomainName']; // This is the mail use by signup page for sending access
        $_SYSHCVOL['AccepterSenderMail'] = 'accepting@' . $_SYSHCVOL['EmailDomainName']; // This is the mail use by accepter action
        $_SYSHCVOL['FeedbackSenderMail'] = 'feedbackform@' . $_SYSHCVOL['EmailDomainName']; // This is the mail use to send mail to volunteers
        $_SYSHCVOL['TestMail'] = 'testmail@' . $_SYSHCVOL['EmailDomainName']; // This is the sender to use with the TestMail feature
        $_SYSHCVOL['MailToNotifyWhenNewMemberSignup'] = 'user@example.org'; // This is the e-mail address, which is notified, when a new member has signed up
        
        // These are the possible Qualifier for the comments
        $_SYSHCVOL['QualityComments'] = array (
            'Good',
            'Neutral',
            'Bad'
        ); 
        
        $_SYSHCVOL['SiteStatus'] = "Open"; // This can be "Closed" or "Open", depend if the site is to be closed or open
        $_SYSHCVOL['SiteCloseMessage'] = "The site is temporary closed"; // Message wich is displayed when the site is closed
         
        // possible answers for accomodation
         $_SYSHCVOL['Accomodation'] = array (   'dependonrequest',  'neverask', 'anytime');
        
        // possible lenght of stay
        $_SYSHCVOL['LenghtComments'] = array ('hewasmyguest', 'hehostedme', 'OnlyOnce', 'HeIsMyFamily', 'HeHisMyOldCloseFriend','NeverMetInRealLife');
        
        $_SYSHCVOL['EvaluateEventMessageReceived'] = "Yes"; // If set to "Yes" events messages received is evaludated at each page refresh
        $_SYSHCVOL['UploadPictMaxSize'] = 500000; // This define the size of the max loaded pictures
        $_SYSHCVOL['AgeMinForApplying'] = 18; // Minimum age a wannabe member must have to become a member 
        $_SYSHCVOL['WhoIsOnlineActive'] = 'Yes'; // Wether who is online is active can be Yes or No 
        $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] = 10; // The delay of non activity to consider a member off line 
        $_SYSHCVOL['WhoIsOnlineLimit'] = 11; // This limit the number of whoisonline, causing the display of ww('MaxOnlineNumberExceeded') at login for new loggers 
        $_SYSHCVOL['EncKey'] = "YEU76EY6"; // encryption key 
    }
    
    
    public function getConfig($key_1, $key_2)
    {
        if (!$category = $this->_local_settings[$key_1]) {
            return 0;
        } else if (!$value = $category[$key_2]) {
            return 0;
        } else {
            return $value;
        }
    }
    
    
    /**
     * singleton getter
     * 
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    
    
    
    public static function getBaseURI()
    {
        return _get()->_baseuri;
    }
    
    
    /**
     * Gets a 'dao' access object for the usual rox database.
     * If we want a different db, this can be a different getter function.
     * 
     * @return dao access object for the usual rox database
     */
    public static function getDAO()
    {
        return _get()->_dao;
    }
}
?>