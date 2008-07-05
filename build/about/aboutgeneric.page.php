<?php


/**
 * AboutGenericPage
 * receives a paramter, that defines which template to show.
 *
 * @package about
 * @author design: Micha, structural refactoring: Andreas (lemon-head)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutGenericPage extends AboutBasePage
{
    public function __construct($pagename) {
        $this->_pagename = $pagename;
    }

    protected function getPageTitle() {
        $titles = array(
            'bod' => 'Board of Directors',
            //'getactive' => 'Get Active',
            'help' => 'Help',
            'terms' => 'Terms of Use',
            'impressum' => 'Impressum',
            'affiliations' => 'Affiliations',
            'privacy' => 'Privacy policy',
            'missions' => 'Missions',
            'newsletters' => 'Newsletters',
        );
        //Should actually be translatable!
        return 'About BeWelcome: '.$titles[$this->_pagename];
    }
    
    protected function getCurrentSubpage() {
        return $this->_pagename;
    }
    
    protected function column_col3() {
        if (!$model = $this->getModel()) {
            echo 'no model in AboutGenericView';
            $isvolunteer = false;
        } else if (!isset($_SESSION['IdMember'])) {
            $isvolunteer = false;
        } else {
            $isvolunteer = $this->getModel()->isVolunteer($_SESSION['IdMember']);
        }
        require 'templates/'.$this->_pagename.'.php';
    }
}


?>