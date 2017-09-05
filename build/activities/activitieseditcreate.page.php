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
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
//       $stylesheets[] = 'styles/css/minimal/screen/custom/jquery-ui/smoothness/jquery-ui-1.10.4.custom.min.css';
//       $stylesheets[] = 'styles/css/minimal/screen/custom/jquery-ui/smoothness/datetimepicker.css';
//       $stylesheets[] = 'build/tempusdominus.css';
       return $stylesheets;
    }

    public function getLateLoadScriptFiles()
    {
        $scripts = parent::getLateLoadScriptfiles();
        $scripts[] = 'build/tempusdominus.js';
        $scripts[] = 'script/activities/edit_create.js';
        return $scripts;
    }
}
