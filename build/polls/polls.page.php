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
This page prepare the verification
**/
class PollsPage extends RoxPageView {
    /**
     * content of the middle column - this is the most important part
     */

    private $_error;
    private $_action;
        private $_data ; // Will receive the data to be used by the template 
    
/**
* according to the value of action a different template will be presented
* data to display for the template will be first filled by the controller in
* data will be used by the template call in column_col3
* 
*/
    public function __construct($error="",$action="",$Data="") {
        $this->_error = $error;
                $this->_action=$action ;
                if (!empty($Data)) {
            $this->_data = $Data;
                }
    }    
      
    protected function column_col3()    {
         
        // get the translation module
        $words = $this->getWords();
        $errormessage=$this->_error ;
         
                switch ($this->_action) {

                    case "listall":
                require 'templates/listall.php';    
                        break ;
                    case "listClose":
                require 'templates/listClose.php';    
                        break ;
                    case "listOpen":
                require 'templates/listOpen.php';    
                        break ;
                    case "listProject":
                require 'templates/listProject.php';    
                        break ;
                    case "listall":
                require 'templates/listall.php';    
                        break ;
                    case  "create":
                require 'templates/create.php';    
                        break ;
                    case  "contribute":
                        $callbackid=55656 ;
                require 'templates/contribute.php';    
                        break ;
                    case  "probablyallreadyvote":
                require 'templates/probablyallreadyvote.php';    
                        break ;
                        
                    case  "sorryyoucannotcontribute":
                require 'templates/sorryyoucannotcontribute.php';    
                        break ;
                        
                        
                    case  "votedone":
                require 'templates/votedone.php';    
                        break ;
                    case  "cancelvote":
                require 'templates/cancelvote.php';    
                        break ;
                    case  "votenotcancelable":
                require 'templates/votenotcancelable.php';    
                        break ;
                    case  "seeresults":
                require 'templates/seeresults.php';    
                        break ;
                    case  "resultsnotyetavailable":
                require 'templates/resultsnotyetavailable.php';    
                        break ;
                    case  "showpoll":
                        $callbackid=5656456 ;
                require 'templates/create.php';    
                        break ;
                        
                    default:
                require 'templates/showexplanation.php';    
                require 'templates/listall.php';
                        break ;    
                }
    }
    
    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        $words = $this->getWords();
        echo $words->getFormatted("polls_teaser") ;
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        $words = $this->getWords();
        return  $words->getFormatted("polls_pagetitle") ;
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {
        $words = $this->getWords();
        echo '<ul class="linklist">';
        echo '<li><a href="http://www.bevolunteer.org/wiki/How_pollsworks">Wiki Doc</a></li>';
        echo '<li><a href="polls/listall">',$words->getFormatted("polls_listlink"),'</a></li>';
        if (MOD_right::get()->HasRight("Poll","create")) {
                    echo '<li><a href="polls/create">',$words->getFormatted("polls_createlink"),'</a></li>';
                }
        echo "</ul>" ;
    }
     
} // end of PollsPage


?>
