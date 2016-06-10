<?php


/**
 * verifymembers page
 *
 * @package verifymembers
 * @author jeanyves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */



class VerifyMembersProceedPage extends VerifyMembersPage
{

    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }

    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
         $words = new MOD_words($this->getSession());
         if (MOD_right::get()->hasRight("Verifier","ApprovedVerifier")) {
            echo $words->getFormatted("verifymembers_approvedverifier") ;
         }
         else {
            echo $words->getFormatted("verifymembers_teaser") ;
         }
    }


} // end of VerifyMembersProceedPage

?>
