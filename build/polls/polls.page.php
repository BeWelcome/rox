<?php


/**
 * verifymembers pages
 * This is the class for all pages displayed by verify members
 *
 * @package verifymembers
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


/**
 * This page prepare the verification
 **/
class PollsPage extends PageWithActiveSkin
{
    /**
     * content of the middle column - this is the most important part
     */

    /** @var HTMLPurifier  */
    private $_purifier;
    private $_error;
    private $_action;
    private $_data; // Will receive the data to be used by the template

    /**
     * according to the value of action a different template will be presented
     * data to display for the template will be first filled by the controller in
     * data will be used by the template call in column_col3
     *
     */
    public function __construct($error = "", $action = "", $Data = "")
    {
        parent::__construct();
        $this->_purifier = MOD_htmlpure::get()->getAdvancedHtmlPurifier();
        $this->_error = $error;
        $this->_action = $action;
        if (!empty($Data)) {
            $this->_data = $Data;
        }
        if ('showpoll' === $this->_action || 'create' === $this->_action) {
            $this->addLateLoadScriptFile('build/roxeditor.js');
            $this->addLateLoadScriptFile('build/cktranslations/'.$this->getSession()->get('lang', 'en').'.js');
            $this->addStylesheet('build/roxeditor.css');
        }
        if ('showpoll' === $this->_action) {
            $this->addLateLoadScriptFile('build/tempusdominus.js');
        }
    }

    protected function getColumnNames()
    {
        return array('col3');
    }

    protected function column_col3()
    {

        // get the translation module
        $words = $this->getWords();
        $errormessage = $this->_error;

        $callbackId = time();
        switch ($this->_action) {
            case "listall":
            case "listClosed":
            case "listOpen":
            case "listProject":
                require 'templates/listall.php';
                break;
            case  "create":
                require 'templates/create.php';
                break;
            case  "contribute":
                require 'templates/contribute.php';
                break;
            case  "probablyallreadyvote":
                require 'templates/probablyallreadyvote.php';
                break;
            case  "sorryyoucannotcontribute":
                require 'templates/sorryyoucannotcontribute.php';
                break;
            case  "votedone":
                require 'templates/votedone.php';
                break;
            case  "cancelvote":
                require 'templates/cancelvote.php';
                break;
            case  "votenotcancelable":
                require 'templates/votenotcancelable.php';
                break;
            case  "seeresults":
                require 'templates/seeresults.php';
                break;
            case  "resultsnotyetavailable":
                require 'templates/resultsnotyetavailable.php';
                break;
            case  "showpoll":
                $callbackid = time();
                require 'templates/create.php';
                break;
            default:
                require 'templates/showexplanation.php';
                require 'templates/listall.php';
                break;
        }
    }

    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem()
    {
        return $this->_action;
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline()
    {
        $words = $this->getWords();
        echo $words->getFormatted("polls_teaser");
    }

    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle()
    {
        $words = $this->getWords();
        return $words->getFormatted("polls_pagetitle");
    }

    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {
    }

    protected function getSubmenuActiveItem()
    {
        return $this->_action;
    }

    protected function getSubmenuItems()
    {
        $items = array();

        $words = $this->getWords();

        $items[] = array('wiki', 'https://www.bevolunteer.org/wiki/How_pollsworks', 'Wiki Doc');
        $items[] = array('listOpen', 'polls/list/open', $words->getSilent("polls_open"));
        $items[] = array('list_contributed', 'polls/list/contributed', $words->getSilent("polls_contributed"));
        if (MOD_right::get()->HasRight("Poll", "create")) {
            $items[] = array('create', 'polls/create', $words->getSilent("polls_createlink"));
            $items[] = array('listProject', 'polls/list/new', $words->getSilent("polls_new"));
            $items[] = array('listall', 'polls/list/all', $words->getSilent("polls_listlink"));
            $items[] = array('listClosed', 'polls/list/closed', $words->getSilent("polls_closed"));
            $items[] = array('update_status', 'polls/updatestatus', $words->getSilent("polls_update_status"));
        }

        return $items;
    }


} // end of PollsPage

