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
 * admintreasurer controller
 * deals with actions that are available exclusively for treasurer tool
 *
 * @package apps
 * @subpackage Admin
 */
class AdminTreasurerController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->model = new AdminTreasurerModel();
    }

    public function __destruct() {
        unset($this->model);
    }

    /**
     * Treasurer overview method
     *
     * @access public
     * @return object
     */
    public function treasurerOverview()
    {
        list($member, $rights) = $this->checkRights('Treasurer');
        $page = new AdminTreasurerPage($this->model);
        return $page;
    }

    /**
     *
     */
    public function treasurerEditCreateDonationCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        if (empty($args->post))
        {
            return false;
        }
        $vars = $args->post;
        $errors = $this->model->treasurerEditCreateDonationVarsOk($vars);
        if (!empty($errors)) {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = $errors;
            return false;
        }
        $countryid = $this->model->getGeonameIdForCountryCode($vars['donate-country']);

        $id = $vars['id'];
        if ($id == 0) {
            $memberId = $vars['IdMember'] != 0 ? $vars['IdMember'] : null;
            $success = $this->model->createDonation($memberId, $vars['DonatedOn'],
                $vars['donate-amount'], $vars['donate-comment'], $countryid);
        } else {
            $success = $this->model->updateDonation($id, $vars['IdMember'], $vars['DonatedOn'],
                $vars['donate-amount'], $vars['donate-comment'], $countryid);
        }
        if (!$success) {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = array('AdminTreasurerDbUpdateFailed');
            return false;
        }
        return $this->router->url('admin_treasurer_overview', array(), false);
    }

    /**
     * Treasurer edit donation method
     *
     * @access public
     * @return object
     */
    public function treasurerEditCreateDonation()
    {
        list($member, $rights) = $this->checkRights('Treasurer');
        $id = 0;
        if (isset($this->route_vars['id'])) {
            $id = $this->route_vars['id'];
        }
        $page = new AdminTreasurerEditCreateDonationPage($this->model, $id);
        return $page;
    }

    /**
     *
     */
    public function treasurerStartDonationCampaignCallback(StdClass $args,
        ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        if (empty($args->post))
        {
            return false;
        }
        $vars = $args->post;
        $errors = $this->model->treasurerStartDonationCampaignVarsOk($vars);
        if (!empty($errors)) {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = $errors;
            return false;
        }
        $success = $this->model->startDonationCampaign($vars);
        if (!$success) {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = array('AdminTreasurerDbUpdateFailed');
            return false;
        }
        $this->session->set( 'AdminTreasurerStatus', 'StartSuccess' );
        return $this->router->url('admin_treasurer_overview', array(), false);
    }

    /**
     * This enables the treasurer to start the donation campaign
     *
     * @access public
     * @return object
     */
    public function treasurerStartDonationCampaign()
    {
        list($member, $rights) = $this->checkRights('Treasurer');
        $page = new AdminTreasurerStartDonationCampaignPage($this->model);
        return $page;
    }

    /**
     * This enables the treasurer to stop the donation campaign
     *
     * @access public
     * @return object
     */
    public function treasurerStopDonationCampaign()
    {
        list($member, $rights) = $this->checkRights('Treasurer');
        $success = $this->model->stopDonationCampaign();
        if ($success) {
            $this->session->set( 'AdminTreasurerStatus', 'StopSuccess' );
        } else {
            $this->session->set( 'AdminTreasurerStatus', 'StopFailed' );
        }
        $this->redirectAbsolute($this->router->url('admin_treasurer_overview'));
    }
}
