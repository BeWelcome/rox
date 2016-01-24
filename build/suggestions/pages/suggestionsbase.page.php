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
    protected $hasSuggestionRight = false;
    protected $viewOnly = true;

    public function __construct($member) {
        $this->member = $member;
        if ($member) {
            $this->hasSuggestionRight = $this->checkSuggestionRight();
            $this->disableTinyMCE = $member->getPreference("PreferenceDisableTinyMCE", $default = "No");
            if ($member->Status != 'ChoiceInactive') {
                $this->viewOnly = false;
            }
        }
        $this->purifier = MOD_htmlpure::getSuggestionsHtmlPurifier();
    }

    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->getBuffered('Suggestions') . ' - BeWelcome';
    }

    protected function teaserHeadline()
    {
        $words = $this->getWords();
        return '<a href="/suggestions">' . $words->get('Suggestions') . '</a>';
    }

    protected function teaserContent()
    {
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callbackTags = $formkit->setPostCallback('SuggestionsController', 'searchSuggestionsCallback');
        $words = $layoutkit->getWords();
        require(SCRIPT_BASE . 'build/suggestions/templates/teaser.php');
    }

    private function checkSuggestionRight()
    {
        $rights = $this->member->getOldRights();
        if (empty($rights)) {
            return false;
        }
        if (in_array('Suggestions', array_keys($rights))) {
            return true;
        } else {
            return false;
        }
        return true;
    }

    protected function getSubmenuItems()
    {
        $words = $this->getWords();
        $items = array();
        // The first item might be overwritten in SuggestionsEditCreatePage
        $items[] = array('about', 'suggestions/about', $words->getSilent('SuggestionsAbout'));
		if ($this->member) {
			$items[] = array('create', 'suggestions/create', $words->getSilent('SuggestionsCreate'));
            $items[] = array('approve', 'suggestions/approve', $words->getSilent('SuggestionsAwaitApproval'));
        }
        $items[] = array('discuss', 'suggestions/discuss', $words->getSilent('SuggestionsDiscuss'));
        $items[] = array('addoptions', 'suggestions/addoptions', $words->getSilent('SuggestionsAddOptions'));
        $items[] = array('vote', 'suggestions/vote', $words->getSilent('SuggestionsVote'));
        $items[] = array('rank', 'suggestions/rank', $words->getSilent('SuggestionsRank'));
        $items[] = array('dev', 'suggestions/dev', $words->getSilent('SuggestionsDevelopment'));
        $items[] = array('results', 'suggestions/results',  $words->getSilent('SuggestionsResults'));
        $items[] = array('team', 'suggestions/team', $words->getSilent('SuggestionsTeams'));

        return $items;
    }

    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }

    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/forums.css?10';
       $stylesheets[] = 'styles/css/minimal/screen/custom/suggestions.css?5';
       $stylesheets[] = 'styles/css/minimal/screen/custom/font-awesome.min.css';
       $stylesheets[] = 'styles/css/minimal/screen/custom/font-awesome-ie7.min.css';
       return $stylesheets;
    }

    protected function getStylesheetPatches() {
       $stylesheets = parent::getStylesheetPatches();
       $stylesheets[] = 'styles/css/minimal/screen/custom/suggestions_iepatch.css';
       return $stylesheets;
    }

    protected function getStateSelect($state) {
        $select = '<select id="suggestion-state" name="suggestion-state">';
        $words = $this->getWords();
        $states = SuggestionsModel::getStatesAsArray();
        foreach($states as $key => $wordCode) {
            $select .= '<option value="' . $key . '"';
            if ($key == $state) {
                $select .= ' selected="selected"';
            }
            $select .= '>' . $words->getSilent($wordCode) . '</option>';
        }
        $select .= '</select>';
        $select .= $words->flushBuffer();
        return $select;
    }
}
