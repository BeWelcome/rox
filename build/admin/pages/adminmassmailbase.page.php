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
     * @author crumbking
     */

    /** 
     * words management overview page
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminMassMailBasePage extends AdminBasePage
{
    const NEWSLETTERSPECIFIC = 1;
    const NEWSLETTERGENERAL = 2;
    const NEWSLETTERALL = 3;
    
    public function __construct($model) {
        parent::__construct($model);
        $this->scope = array_keys($this->rights);
        $this->member = $this->model->getLoggedInMember();
        $this->rights = $this->member->getOldRights();
        $scope = $this->rights["MassMail"]["Scope"];
        $this->canChangeType = (stripos($scope, '"changetype"') !== false);
        
        // newsletter types
        $this->newsletterSpecific = (stripos($scope, '"specific"') !== false);
        $this->newsletterGeneral = (stripos($scope, '"general"') !== false);
        
        // if no type is set assume all
        if (!($this->newsletterSpecific || $this->newsletterGeneral)) {
            $this->newsletterSpecific = true;
            $this->newsletterGeneral = true;
        }        
        $this->enqueueGroups = array();
        $this->enqueueCountries = array();
        $this->canEnqueueMembers = (stripos($scope, '"members"') !== false);
        $this->canEnqueueLocation = (stripos($scope, '"location"') !== false) 
            || (stripos($scope, '"location:') !== false);
        if ($this->canEnqueueLocation) {
            $startpos = stripos($scope, '"location:') + 10;
            if ($startpos !== false) {
                $endpos = strpos($scope, '"', $startpos);
                if ($endpos === false) {
                    $endpos = strlen($scope);
                }
                $countries = substr($scope, $startpos, $endpos - $startpos);
                $this->enqueueCountries = explode(",", trim($countries));
            }
        }
        $this->canEnqueueGroup = (stripos($scope, "group") !== false) 
            || (stripos($scope, "group:") !== false);
        if ($this->canEnqueueGroup) {
            $startpos = stripos($scope, '"group:') + 7;
            if ($startpos !== false) {
                $endpos = strpos($scope, '"', $startpos);
                if ($endpos === false) {
                    $endpos = strlen($scope);
                }
                $groups = substr($scope, $startpos, $endpos - $startpos);
                $this->enqueueGroups = explode(",", trim($groups));
            }
        }
        $this->canEnqueueVote = (stripos($scope, "vote") !== false);

        // if no scope was given for enqueueing assume full scope
        $enqueueAny = $this->canEnqueueMembers | $this->canEnqueueLocation |
            $this->canEnqueueGroup | $this->canEnqueueVote;
        if ($enqueueAny == false) {
            $this->canEnqueueMembers = true;
            $this->canEnqueueLocation = true;
            $this->canEnqueueGroup = true;
            $this->canEnqueueVote = true;
        }

        if ($this->rights["MassMail"]["Level"] >= 1) {
            $this->canEditCreateEnqueue = true;
        }
        
        if ($this->rights["MassMail"]["Level"] == 5) {
            $this->canTrigger = true;
        }
       
        if ((stripos($scope, "All") !== false)) {
            $this->canEnqueueMembers = true;
            $this->canEnqueueLocation = true;
            $this->canEnqueueGroup = true;
            $this->canEnqueueVote = true;
            $this->canChangeType = true;
            $this->canTrigger = true;
            $this->specificNewsletter = true;
            $this->generalNewsletter = true;
        }
    }
}
