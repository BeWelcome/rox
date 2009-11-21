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

            // Try to find the max level of verification for this member
            $VerificationMaxLevel="verifymembers_NotYetVerified" ;
            for ($ii=0;$ii<count($list);$ii++) {
                if ($list[$ii]->VerificationType=="VerifiedByNormal") {
                    $VerificationMaxLevel="verifymembers_".$list[$ii]->VerificationType ;
                    break ;
                }
            }
            for ($ii=0;$ii<count($list);$ii++) {
                if ($list[$ii]->VerificationType=="VerifiedByVerified") {
                    $VerificationMaxLevel="verifymembers_".$list[$ii]->VerificationType ;
                    break ;
                }
            }
            for ($ii=0;$ii<count($list);$ii++) {
                if ($list[$ii]->VerificationType=="VerifiedByApproved") {
                    $VerificationMaxLevel="verifymembers_".$list[$ii]->VerificationType ;
                    break ;
                }
            }
            $Username=$this->VerifierUsername ;
            require 'templates/showverifiers.php';
         }
         if ($this->VerifiedUsername!="") {
            $Username=$this->VerifiedUsername ;
            require 'templates/showverified.php';
         }
    }

    /**
     * which item in the top menu should be activated when showing this page?
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {
        return '';
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
         $words = new MOD_words();
         if ($this->VerifierUsername!="") {
            echo $words->getFormatted("verifymembers_verifiedbynb",count($this->list),"<a href=\"people/".$this->VerifierUsername."\">".$this->VerifierUsername."</a>") ;
        }
         if ($this->VerifiedUsername!="") {
            echo count($this->list)," have been verified by ",$this->VerifiedUsername ;
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
    protected function leftSidebar() {
        $words = $this->getWords();
        echo '<ul>' ;
        echo '<li><a href="wiki/Verification">',$words->getFormatted("VerifyDoc"),'</a></li>';
        echo '<li><a href="verifymembers/verifiersof/'.$_SESSION["Username"],'">',$words->getFormatted("MyVerifier"),'</a></li>';
        echo '<li><a href="verifymembers/verifiersby/'.$_SESSION["Username"],'">',$words->getFormatted("MyVerified"),'</a></li>';
        echo '<li><a href="verifymembers/approvedverifiers">',$words->getFormatted("ApprovedVerififiersLink"),'</a></li>';
        echo '</ul>' ;
    }

} // end of VerifyMembersPage
