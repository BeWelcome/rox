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
 * This page lists all Suggestions for which voting hasn't started yet (which
 * therefore can still be edited)
 *
 * @package Apps
 * @subpackage Suggestions
 */
class SuggestionsEditCreatePage extends SuggestionsBasePage
{
    protected function getSubmenuItems()
    {
        $words = $this->getWords();

        $items = parent::getSubmenuItems();
        if ($this->suggestion->id) {
            $items[1] = array('edit', 'suggestions/' . $this->suggestion->id . '/edit', $words->getSilent('SuggestionsEdit'));
        } else {
            $items[1] = array('create', 'suggestions/create', $words->getSilent('SuggestionsCreate'));
        }
        return $items;
    }

    protected function getSubmenuActiveItem()
    {
        if ($this->suggestion->id) {
            return 'edit';
        } else {
            return 'create';
        }
    }

    public function getLateLoadScriptFiles()
    {
        $scripts = parent::getLateLoadScriptfiles();
        $pref = $this->member->getPreference("PreferenceDisableTinyMCE", $default = "No");
        if ($this->member->getPreference("PreferenceDisableTinyMCE", $default = "No") == 'No') {
            $scripts[] = 'tinymceconfig.js';
        }
        return $scripts;
    }
}


