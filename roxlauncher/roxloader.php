<?php


class RoxLoader
{
    private $_ini_settings = 0;
    private $_rox_local_ini_found = false;
    
    public function get($section = 0, $key = 0)
    {
        if (!self::$_ini_settings) self::_load();
        if (!$section) {
            // copy everything, to be sure.
            $res = array();
            foreach (self::$_ini_settings as $sec => $contents) {
                $res[$sec] = array();
                foreach ($contents as $k => $value) {
                    $res[$sec][$k] = $value;
                }
            }
            return $res;
        }
        if (!isset (self::$_ini_settings[$section])) return 0;
        if (!$key) {
            // copy the section, to be sure.
            $res = array();
            foreach(self::$_ini_settings[$section] as $k => $value) {
                $res[$k] = $value;
            }
            return $res;
        }
        if (!isset (self::$_ini_settings[$section][$key])) return 0;
        return self::$_ini_settings[$section][$key];
    }
    
    public static function initGlobals()
    {
        if (!self::$_ini_settings) self::_load();
        
        foreach (array(
            'db' => 'config_rdbms',
            'smtp' => 'config_smtp',
            'mailAddresses' => 'config_mailAddresses',
            'request' => 'config_request',
            'google' => 'config_google',
            'chat' => 'config_chat',
            'env' => 'env'
        ) as $key => $value) {
            if(isset(self::$_ini_settings[$key])) {
                PVars::register($value, self::$_ini_settings[$key]);
            }
        }
        define('SESSION_NAME', self::get('env', 'session_name'));  // the config.inc.php does this already
        
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
    
    public function load($files)
    {
        $ini_settings = array();
        if (!is_array($files)) {
            // do nothing, leave $ini_settings empty
        } else foreach ($files as $filename) {
            echo $filename.'<br>';
            if (is_file($filename)) {
                $sections = parse_ini_file($filename, true);
                foreach ($sections as $section => $contents) {
                    if(!isset($ini_settings[$section])) {
                        $ini_settings[$section] = array();
                    }
                    foreach ($contents as $key => $value) {
                        $ini_settings[$section][$key] = $value;
                    }
                }
            }
        }
        
        return $ini_settings;
    }
    
    private static function _load_ini($filename)
    {
        if (!is_file($filename)) {
            return false;
        } else {
            $sections = parse_ini_file($filename, true);
            foreach ($sections as $section => $contents) {
                if(!isset(self::$_ini_settings[$section])) {
                    self::$_ini_settings[$section] = array();
                }
                foreach ($contents as $key => $value) {
                    self::$_ini_settings[$section][$key] = $value;
                }
            }
            return true;
        }
    }
    
    public static function create_ini_file()
    {
        // settings from ini file
        self::_load();
        
        if(self::$_rox_local_ini_found) {
            // rox_local.ini exists, don't need to create.
        } else {
            // the rox_local.ini is missing, and has to be created.
            
            // where is PVars different from the ini settings?
            $differences = array();
            foreach (array(
                'db' => 'config_rdbms',
                'smtp' => 'config_smtp',
                'mailAddresses' => 'config_mailAddresses',
                'request' => 'config_request',
                'google' => 'config_google',
                'chat' => 'config_chat',
                'env' => 'env'
            ) as $section => $objectname) {
                if(
                    ($object = PVars::getObj($objectname)) &&
                    isset(self::$_ini_settings[$section])
                ) {
                    $settings_pvars[$section] = array();
                    foreach (self::$_ini_settings[$section] as $key => $value) {
                        $settings_pvars[$section][$key] = $object->__get($key);
                        if (self::$_ini_settings[$section][$key] != $object->__get($key)) {
                            if (!isset($differences[$section])) {
                                $differences[$section] = array(); 
                            }
                            $differences[$section][$key] = $object->__get($key);
                        }
                        echo '.';
                    }
                    echo ':';
                }
                echo ';';
            }
            
            // some things are forced to be explicit.
            if (!isset($differences['db'])) $differences['db'] = array();
            if (!isset($differences['env'])) $differences['env'] = array();
            $differences['db']['dsn'] = PVars::getObj('config_rdbms')->dsn;
            $differences['db']['user'] = PVars::getObj('config_rdbms')->user;
            $differences['db']['password'] = PVars::getObj('config_rdbms')->password;
            $differences['env']['baseuri'] = PVars::getObj('env')->baseuri;
            
            // create the string to write into the file
            $res = "";
            foreach ($differences as $category => $contents) {
                $res .= "\n[$category]\n";
                foreach ($contents as $key => $value) {
                    $res .= "$key = \"$value\"\n";
                }
            }
            $filename = SCRIPT_BASE.'rox_local.ini';
            if (!$file_handle = fopen($filename, 'w')) {
                // didn't work..
                echo '<br>Rox_ini::create_ini_file() tried to create a new file "'.$filename.'", but failed to open the file handle.<br>';
                return;
            } else if (!fwrite($file_handle, $res)) {
                // didn't work..
                echo '<br>Rox_ini::create_ini_file() tried to create a new file "'.$filename.'", but failed to write to the file handle.<br>';
                return;
            } else {
                // it worked!
                echo '<br>Rox_ini::create_ini_file() has successfully created a new file "'.$filename.'", that will from now on replace your "config.inc.php".<br>';
            }
            if (!fclose($file_handle)) {
                echo 'And now failed to close the file again? Ouch!<br>';
                return;
            }
        }
    }
}


?>