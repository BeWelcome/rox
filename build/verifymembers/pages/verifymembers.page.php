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
* This page prepares the verification
**/
class VerifyMembersPage extends RoxPageView
{

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
        return 'Verify members page!';
    }

    /**
     * configure the sidebar
     */
    protected function leftSidebar() {
        $words = $this->getWords();
        echo '<h3>',$words->getFormatted("MyVerifyTools"),'</h3>';
        echo '<ul class="linklist">' ;
        echo '<li><a href="verifymembers/verifiersof/'.$this->_session->get("Username"),'">',$words->getFormatted("MyVerifier"),'</a></li>';
        echo '<li><a href="verifymembers/verifiersby/'.$this->_session->get("Username"),'">',$words->getFormatted("MyVerified"),'</a></li>';
        echo '</ul>' ;
        echo '<h3>',$words->getFormatted("MoreInfo"),'</h3>';
        echo '<ul class="linklist">' ;
        echo '<li><a href="verifymembers/approvedverifiers">',$words->getFormatted("ApprovedVerififiersLink"),'</a></li>';
        echo '<li><a href="wiki/Verification">',$words->getFormatted("VerifyDoc"),'</a></li>';
        echo '</ul>' ;
    }

} // end of VerifyMembersPage


?>
