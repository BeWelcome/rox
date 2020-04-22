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
 * base class for all Activities pages
 *
 * @package Apps
 * @subpackage Activities
 */
class ActivitiesEditCreatePage extends ActivitiesBasePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addStylesheet('build/jquery_ui.css');
        $this->addStylesheet('build/roxeditor.css');
        $this->addLateLoadScriptFile('build/tempusdominus.js');
        $this->addLateLoadScriptFile('build/jquery_ui.js');
        $this->addLateLoadScriptFile('build/search/searchpicker.js');
        $this->addLateLoadScriptFile('build/roxeditor.js');
        $this->addLateLoadScriptFile('build/cktranslations/'.$this->getSession()->get('lang', 'en').'.js');
    }

    protected function getSubmenuItems()
    {
        if ($this->activity->id == 0) {
            $this->update = false;
        } else {
            $this->update = true;
        }
        $items = parent::getSubmenuItems();
        return $items;
    }

    protected function getSubmenuActiveItem()
    {
        return 'createactivities';
    }
}
