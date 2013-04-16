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
class ActivitiesBasePage extends PageWithActiveSkin
{
    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->getBuffered('Activities') . ' - BeWelcome';
    }

    protected function teaserContent()
    {
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callbackTags = $formkit->setPostCallback('ActivitiesController', 'searchActivitiesCallback');
        $words = $layoutkit->getWords();
        require('templates/teaser.php');
    }
    
    
    protected function getSubmenuItems()
    {
        $items = array();
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        if ($this->member) {
            $items[] = array('myactivities', 'activities/myactivities', $words->getSilent('ActivitiesMyActivities'));
            $items[] = array('upcomingactivities', 'activities/upcomingactivities', $words->getSilent('ActivitiesUpcoming'));
            $items[] = array('pastactivities', 'activities/pastactivities', $words->getSilent('ActivitiesPastActivities'));
            $geo = new Geo($this->member->IdCity);
            $items[] = array('activitiesnearme', 'activities/nearme', $words->getSilent('ActivitiesActivitiesNear', $geo->name));
            if ($this->update) {
                $items[] = array('editactivities', 'activities/edit', $words->getSilent('ActivitiesEdit'));
            } else {
                $items[] = array('createactivities', 'activities/create', $words->getSilent('ActivitiesCreate'));
            }
        } else {
            $items[] = array('upcomingactivities', 'activities/upcomingactivities', $words->getSilent('ActivitiesUpcoming'));
            $items[] = array('pastactivities', 'activities/pastactivities', $words->getSilent('ActivitiesPastActivities'));
        }
        return $items;
    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/activities.css';
       $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
       $stylesheets[] = 'styles/css/minimal/screen/custom/fontawesome.css';
       $stylesheets[] = 'styles/css/minimal/screen/custom/fontawesome-ie7.css';
       return $stylesheets;
    }

}

