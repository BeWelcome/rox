<?php
/**
* wiki controller
*
* @package wiki
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class WikiController extends PAppController {
    private $_model;
    private $_view;

    public function __construct() {
        parent::__construct();
        $this->_model = new Wiki();
        $this->_view = new WikiView($this->_model);
    }

    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }

    public function editProcess($actionurl = false) {
        global $callbackId;
        if (PPostHandler::isHandling()) {
            $vars =& PPostHandler::getVars();
            if ($vars) {
            // Populate the _REQUEST array with the Post-Vars, so the wiki can use them :-/
            foreach ($vars as $key => $value) {
                $_REQUEST[$key] = $value;
            }
            }

            $url = $this->parseRequest();
            $this->no_output = true;
            ob_start();
            $this->getWiki($url);
            ob_end_clean();
            PPostHandler::clearVars();

            $url = str_replace('edit/', '', $url);
            if ($actionurl) {
                header('Location: '.PVars::getObj('env')->baseuri.$actionurl);
                PPHP::PExit();
            }
            header('Location: '.PVars::getObj('env')->baseuri.'wiki/'.$url);
            PPHP::PExit();

            //return PVars::getObj('env')->baseuri.'wiki';
        } else {
            $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
            PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }
    /**
    * index is called when http request = ./wiki
    */
    public function index() {
        $request = PRequest::get()->request;

        ob_start();
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->teaserBar .= $str;
        ob_end_clean();

        // ob_start();
        // $this->_view->stylesFullWidth();
        // $str = ob_get_contents();
        // ob_end_clean();
        // $P = PVars::getObj('page');
        // $P->addStyles .= $str;

        ob_start();

        $this->editProcess();

        $url = $this->parseRequest();
        $this->getWiki($url, true);
        echo $url;
        $Page = PVars::getObj('page');
        $Page->content .= ob_get_contents();
        $P->title = "Wiki - BeWelcome";
        ob_end_clean();
        ob_start();
        $this->_view->indicateRSS();
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->addStyles .= $str;
        ob_end_clean();
        
        ob_start();
        $this->_view->userbar();
        $str = ob_get_contents();
        ob_end_clean();
        $P = PVars::getObj('page');
        $P->newBar .= $str;
    }

    public function getWiki($page,$title = true) {
        global $ewiki_db, $ewiki_links, $ewiki_plugins, $ewiki_ring, $ewiki_t,
            $ewiki_errmsg, $ewiki_data, $ewiki_title, $ewiki_id,
            $ewiki_action, $ewiki_config, $ewiki_author;

        // Some settings
        define("EWIKI_NAME", "BeWelcome Wiki");
        define('EWIKI_SCRIPT', 'wiki/');
        define("EWIKI_SCRIPT_URL", PVars::getObj("env")->baseuri.'wiki/');	# absolute URL
        define("EWIKI_SCRIPT_BINARY", 0);
        define("EWIKI_PROTECTED_MODE", 1);
        define("EWIKI_RESCUE_HTML", 1);
        define("EWIKI_URLENCODE", 0);
        define("EWIKI_URLDECODE", 0);
        define("EWIKI_ALLOW_HTML", 1);

        define("EWIKI_DESC", "Document and share content about hospitality exchange and travel on bewelcome.org");  # site description
        define("EWIKI_COPY", "PrimarilyPublicDomain");      # site copyright
        define("EWIKI_CATEGORY", "Hospitality Exchange");              # site subject
        define("EWIKI_LOGO_URL", "http://www.bewelcome.org/images/logo_index_top.png");
        
        if (!$title) define("EWIKI_PRINT_TITLE", 0);        # <h2>WikiPageName</h2> on top

        // Authentification
        require_once("erfurtwiki/plugins/auth/auth_perm_ring.php");
        $ewiki_perm_rings['rss'] = 3;

        $model = new MembersModel;
        $member = $model->getLoggedInMember();
        $Right = new MOD_right();
        if ($member && $Right->hasRight('Admin','Wiki')) {
            $ewiki_author = $member->Username;
            define("EWIKI_AUTH_DEFAULT_RING", 0);    //  0 = admin
        } elseif ($member) {
            $ewiki_author = $member->Username;
            define("EWIKI_AUTH_DEFAULT_RING", 2);    //  2 = edit allowed
        } else {
            $ewiki_author = 'guest';
            define("EWIKI_AUTH_DEFAULT_RING", 3);    //  3 = read/view/browse-only
        }
        $this->defineMarkup(&$ewiki_config);
                
        // More plugins
        require_once("erfurtwiki/plugins/aview/toc.php"); // Table of contents
        $ewiki_plugins["view_final"][] = "ewiki_add_title";

        require_once("erfurtwiki/plugins/markup/mediawiki.php"); // load our own mediawiki plugin
        require_once("erfurtwiki/plugins/aview/fpage_copyright.php"); // Copyleft Info
        require_once("erfurtwiki/plugins/markup/bbcode.php"); // BBcode plugin
        require_once("erfurtwiki/plugins/markup/smilies.php"); // smilies ;)
        require_once("erfurtwiki/plugins/markup/rescuehtml.php"); // safe html tags ;)
        require_once("erfurtwiki/plugins/admin/control.php"); // load some plugins
        require_once("erfurtwiki/plugins/action/diff.php"); // stupid diff ;)
        require_once("erfurtwiki/plugins/action/info_qdiff.php"); // quick diff
        require_once("erfurtwiki/plugins/linking/titlefix.php"); // quick diff

        // require_once("erfurtwiki/plugins/markup/htmltable.php"); // quick diff

        // require_once("erfurtwiki/plugins/action/verdiff.php"); // version diff - not needed right now!?
        
        // RSS support
        require_once("erfurtwiki/plugins/lib/feed.php"); // load our own mediawiki plugin
        require_once("erfurtwiki/plugins/action/rss.php"); // load our own mediawiki plugin
        
        // Static pages
        require_once("erfurtwiki/plugins/page/wikinews.php"); // load some plugins
        require_once("erfurtwiki/plugins/page/recentchanges.php"); // load some plugins
        require_once("erfurtwiki/plugins/page/powersearch.php"); // load some plugins
        require_once("erfurtwiki/plugins/page/wantedpages.php"); // load some plugins
        require_once("erfurtwiki/plugins/page/orphanedpages.php"); // load some plugins
        require_once("erfurtwiki/plugins/page/recentchanges.php"); // load some plugins

//        require_once("erfurtwiki/plugins/aview/control2.php"); // quick diff

        require_once('erfurtwiki/ewiki.php');
        $ewiki_config["smilies"] = array(
           ":)" => "emoticon_happy.png",
           ";)" => "emoticon_grin.png",
        );
        
        $wiki = ewiki_page($page);
        echo $wiki;
    }

    private function parseRequest() {
        $request = PRequest::get()->request;

        if (count($request) == 1) {
            return '';
        }

        $request = implode('/', $request);

        $request = str_replace('wiki/', '', $request);

        return $request;
    }
    
    /**
    * defineMarkup tunes the ewiki's default markup my custom values
    */
    public function defineMarkup($ewiki_config) 
    {    
        /*
        * MediaWiki Markup
        */
    
        $ewiki_config["wm_style"]["&rarr;"] = array("", "");
        
        $ewiki_config["wm_style"]["'''"] = array("<strong>", "</strong>");
        $ewiki_config["wm_style"]["''"] = array("<em>", "</em>");
        
        // Headings
        $ewiki_config["wm_style"]["======"] = array("<h6>", "</h6>");
        $ewiki_config["wm_style"]["====="] = array("<h5>", "</h5>");
        $ewiki_config["wm_style"]["===="] = array("<h4>", "</h4>");
        $ewiki_config["wm_style"]["==="] = array("<h3>", "</h3>");
        $ewiki_config["wm_style"]["=="] = array("<h2>", "</h2>");        
        $ewiki_config["wm_style"]["="] = array('<h2 class="first">', "</h2>");
        return $ewiki_config;     
    }    

}
?>
