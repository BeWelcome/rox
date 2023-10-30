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

class AdminMassmailBasePage extends AdminBasePage
{
    protected $sidebar = array(
        'AdminMassMail' => 'admin/massmail',
        'AdminMassMailCreate' => 'admin/massmail/create',
    );

    protected $current = '';

    const NEWSLETTERSPECIFIC = 1;
    const NEWSLETTERGENERAL = 2;
    const LOGINREMINDER = 4;
    const VOTINGREMINDER = 8;
    const TERMSOFUSE = 16;
    const CORRECTBIRTHDATE = 32;
    const NEWSLETTERALL = 63;

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
        $this->loginReminder = (stripos($scope, '"remindtologin"') !== false);
        $this->mailToConfirmReminder = (stripos($scope, '"mailtoconfirmreminder"') !== false);
        $this->correctBirthDate = (stripos($scope, '"correctbirthdate"') !== false);
        $this->termsOfUse = (stripos($scope, '"termsofuse"') !== false);
        $this->suspendAfter5Reminders = (stripos($scope, '"suspendafter5reminders"') !== false);

        // if no type is set assume all
        if (!($this->newsletterSpecific || $this->newsletterGeneral || $this->loginReminder
            || $this->mailToConfirmReminder || $this->termsOfUse
            || $this->correctBirthDate || $this->suspendAfter5Reminders)) {
            $this->newsletterSpecific = true;
            $this->newsletterGeneral = true;
            $this->loginReminder = true;
            $this->mailToConfirmReminder = true;
            $this->correctBirthDate = true;
            $this->termsOfUse = true;
            $this->suspendAfter5Reminders = true;
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
        $this->canEnqueueReminder = (stripos($scope, "reminder") !== false);
        $this->canEnqueueMailToConfirmReminder = (stripos($scope, "mailtoconfirmreminder") !== false);
        $this->canEnqueueTermsOfUse = (stripos($scope, "termsofuse") !== false);

        // if no scope was given for enqueueing assume full scope
        $enqueueAny = $this->canEnqueueMembers || $this->canEnqueueLocation || $this->canEnqueueGroup
            || $this->canEnqueueReminder || $this->canEnqueueMailToConfirmReminder
            || $this->canEnqueueTermsOfUse || $this->canEnqueueSuspensionNotification
        ;
        if ($enqueueAny == false) {
            $this->canEnqueueMembers = true;
            $this->canEnqueueLocation = true;
            $this->canEnqueueGroup = true;
            $this->canEnqueueReminder = true;
            $this->canEnqueueCorrectBirthDate = true;
            $this->canEnqueueMailToConfirmReminder = true;
            $this->canEnqueueTermsOfUse = true;
            $this->canEnqueueSuspensionNotification = true;
        }

        if ($this->rights["MassMail"]["Level"] >= 1) {
            $this->canEditCreateEnqueue = true;
        }

        if ($this->rights["MassMail"]["Level"] >= 5) {
            $this->canTrigger = true;
        }

        if ((stripos($scope, "All") !== false)) {
            $this->canEnqueueMembers = true;
            $this->canEnqueueLocation = true;
            $this->canEnqueueGroup = true;
            $this->canEnqueueReminder = true;
            $this->canEnqueueMailToConfirmReminder = true;
            $this->canEnqueueTermsOfUse = true;
            $this->canEnqueueCorrectBirthDate = true;
            $this->canEnqueueSuspensionNotification = true;
            $this->canChangeType = true;
            $this->canTrigger = true;
            $this->specificNewsletter = true;
            $this->generalNewsletter = true;
            $this->loginReminder = true;
            $this->mailToConfirmReminder = true;
            $this->correctBirthDate = true;
            $this->termsOfUse = true;
        }
    }

    protected function getSubmenuItems()
    {
        $items = [];
        foreach($this->sidebar as $key => $item) {
            $items[] = [
                $key,
                $item,
                $this->getWords()->get($key),
            ];
        }
        return $items;
    }

    protected function getSubmenuActiveItem()
    {
        return $this->current;
    }

    protected function setCurrent($current)
    {
        $this->current = $current;
    }
}
