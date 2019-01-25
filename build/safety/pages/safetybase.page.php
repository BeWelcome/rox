<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/


/**
 * @author sitatara
 */

/**
 * base class for all Safety pages
 *
 * @package Apps
 * @subpackage Safety
 */
class SafetyBasePage extends PageWithActiveSkin
{
    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->getBuffered('Safety') . ' - BeWelcome';
    }

    protected function teaserContent()
    {
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
		$words = $layoutkit->getWords();
        require('../build/safety/templates/teaser.php');
    }
    
    
    protected function getSubmenuItems()
    {
        $items = array();
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
		$items[] = array('main', 'safety', $words->getSilent('SafetyMain'));
		$items[] = array('basics', 'safety/basics', $words->getSilent('SafetyBasics'));
		$items[] = array('whattodo', 'safety/whattodo', $words->getSilent('SafetyWhatToDo'));
		$items[] = array('tips', 'safety/tips', $words->getSilent('SafetyTips'));
		/*$items[] = array('female', 'safety/female', $words->getSilent('SafetyFemale'));*/
		$items[] = array('faq', 'safety/faq', $words->getSilent('SafetyFAQ'));
		$items[] = array('team', 'safety/team', $words->getSilent('SafetyTeam'));
		$items[] = array('contact', 'feedback?IdCategory=2', $words->getSilent('SafetyContact'));
        return $items;
    }
        protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
//       $stylesheets[] = 'styles/css/minimal/screen/custom/safety.css';
       return $stylesheets;
    }

}

