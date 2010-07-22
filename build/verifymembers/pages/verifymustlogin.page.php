<?php

/**
 * VerifyMustLoginPage
 *
 * @package verifymembers
 * @author JeanYves, Micha
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class VerifyMustLoginPage extends VerifyMembersPage
{
    private $_redirect_url = 'verify';

    // the address after login
    public function setRedirectURL($url)
    {
        $this->_redirect_url = $url;
    }

    protected function column_col3()
    {
        $url = $this->_redirect_url;

        $login_widget = $this->createWidget('LoginFormWidget');

        if ($memory = $this->memory) {
            $login_widget->memory = $memory;
        }

        $login_widget->render();
    }


    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }

}
