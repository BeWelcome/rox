<?php
/*
Copyright (c) 2007-2014 BeVolunteer

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
 * base class for all search members pages
 *
 * @package Apps
 * @subpackage SearchMembers
 */
class SearchMembersBasePage extends PageWithActiveSkin
{
    public function __construct() {
        parent::__construct();
        $this->purifier = (new MOD_htmlpure())->getAdvancedHtmlPurifier();
    }

    protected function teaserHeadline() {
        return $this->getWords()->get('FindMembers');
    }

    #[\Override]
    protected function getPageTitle() {
        return $this->getWords()->get('FindMembers') . ' - BeWelcome';
    }

    protected function leftSidebar() {
        return '';
    }

    #[\Override]
    protected function getColumnNames()
    {
        // we don't need the other columns
        return ['col3'];
    }

    #[\Override]
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/search.css?2';
        $stylesheets[] = 'styles/css/minimal/screen/custom/jquery-ui/smoothness/jquery.ui.all.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/jquery-ui/smoothness/jquery-ui-1.10.4.custom.min.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/jquery.multiselect.css';
        return $stylesheets;
    }

    #[\Override]
    public function getLateLoadScriptfiles() {
        $scriptFiles = parent::getLateLoadScriptfiles();
        $scriptFiles[] = 'search/searchajax.js?1';
        $scriptFiles[] = 'search/searchlocation.js?1';
        $scriptFiles[] = 'search/searchmultiselect.js?1';
        return $scriptFiles;
    }
}

