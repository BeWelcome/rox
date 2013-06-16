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
 * @author shevek
 */

/**
 * base class for all Suggestions pages
 *
 * @package Apps
 * @subpackage Suggestions
 */
class SuggestionsBasePage extends PageWithActiveSkin
{
    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->getBuffered('Suggestions') . ' - BeWelcome';
    }

    protected function teaserContent()
    {
        $words = $this->getWords();
        require('templates/teaser.php');
    }
    
    
    protected function getSubmenuItems()
    {
        $words = $this->getWords();
        $items = array();
        if ($this->hasSuggestionRights) {
        //    $items[] = array('create', 'suggestions/create', $words->getSilent('SuggestionsCreate'));
        //    $items[] = array('approve', 'suggestions/approve', $words->getSilent('SuggestionsAwaitApproval'));
        }
        // $items[] = array('discuss', 'suggestions/discuss', $words->getSilent('SuggestionsDiscuss'));
        if ($this->hasSuggestionRights) {
        //    $items[] = array('addoptions', 'suggestions/addoptions', $words->getSilent('SuggestionsAddOptions'));
        }
        $items[] = array('vote', 'suggestions/vote', $words->getSilent('SuggestionsVote'));
        // $items[] = array('rank', 'suggestions/rank', $words->getSilent('SuggestionsRank'));
        // $items[] = array('rejected', 'suggestions/rejected', $words->getSilent('SuggestionsRejected'));
        // $items[] = array('dev', 'suggestions/dev', $words->getSilent('SuggestionsDevelopment'));
        return $items;
    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/suggestions.css';
       $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
       $stylesheets[] = 'styles/css/minimal/screen/custom/fontawesome.css';
       $stylesheets[] = 'styles/css/minimal/screen/custom/fontawesome-ie7.css';
       return $stylesheets;
    }

}

