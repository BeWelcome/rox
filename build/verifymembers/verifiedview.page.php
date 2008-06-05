<?php


/**
 * verifiedview pages
 * This is the class for pages displaying verified members
 *
 * @package verifiedview
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


/**
This page prepare the verification
**/
class VerifiedMembersViewPage extends RoxPageView {
    /**
     * content of the middle column - this is the most important part
     */

	 public $VerifierUsername ;
	 public $VerifiedUsername ;
	 public $list ;
    
    public function __construct($MyVerifierUsername,$MyVerifiedUsername,$MyList) {
	 	 $this->VerifierUsername=$MyVerifierUsername ;
	 	 $this->VerifiedUsername=$MyVerifiedUsername ; 
		 $this->list=$MyList ;
    }    

	  
    protected function column_col3()    {
	  	 
        // get the translation module
        $words = $this->getWords();
		
		 $list=$this->list ;
		 
		 
		 if ($this->VerifierUsername!="") {
		 	$Username=$this->VerifierUsername ;
        	require TEMPLATE_DIR.'apps/verifymembers/showverifiers.php';
		 }
		 if ($this->VerifiedUsername!="") {
		 	$Username=$this->VerifiedUsername ;
        	require TEMPLATE_DIR.'apps/verifymembers/showverified.php';
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
	 	 $words = new MOD_words();
		 if ($this->VerifierUsername!="") {
		 	echo $words->getFormatted("verifymembers_verifiedbynb",count($this->list),$this->VerifierUsername) ;
		}
		 if ($this->VerifiedUsername!="") {
		 	echo $words->getFormatted("verifymembers_hasverify",count($this->list),$this->VerifiedUsername) ;
		}
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return 'Verify members page!';
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {
        echo 'verify members Sidebar';
    }
	 
} // end of VerifyMembersPage


?>