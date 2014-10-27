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
 * This page list all future Activities
 *
 * @package Apps
 * @subpackage Activities
 */
class SuggestionsSearchResultsPage extends SuggestionsBasePage
{
    protected $NoItems = 'SuggestionsSearchNoResults';

    protected function teaserContent()
    {
        $this->hideSearch = true;
        parent::teaserContent();
    }

    protected function getSubmenuItems()
    {
        $items = parent::getSubmenuItems();
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();

        // Add search results to the end of the list
        $items[] = array('suggestionssearch', 'suggestions/search', $words->getSilent('SuggestionsSearchResult'));
        return $items;
    }

    protected function getSubmenuActiveItem()
    {
        return 'suggestionssearch';
    }

    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/suggestions.css?1';
       return $stylesheets;
    }
}