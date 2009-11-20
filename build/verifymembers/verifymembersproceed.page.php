<?php


/**
 * verifymembers page
 *
 * @package verifymembers
 * @author jeanyves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class VerifyMembersProceedPage extends RoxPageView {
    /**
     * content of the middle column - this is the most important part
     */

    private $membertoverify;

    public function __construct($m) {
        $this->membertoverify = $m;
    }    


    protected function column_col3()
    {

        // get the translation module
        $words = $this->getWords();
		$m = $this->membertoverify ;
        require 'templates/proceedtoverification.php';    
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
        echo 'Verify members teaser';
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
	 	 $words = new MOD_words();
	 	 if (HasRight("Verifier","ApprovedVerifier")) {
		 	echo $words->getFormatted("verifymembers_approvedverifier") ;
	     }
		 else {
		 	echo $words->getFormatted("verifymembers_teaser") ;
		 }
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {
        echo 'verify members Sidebar';
    }
	 
} // end of VerifyMembersProceedPage
