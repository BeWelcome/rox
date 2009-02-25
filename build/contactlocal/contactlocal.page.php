<?php


/**
 * ContactLocals pages
 * This is the class for all pages displayed by the contact locals function
 * There is no real need to make it translatable since volunteers are generally using english
 *
 * @package ContactLocals
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


/**

**/
class ContactlocalsPage extends RoxPageView {
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

            case "MissRight":
				require 'templates/missright.php';    
                break ;
            case "listall":
				require 'templates/listall.php';    
                break ;
            case "preparenewmessage":
				$callbackId=564;
				require 'templates/preparenewmessage.php';    
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
        return '';
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        $words = $this->getWords();
        echo "Contact members for local volunteers" ;
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        $words = $this->getWords();
        return  "Contact members for local volunteers" ;
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {
        $words = $this->getWords();
        echo '<ul class="linklist">';
        echo '<li><a href="http://www.bevolunteer.org/wiki/How_ContactLocalsWorks">Wiki Doc</a></li>';
        echo '<li><a href="contactlocal/listall">List All</a></li>';
        echo '<li><a href="contactlocal/preparenewmessage">Start new message</a></li>';
        if (MOD_right::get()->HasRight("Poll","create")) {
             echo '<li><a href="polls/create">',$words->getFormatted("polls_createlink"),'</a></li>';
        }
        echo "</ul>" ;
    }
     
} // end of ContactLocalsPage


?>
