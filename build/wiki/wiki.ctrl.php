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

            // Populate the _REQUEST array with the Post-Vars, so the wiki can use them :-/
            foreach ($vars as $key => $value) {
                $_REQUEST[$key] = $value;
            }

            $url = $this->parseRequest();
            $this->no_output = true;
            $this->getWiki($url);

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
        $User = APP_User::login();

        ob_start();
        $this->_view->teaser();
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->teaserBar .= $str;
        ob_end_clean();

        ob_start();
        $this->_view->userbar();
        $str = ob_get_contents();
        ob_end_clean();
        $P = PVars::getObj('page');
        $P->newBar .= $str;

        ob_start();

        $this->editProcess();

        $url = $this->parseRequest();
        $this->getWiki($url, true);
        echo $url;
        $Page = PVars::getObj('page');
        $Page->content .= ob_get_contents();
        $P->title = "Wiki - BeWelcome";
        ob_end_clean();

    }

    public function getWiki($page,$title = true) {
        global $ewiki_db, $ewiki_links, $ewiki_plugins, $ewiki_ring, $ewiki_t,
            $ewiki_errmsg, $ewiki_data, $ewiki_title, $ewiki_id,
            $ewiki_action, $ewiki_config, $ewiki_author;

        define('EWIKI_SCRIPT', 'wiki/');
        define("EWIKI_SCRIPT_BINARY", 0);
        define("EWIKI_PROTECTED_MODE", 1);
        if (!$title) define("EWIKI_PRINT_TITLE", 0);        # <h2>WikiPageName</h2> on top
        require_once("erfurtwiki/plugins/auth/auth_perm_ring.php");

        $User = APP_User::login();

        if ($User) {
            $ewiki_author = $User->getHandle();
            define("EWIKI_AUTH_DEFAULT_RING", 2);    //  2 = edit allowed
        } else {
            define("EWIKI_AUTH_DEFAULT_RING", 3);    //  3 = read/view/browse-only
        }

        require_once('erfurtwiki/ewiki.php');

        define("EWIKI_NAME", "BeWelcome Rox Wiki");

        echo ewiki_page($page);
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


}
?>
