<?php

/**
 * VerifyNoMemberSpecifiedPage
 *
 * @package verifymembers
 * @author JeanYves, Micha
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class VerifyNoMemberSpecifiedPage extends VerifyMembersPage
{

    protected function teaserHeadline() {
        $words = $this->getWords();
        echo $words->getFormatted("verifymembers_teaser") ;
    }

    protected function column_col3()
    {
        echo '<p class="note error big">You did not specify the member you want to verify. Please go to a profile and follow the link to verfiy the selected member!</p>';
    }


    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }

}
