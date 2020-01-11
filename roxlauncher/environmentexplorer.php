<?php

/*
 * Processes PHP and server environement variables and sets up PHP
 * autoload.  A lot of this is taken from PT, inc/
 */

use App\Utilities\SessionTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zend\Uri\Exception\InvalidUriException;
use Zend\Uri\Http;


class EnvironmentExplorer
{
    use SessionTrait;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator = null) {
        $this->urlGenerator = $urlGenerator;
        $this->setSession();
    }

    public function initializeGlobalState($db_host, $db_name, $db_user, $db_password)
    {
        if (!defined('SCRIPT_BASE')) {
            define('SCRIPT_BASE', getcwd() . '/../');
            define('HTDOCS_BASE', SCRIPT_BASE . 'htdocs/');
            define('LIB_DIR', SCRIPT_BASE . 'lib/');
            define('BUILD_DIR', SCRIPT_BASE . 'build/');
            define('TEMPLATE_DIR', SCRIPT_BASE . 'templates/');
            define('DATA_DIR', SCRIPT_BASE . 'data/');
        }

        $dsn = sprintf('mysqli:host=%s;dbname=%s', $db_host, $db_name);

        $settings = $this->loadConfiguration();

        $settings = $this->mergeNewConfig($settings, $dsn, $db_user, $db_password);

        // No longer needed; session already started from Symfony code
        // $this->initSession($settings);

        PSurveillance::get();

        // initialize global vars and global registry
        $this->_initPVars($settings);
        $this->_initBWGlobals($settings);


        PSurveillance::setPoint('base_loaded');

        // print_r($class_loader);
    }

    protected function mergeNewConfig(array $settings, $dsn, $db_user, $db_password)
    {
        $uri = (null === $this->urlGenerator) ?
            'http://localhost/' :
            $this->urlGenerator->generate('homepage', [], UrlGenerator::ABSOLUTE_URL);

        return array_replace_recursive($settings, [
            'db' => [
                'dsn' => $dsn,
                'user' => $db_user,
                'password' => $db_password,
            ],
            'env' => [
                'baseuri' => $uri,
                'baseuri_http' => str_replace('https://', 'http://', $uri),
                'baseuri_https' => str_replace('http://', 'https://', $uri),
            ],
        ]);
    }

    protected function loadSettings()
    {
        // loads from an ini file, instead of a php file
        if (!is_file(SCRIPT_BASE.'rox_local.ini')) {
            return false;
        } else {
            $loader = new RoxLoader();
            // load everything, and continue as normal
            $settings = $loader->load(array(
                SCRIPT_BASE.'rox_default.ini',
                SCRIPT_BASE.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'cronjob').'.ini',
                SCRIPT_BASE.'rox_local.ini',
                SCRIPT_BASE.'rox_secret.ini'
            ));
            global $rox_baseuri;
            if (isset($rox_baseuri)) {
                $settings['env']['baseuri'] = $rox_baseuri;
            }
            return $settings;
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
        global $rox_baseuri;

        // loads from an ini file, instead of a php file
        $loader = new RoxLoader();

        // load everything, and continue as normal
        $settings = $loader->load(array(
            SCRIPT_BASE.'rox_default.ini',
            SCRIPT_BASE.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'cronjob').'.ini',
            SCRIPT_BASE.'rox_secret.ini'
        ));

        if (isset($rox_baseuri)) {
            $settings['env']['baseuri'] = $rox_baseuri;
        }

        return $settings;
    }

    protected function loadPTClasses($Classes)
    {
        // from here on, this is a copy of PT code, as in lib/libs.xml

        //***************************************************************
        // Miscellaneous
        //***************************************************************
        $Classes->addClass('PException',    SCRIPT_BASE.'lib/misc/exception.lib.php');
        $Classes->addClass('PPHP',          SCRIPT_BASE.'lib/misc/phpi.lib.php');
        $Classes->addClass('PVars',         SCRIPT_BASE.'lib/misc/vars.lib.php');
        $Classes->addClass('PVarObj',       SCRIPT_BASE.'lib/misc/var_obj.lib.php');
        $Classes->addClass('PFunctions',    SCRIPT_BASE.'lib/misc/functions.lib.php');
        $Classes->addClass('PModules',      SCRIPT_BASE.'lib/misc/modules.lib.php');
        $Classes->addClass('PDate',         SCRIPT_BASE.'lib/misc/date.lib.php');
        $Classes->addClass('PSurveillance', SCRIPT_BASE.'lib/misc/surveillance.lib.php');
        $Classes->addClass('PDataDir',      SCRIPT_BASE.'lib/misc/datadir.lib.php');
        //***************************************************************
        // DB
        //***************************************************************
        $Classes->addClass('PDB',                 SCRIPT_BASE.'lib/db/db.lib.php');
        $Classes->addClass('PDB_frame',           SCRIPT_BASE.'lib/db/db_interface.php');
        $Classes->addClass('PDBStatement',        SCRIPT_BASE.'lib/db/db_statement.lib.php');
        $Classes->addClass('PDBStatement_mysql',  SCRIPT_BASE.'lib/db/db_statement_mysql.lib.php');
        $Classes->addClass('PDBStatement_mysqli', SCRIPT_BASE.'lib/db/db_statement_mysqli.lib.php');
        $Classes->addClass('PDB_mysql',           SCRIPT_BASE.'lib/db/db_mysql.lib.php');
        $Classes->addClass('PDB_mysqli',          SCRIPT_BASE.'lib/db/db_mysqli.lib.php');
        //***************************************************************
        // Handler
        //***************************************************************
        $Classes->addClass('PPostHandler', SCRIPT_BASE.'lib/handler/posthandler.lib.php');
        $Classes->addClass('PRequest',     SCRIPT_BASE.'lib/handler/requesthandler.lib.php');
        //***************************************************************
        // Application control
        //***************************************************************
        $Classes->addClass('PApplication',   SCRIPT_BASE.'lib/application/app_interface.php');
        $Classes->addClass('PApps',          SCRIPT_BASE.'lib/application/apps.lib.php');
        $Classes->addClass('PAppModel',      SCRIPT_BASE.'lib/application/app_model.lib.php');
        $Classes->addClass('PAppView',       SCRIPT_BASE.'lib/application/app_view.lib.php');
        $Classes->addClass('PAppController', SCRIPT_BASE.'lib/application/app_controller.lib.php');
        //***************************************************************
        // XML
        //***************************************************************
        $Classes->addClass('PData',     SCRIPT_BASE.'lib/xml/xml_data.lib.php');
        $Classes->addClass('PSafeHTML', SCRIPT_BASE.'lib/xml/safehtml.lib.php');
        //***************************************************************
        // PEAR
        //***************************************************************
        $Classes->addClass('Mail', 'Mail.php');

        // end of copied PT code
    }


    /**
     * globals are said to be evil, but we need them, at least for legacy reasons.
     *
     * @param array $settings
     */
    private function _initPVars($settings)
    {
        $keymap = array();
        foreach ($settings as $key => $value) {
            $keymap[$key] = $key;
        }
        // some of the keys need another name
        foreach (array(
            'db' => 'db',
            'config_rdbms' => 'db',
            'config_smtp' => 'smtp',
            'config_mailAddresses' => 'mailAddresses',
            'config_request' => 'request',
            'config_google' => 'google',
            'env' => 'env'
        ) as $key_in_pvars => $key_in_inifile) {
            $keymap[$key_in_pvars] = $key_in_inifile;
        }
        foreach ($keymap as $key => $value) {
            if(isset($settings[$value])) {
                PVars::register($key, $settings[$value]);
            }
        }

        // if (empty ($_COOKIE[session_name ()]) ) {
        if (empty ($_COOKIE[$this->session->getName()]) ) {

            PVars::register('cookiesAccepted', false);
        } else {
            PVars::register('cookiesAccepted', true);
        }
        PVars::register('queries', 0);
    }

    /**
     * we need even more globals for bewelcome.
     *
     */
    private function _initBWGlobals($settings)
    {
        //********************************************************
        // LEGACY CODE FROM TRADITIONAL BW CONFIGURATION
        //********************************************************

        // TODO: remove this when the old bw part is no longer needed.

        global $_SYSHCVOL;
        $syshcvol = $settings['syshcvol'];
        $_SYSHCVOL = array();

        $_SYSHCVOL['MYSQLServer'] = "localhost";

        // We want autoupdates
        $_SYSHCVOL['NODBAUTOUPDATE'] = 0;

        // Leave these empty for test environment
        $_SYSHCVOL['ARCH_DB'] = ""; // name of the archive DB
        $_SYSHCVOL['CRYPT_DB'] = isset($syshcvol['CRYPT_DB']) ? $syshcvol['CRYPT_DB'] : ""; // name of the crypted DB

        // This parameter if set to True will force each call to HasRight to look in
        // the database, this is usefull when a right is update to force it to be used
        // immediately, of course in the long run it slow the server
        $_SYSHCVOL['ReloadRight'] = 'False'; // Deprecated use ($this->session->get('Param')->ReloadRightsAndFlags instead

        // This parameter if the name of the database with (a dot) where are stored crypted data, there is no cryptation it it is left blank
        $_SYSHCVOL['Crypted'] = $_SYSHCVOL['CRYPT_DB'].'.';

        $_SYSHCVOL['IMAGEDIR'] = "/var/www/upload/images/";

        // this is the e-mail domain; we might use "bewelcome.org" on our productive system, but while development it is probably "localhost"
        $_SYSHCVOL['EmailDomainName'] = isset($syshcvol['EmailDomainName']) ? $syshcvol['EmailDomainName'] : "example.org";
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
        $_SYSHCVOL['CommentsRelation'] = array ('hewasmyguest', 'hehostedme', 'OnlyOnce', 'HeIsMyFamily', 'HeHisMyOldCloseFriend','NeverMetInRealLife');

        $_SYSHCVOL['EvaluateEventMessageReceived'] = "Yes"; // If set to "Yes" events messages received is evaludated at each page refresh
        $_SYSHCVOL['UploadPictMaxSize'] = 500000; // This define the size of the max loaded pictures
        $_SYSHCVOL['AgeMinForApplying'] = 18; // Minimum age a wannabe member must have to become a member
        $_SYSHCVOL['WhoIsOnlineActive'] = 'Yes'; // Wether who is online is active can be Yes or No
        $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] = 10; // The delay of non activity to consider a member off line
        $_SYSHCVOL['WhoIsOnlineLimit'] = 11; // This limit the number of whoisonline, causing the display of ww('MaxOnlineNumberExceeded') at login for new loggers
        $_SYSHCVOL['EncKey'] = "YEU76EY6"; // encryption key


        // some of the syshcvol settings should rather be extracted from other settings!
        $_SYSHCVOL['MYSQLUsername'] = PVars::getObj('config_rdbms')->user;
        $_SYSHCVOL['MYSQLPassword'] = PVars::getObj('config_rdbms')->password;
        $_SYSHCVOL['MYSQLDB'] = substr(strstr(PVars::getObj('config_rdbms')->dsn,"dbname="),strlen("dbname=")); // name of the main DB

        $_SYSHCVOL['SiteName'] = substr(substr(PVars::getObj('env')->baseuri,strlen("http://")),0,strpos(substr(PVars::getObj('env')->baseuri,strlen("http://")),'/')); // This is the name of the web site
        $_SYSHCVOL['MainDir'] = substr(substr(PVars::getObj('env')->baseuri,strlen("http://")),strpos(substr(PVars::getObj('env')->baseuri,strlen        ("http://")),'/')) . "bw/"; // This is the name of the web site

        $_SYSHCVOL['WWWIMAGEDIR'] = PVars::getObj('env')->baseuri."bw/memberphotos";


        // write the entire [syshcvol] ini section to $_SYSHCVOL..
        // this can overwrite settings from above, if they are manually set.
        // (this is legacy support for alpha.bw and www.bw)
        if (!isset($settings['syshcvol'])) {
            // ehm, whatever. no special syshcvol settings.
        } else foreach ($settings['syshcvol'] as $key => $value) {
            $_SYSHCVOL[$key] = $value;
        }
    }

    /**
     * some fields in the $_SESSION need to be filled with default values, if they are empty.
     *
     */
    protected function fillSessionWithValues()
    {
//         // TODO: This is maybe not the best place to do this,
//         // but so far I don't know a better one.
//         if (!$this->session->has( 'lang' ) || !$this->session->has( 'IdLanguage' ) {
//             // normally either none or both of them are set.
//             $this->session->set( 'lang', 'en' )
//             $this->session->set( 'IdLanguage', 0 )
//         }
//         PVars::register('lang', $this->session->get('lang'));
    }


    protected function loadPModules($class_loader)
    {
        PSurveillance::setPoint('loading_modules');
        $Mod = PModules::get();
        $Mod->setModuleDir(SCRIPT_BASE.'modules');
        $Mod->loadModules();
        PSurveillance::setPoint('modules_loaded');
    }


    protected function loadPApps($class_loader)
    {
        $Apps = PApps::get();
        $Apps->build();
        // process includes
        $includes = $Apps->getIncludes();
        if ($includes) {
            foreach ($includes as $inc) {
                require_once $inc;
            }
        }
        PSurveillance::setPoint('apps_loaded');
    }


    protected function loadRoxClasses($class_loader)
    {
        // extensions mechanism

        $autoload_folders = array();
        if (!$this->session->has($this->session->get('extension_folders'))) {
            // nothing
        } else if (!is_string($ext_dirs_encoded = $this->session->get('extension_folders'))) {
            // nothing
        } else {
            $ext_folders = preg_split("/[,\n\r\t ]+/", $ext_dirs_encoded);
            foreach ($ext_folders as $folder) {
                $autoload_folders[] = SCRIPT_BASE.'extensions/'.$folder;
            }
        }

        // allow to use ini files for the autoload stuff.
        $autoload_folders[] = SCRIPT_BASE.'build';
        $autoload_folders[] = SCRIPT_BASE.'modules';
        $autoload_folders[] = SCRIPT_BASE.'tools';
        $autoload_folders[] = SCRIPT_BASE.'pthacks';

        foreach ($autoload_folders as $maindir) {
            $class_loader->addClassesForAutoloadInisInFolder($maindir);
        }
    }
}
?>
