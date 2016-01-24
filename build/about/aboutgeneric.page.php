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
    private $_pagename;
    private $_lang;

    public function __construct($pagename, $lang) {
        $this->_pagename = $pagename;
        $this->_lang = $lang;
    }

    protected function getPageTitle() {
        $titles = array(
            'bod' => 'Board of Directors',
            //'getactive' => 'Get Active',
            'help' => 'Help',
            'terms' => 'Terms of Use',
            'commentguidelines' => 'Comment guidelines',
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

    protected function getStylesheets()
    {
        $styleSheets = parent::getStylesheets();
        if ($this->_pagename == "terms") {
            $styleSheets[] = "styles/css/minimal/screen/custom/terms.css";
        }
        return $styleSheets;
    }

    protected function column_col3() {
        if (!($model = $this->getModel()) || !($member = $model->getLoggedInMember()))
        {
            $isvolunteer = false;
        }
        else
        {
            $isvolunteer = $model->isVolunteer($member);
        }
        require 'templates/'.$this->_pagename.'.php';
    }
}

