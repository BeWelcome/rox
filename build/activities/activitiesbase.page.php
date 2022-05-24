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
        $items[] = array('myactivities', 'activities/myactivities', $words->getSilent('ActivitiesMyActivities'));
        $items[] = array('upcomingactivities', 'activities/upcoming', $words->getSilent('ActivitiesUpcoming'));
        $items[] = array('upcomingonlineactivities', 'activities/online', $words->getSilent('ActivitiesUpcomingOnline'));
        $items[] = array('pastactivities', 'activities/past', $words->getSilent('ActivitiesPastActivities'));
        $geo = new Geo($this->member->IdCity);
        $items[] = array('activitiesnearme', 'activities/nearme', $words->getSilent('ActivitiesActivitiesNear', $geo->name));
        if ($this->update) {
            $items[] = array('createactivities', 'activities/' . $this->activity->id . '/edit', $words->getSilent('ActivitiesEdit'));
        } else {
            $items[] = array('createactivities', 'activities/create', $words->getSilent('ActivitiesCreate'));
        }
        $items[] = array('rules', 'activities/rules', $words->getSilent('activities.rules'));

        return $items;
    }

    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }

    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'build/leaflet.css';
       return $stylesheets;
    }

    protected function getLateLoadScriptfiles()
    {
        $scriptFiles = parent::getLateLoadScriptfiles();
        $scriptFiles[] = 'build/leaflet.js';
        $scriptFiles[] = 'script/map/activities/activities_map.js';
        return $scriptFiles;
    }
}

