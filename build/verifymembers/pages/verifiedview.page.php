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

    /**
     * verification level
     * @return string name of the menu
     */
    protected function pagename() {
        if ($this->VerifierUsername!="") {
            return "MyVerifier";
        } elseif ($this->VerifiedUsername!="") {
            return "MyVerified";
        } else return "ListofVerification";
    }

    protected function column_col3()    {

        // get the translation module
        $words = $this->getWords();

         $list=$this->list ;


         if ($this->VerifierUsername!="") {

            // Try to find the max level of verification for this member
            $VerificationMaxLevel="verifymembers_NotYetVerified";
            for ($ii=0;$ii<count($list);$ii++) {
                if ($list[$ii]->VerificationType=="VerifiedByApproved") {
                    $VerificationMaxLevel="verifymembers_".$list[$ii]->VerificationType;
                    break;
                } elseif ($list[$ii]->VerificationType=="VerifiedByVerified") {
                    $VerificationMaxLevel="verifymembers_".$list[$ii]->VerificationType;
                } elseif ($list[$ii]->VerificationType=="VerifiedByNormal" && $VerificationMaxLevel != "VerifiedByVerified") {
                    $VerificationMaxLevel="verifymembers_".$list[$ii]->VerificationType;
                } elseif ($list[$ii]->VerificationType=="" && $VerificationMaxLevel != "VerifiedByVerified" && $VerificationMaxLevel != "VerifiedByNormal" ) {
                    $VerificationMaxLevel="verifymembers_";
                }
            }
            $Username=$this->VerifierUsername ;
            require SCRIPT_BASE . 'build/verifymembers/templates/showverifiers.php';
         } elseif ($this->VerifiedUsername!="") {
            $Username=$this->VerifiedUsername ;
            require SCRIPT_BASE . 'build/verifymembers/templates/showverified.php';
         }
    }

    /**
     * which item in the top menu should be activated when showing this page?
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {
        return "";
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
         $words = $this->getWords();
         if ($this->VerifierUsername!="") {
             // UserVerifiedByNumber is '%d users have verified user %s'
            echo $words->getFormatted("verifymembers_verifiedbynb",count($this->list),$this->VerifierUsername) ;
        }
         if ($this->VerifiedUsername!="") {
             // NumberOfMembersVerifiedByUser is '%d have been verified by user %s'
            echo $words->getFormatted('NumberOfMembersVerifiedByUser', count($this->list), $this->VerifiedUsername) ;
        }
    }

    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return $this->getWords()->getFormatted($this->pagename());
    }

    /**
     * configure the sidebar
     */
    protected function leftSidebar() {
        $words = $this->getWords();
        echo '<h3>',$words->getFormatted("MyVerifyTools"),'</h3>';
        echo '<ul class="linklist">' ;
        echo '<li><a href="verifymembers/verifiersof/'.$_SESSION["Username"],'">',$words->getFormatted("MyVerifier"),'</a></li>';
        echo '<li><a href="verifymembers/verifiersby/'.$_SESSION["Username"],'">',$words->getFormatted("MyVerified"),'</a></li>';
        echo '</ul>' ;
        echo '<h3>',$words->getFormatted("MoreInfo"),'</h3>';
        echo '<ul class="linklist">' ;
        echo '<li><a href="verifymembers/approvedverifiers">',$words->getFormatted("ApprovedVerififiersLink"),'</a></li>';
        echo '<li><a href="wiki/Verification">',$words->getFormatted("VerifyDoc"),'</a></li>';
        echo '</ul>' ;
    }

} // end of VerifyMembersPage
