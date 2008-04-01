<?php

require_once SCRIPT_BASE.'roxlauncher/ptlauncher.php';
require_once SCRIPT_BASE.'roxlauncher/roxloader.php';



/**
 * methods in here get called by other methods in PTLauncher.
 *
 */
class RoxLauncher extends PTLauncher
{
    /**
     * this is called at some point to check some environment stuff.
     *
     */
    protected function checkEnvironment()
    {
        //BW Rox needs the GD plugin
        if (!PPHP::assertExtension('gd')) {
            die('GD lib required!');
        }
    }
    
    /**
     * we override the parent method that would only load the inc/config.inc.php
     * we get the information from ini files instead.
     * if required ini files are missing, they can be created automatically from inc/config.inc.php
     * if there is no inc/config.inc.php either, a warning is shown.
     *
     */
    protected function loadConfiguration()
    {
        // loads from an ini file, instead of a php file
        $loader = new RoxLoader(); 
        $settings = $loader->load(array(
            SCRIPT_BASE.'rox_default.ini',
            SCRIPT_BASE.$_SERVER['SERVER_NAME'].'.ini',
            SCRIPT_BASE.'rox_local.ini',
            SCRIPT_BASE.'rox_secret.ini'
        ));
        if (is_file(SCRIPT_BASE.'rox_local.ini')) {
            $this->_initRoxGlobals($settings);
            $this->_initBWGlobals();
        } else if (is_file(SCRIPT_BASE.'inc/config.inc.php')) {
            require_once SCRIPT_BASE.'roxlauncher/roxlocalsettingsimporter.php';
            $importer = new RoxLocalSettingsImporter();
            // the importer gets settings from the inc/config.inc.php
            $importer->importConfigPHP($settings);
            PPHP::PExit();
        } else {
            // TODO: A warning page, or even a setup form!
            echo '<pre>
    "'.SCRIPT_BASE.'rox_local.ini" not found.
    This file is needed for bw-rox to run.
    
    Trying to get read the old config file instead.
    "'.SCRIPT_BASE.'inc/config.inc.php" not found.
    
    Please copy the "'.SCRIPT_BASE.'rox_local.example.ini"
    to "'.SCRIPT_BASE.'rox_local.ini",
    and fill it with your local settings (database and baseuri).
            </pre>';
            PPHP::PExit();
        }
    }
    
    
    /**
     * globals are evil, but we need them, at least for legacy reasons.
     *
     * @param unknown_type $settings
     */
    private function _initRoxGlobals($settings)
    {
        foreach (array(
            'db' => 'config_rdbms',
            'smtp' => 'config_smtp',
            'mailAddresses' => 'config_mailAddresses',
            'request' => 'config_request',
            'google' => 'config_google',
            'chat' => 'config_chat',
            'env' => 'env'
        ) as $key => $value) {
            if(isset($settings[$key])) {
                PVars::register($value, $settings[$key]);
            }
        }
        if (!isset($settings['env']['session_name'])) {
            die('session name not set');
        } else {
            define('SESSION_NAME', $settings['env']['session_name']);  // the config.inc.php does this already
        }
    }
    
    /**
     * we need even more globals for bewelcome.
     *
     */
    private function _initBWGlobals()
    {
        //********************************************************
        // LEGACY CODE FROM TRADITIONAL BW CONFIGURATION
        //********************************************************
        
        // TODO: remove this when the old bw part is no longer needed.
        
        global $_SYSHCVOL;
        $_SYSHCVOL = array();
        
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

    /**
     * some fields in the $_SESSION need to be filled with default values, if they are empty.
     *
     */
    protected function fillSessionWithValues()
    {
        // TODO: This is maybe not the best place to do this,
        // but so far I don't know a better one.
        if (!isset($_SESSION['lang']) || !isset($_SESSION['IdLanguage'])) {
            // normally either none or both of them are set.
            $_SESSION['lang'] = 'en';
            $_SESSION['IdLanguage'] = 0;
        }
        PVars::register('lang', $_SESSION['lang']);
    }
    
    
    /**
     * prepare the php __autoload mechanism.
     *
     */
    protected function initAutoload()
    {
        parent::initAutoload();
        
        $class_loader = Classes::get();
        
        // a hack that allows to use ini files for the autoload stuff.
        foreach (scandir(SCRIPT_BASE.'build') as $dir) {
            $dir = SCRIPT_BASE.'build/'.$dir;
            if (!is_dir($dir)) {
                // echo ' - not a dir';
            } else if (!is_file($filename = $dir.'/autoload.ini')) {
                // echo ' - not a file';
            } else if (!is_array($ini_settings = parse_ini_file($filename))) {
                // echo ' - not an array';
            } else foreach ($ini_settings as $key => $value) {
                $classes = split("[,\n\r\t ]+", $value);
                $file = $dir.'/'.$key;
                foreach ($classes as $class) {
                    $class_loader->addClass($class, $file);
                }
            }
        }
    }
    
    /**
     * This is called from
     * htdocs/bw/lib/tbinit.php
     */
    public function initBW()
    {
        // load data in base.xml
        $this->loadBaseXML();
        
        // load essential framework libraries
        $this->loadFramework();
        
        $S = PSurveillance::get();
        
        $this->checkEnvironment();
        $this->loadConfiguration();
        $this->loadDefaults();
        
        PSurveillance::setPoint('base_loaded');
        
        $this->initSession();
        $this->initAutoload();
        
        // TODO: why do we need this?
        new RoxController;
    }
    
    /**
     * call the PT posthandler, which does something with $_POST requests.
     *
     */
    protected function doPostHandling()
    {
        // do this in the RoxFrontRouter instead!
        // PPostHandler::get();
    }
    
    /**
     * choose a controller and call the index() function.
     * If necessary, flush the buffered output.
     */
    protected function chooseAndRunApplication()
    {
        $router = new RoxFrontRouter();
        $router->inject('request', PRequest::get()->request);
        $router->inject('post_args', $_POST);
        $router->inject('get_args', $_GET);
        $router->route();
    }
    
}


?>