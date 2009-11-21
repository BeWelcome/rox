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
class VerifiedApprovedVerifiers extends RoxPageView {
    /**
     * content of the middle column - this is the most important part
     */

    private $_error;
    
    public function __construct($MyList) {
		 $this->list=$MyList ;
    }    



    
	  
    protected function column_col3()    {
	  	 
        // get the translation module
        $words = $this->getWords();
		 
		 		$list=$this->list ;
        require SCRIPT_BASE . 'build/verifymembers/templates/showexplanationapprovedverifiers.php';    
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
        if (MOD_right::get()->hasRight("Verifier","ApprovedVerifier")) {
            echo $words->getFormatted("verifymembers_approvedverifier") ;
        }
        else {
            echo $words->getFormatted("verifymembers_teaser") ;
        }
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return 'Approved verifiers page!';
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {
        $words = $this->getWords();
		echo "<ul>" ;
        echo '<li><a href="http://www.bevolunteer.org/wiki/How_verification_makes_it_safer">Wiki Doc</a></li>';
        echo '<li><a href="verifymembers/verifiersof/'.$_SESSION["Username"],'">',$words->getFormatted("MyVerifier"),'</a></li>';
        echo '<li><a href="verifymembers/verifiersby/'.$_SESSION["Username"],'">',$words->getFormatted("MyVerified"),'</a></li>';
        echo '<li><a href="verifymembers/approvedverifiers">',$words->getFormatted("ApprovedVerififiersLink"),'</a></li>';
		echo "</ul>" ;
    }
	 
} // end of VerifyMembersPage


?>