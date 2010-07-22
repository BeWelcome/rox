<?php

/**
 * VerifyMustLoginPage
 *
 * @package verifymembers
 * @author JeanYves, Micha
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class VerifyMyselfPage extends VerifyMembersPage
{
    private $_redirect_url = 'verify';

    // the address after login
    public function setRedirectURL($url)
    {
        $this->_redirect_url = $url;
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        $words = $this->getWords();
        echo $words->getFormatted("verifymembers_teaser") ;
    }

    protected function column_col3()
    {
        $words = $this->getWords();
        echo '<p class="note error big">'.$words->getFormatted("YouCanNotVerifiyYourself").'</p>';
    }


    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }

}
