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
     * @author Felix <fvanhove@gmx.de>
     * @author Fake51
     */

    /**
     * admin controller
     * deals with actions that are available exclusively for administrators
     *
     * @package apps
     * @subpackage Admin
     */
class AdminGeneralController extends AdminBaseController
{
    const MASSMAILEDIT    = 1;
    const MASSMAILCREATE  = 1;
    const MASSMAILENQUEUE = 1;
    const MASSMAILTRIGGER = 5;

    /**
     * overview page, displays tools for admins
     *
     * @access public
     * @return object
     */
    public function index()
    {
        list($member, $rights) = $this->checkRights();
        $page = new AdminOverviewPage;
        $page->member = $member;
        $page->rights = $rights;
        return $page;
    }
//{{{ START: tempVolStart stuff
    /**
     * tempVolStart method - will be removed after new admin pages work
     *
     * @access public
     * @return object
     *
     */
    public function tempVolStart()
    {
        list($member, $rights) = $this->checkRights();
        $page = new TempVolStartPage;
        return $page;
    }
//}}} END: tempVolStart stuff
//{{{ START: Debug right methods
    /**
     * displays the php error logs
     *
     * @access public
     * @return object
     */
    public function debugLogs()
    {
        list($member, $rights) = $this->checkRights('Debug');
        $page = new AdminLogsPage($this->route_vars['log_type']);
        $page->member = $member;
        $page->rights = $rights;
        $page->lines = ((!empty($this->args_vars->get['lines']) && intval($this->args_vars->get['lines'])) ? $this->args_vars->get['lines'] : 100);
        return $page;
    }

//}}} END: Debug right methods

//{{{ START: admin comments stuff
    /**
     * comments overview method
     *
     * @access public
     * @return object
     */
    public function commentsOverview()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $page = new AdminCommentsPage;
        $page->member = $member;

        $page->bad_comments = $this->_model->getBadComments();
        $params->strategy = new HalfPagePager('left');
        $params->items = count($page->bad_comments);
        $params->items_per_page = 25;
        $page->pager = new PagerWidget($params);
        return $page;
    }
//}}} END: admin comment stuff
//{{{ START: admin spam stuff
    /**
     * spam overview method
     *
     * @access public
     * @return object
     */
    public function spamOverview()
    {
        list($member, $rights) = $this->checkRights('Checker');
        $page = new AdminSpamPage;
        $page->member = $member;
        $params->strategy = new HalfPagePager('left');
        $params->items = count($page->bad_spam);
        $params->items_per_page = 25;
        $page->pager = new PagerWidget($params);
        return $page;
    }
//}}} END: admin spam stuff
//{{{ START: admin words stuff
    /**
     * words overview method
     *
     * @access public
     * @return object
     */
    public function wordsOverview()
    {
        list($member, $rights) = $this->checkRights('Words');
        $page = new AdminWordsPage;
        $page->member = $member;
        return $page;
    }
//}}} END: admin words stuff
    /**
     * generic board update function
     * post callback
     *
     * @param stdClass       $args   - all sorts of variables
     * @param ReadOnlyObject $memory - memory related stuff
     * @param stuff stuff
     * @param stuff stuff
     *
     * @access public
     * @return string return url
     */
    public function updateVolunteerBoard(stdClass $args, $memory, $stuff3, $stuff4)
    {
        if (empty($args->post) || empty($args->post['boardname']) || empty($args->post['tool_url']) || empty($args->post['TextContent']))
        {
            return false;
        }
        if (!$this->_model->updateVolunteerBoard($args->post['boardname'], $args->post['TextContent']))
        {
            return false;
        }
        return false;
    }

    public function activityLogs()
    {
/* This does not work yet. If you call admin/activitylogs ..
                case 'activitylogs':

                $level = $R->hasRight('Logs');
	            if (!$level || $level < 1) {
	                PPHP::PExit(); // TODO: redirect or display message?
	            }

	            ob_start();
	            $this->_view->leftSidebar();
	            $this->_view->activitylogs($level);
	            $str = ob_get_contents();
	            ob_end_clean();
	            $Page = PVars::getObj('page');
	            $Page->content .= $str;
	            break;
*/

    }

    public function wordsDownload()
    {
                $level = $R->hasRight('Words');
                if (!$level || $level < 1) {
                    PPHP::PExit(); // TODO: redirect or display message?
                }

                ob_start();
                $this->_view->wordsdownload($this->_model->wordsdownload());
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;

                ob_start();
                $this->_view->wordsdownload_teaser();
                $str = ob_get_contents();
                $Page->teaserBar .= $str;
                ob_end_clean();

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
        $page = new AdminTreasurerPage($this->_model);
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
        $errors = $this->_model->treasurerEditCreateDonationVarsOk($vars);
        if (!empty($errors)) {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = $errors;
            return false;
        }
        $countryid = $this->_model->getGeonameIdForCountryCode($vars['donate-country']);
        if (!$countryid) {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = array('AdminTreasurerDbCountry');
            return false;
        }
        $id = $vars['id'];
        if ($id == 0) {
            $success = $this->_model->createDonation($vars['IdMember'], $vars['DonatedOn'],
                $vars['donate-amount'], $vars['donate-comment'], $countryid);
        } else {
            $success = $this->_model->updateDonation($id, $vars['IdMember'], $vars['DonatedOn'],
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
        $page = new AdminTreasurerEditCreateDonationPage($this->_model, $id);
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
        $errors = $this->_model->treasurerStartDonationCampaignVarsOk($vars);
        if (!empty($errors)) {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = $errors;
            return false;
        }
        $success = $this->_model->startDonationCampaign($vars);
        if (!$success) {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = array('AdminTreasurerDbUpdateFailed');
            return false;
        }
        $_SESSION['AdminTreasurerStatus'] = array('StartSuccess');
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
        $page = new AdminTreasurerStartDonationCampaignPage($this->_model);
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
        $success = $this->_model->stopDonationCampaign();
        if ($success) {
            $_SESSION['AdminTreasurerStatus'] = array('StopSuccess');
        } else {
            $_SESSION['AdminTreasurerStatus'] = array('StopFailed');
        }
        $this->redirectAbsolute($this->router->url('admin_treasurer_overview'));
    }

    /**
     * overview page for mass mailings
     *
     */
    public function massmail() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILEDIT);
        $page = new AdminMassMailPage($this->_model);
        return $page;
    }

    /**
     * Massmail edit/create callback function
     *
     */
    public function massmailEditCreateCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        if (empty($args->post))
        {
            return false;
        }
        $errors = $this->_model->massmailEditCreateVarsOk($args->post);
        if (!empty($errors)) {
            $mem_redirect->vars = $args->post;
            $mem_redirect->errors = $errors;
            return false;
        }
        if ($args->post['Id'] == 0) {
            $this->_model->createMassmail($args->post['Name'], $args->post['Type'],
                $args->post['Subject'], $args->post['Body'], $args->post['Description']);
            $_SESSION['AdminMassMailStatus'] = array( 'Create', $args->post['Name']);
        } else {
            $this->_model->updateMassmail($args->post['Id'], $args->post['Name'],
                $args->post['Type'], $args->post['Subject'], $args->post['Body']);
            $_SESSION['AdminMassMailStatus'] = array( 'Edit', $args->post['Name']);
        }
        return $this->router->url('admin_massmail', array(), false);
    }

    /**
     * create a mass mailing
     *
     */
    public function massmailCreate() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILEDIT);
        $page = new AdminMassMailEditCreatePage($this->_model);
        $page->member = $member;
        return $page;
    }

    /**
     * show details for a mass mailing
     *
     */
    public function massmailDetails() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILEDIT);
        $id = $this->route_vars['id'];
        $page = new AdminMassMailDetailsPage($this->_model, $id);
        $page->member = $member;
        return $page;
    }

    /**
     * show details about status for a mass mailing
     *
     */
    public function massmailDetailsMailing() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILEDIT);
        $id = $this->route_vars['id'];
        $type = $this->route_vars['type'];
        $pageno = 1;
        if (isset($this->route_vars['page'])) {
            $pageno = $this->route_vars['page'];
        }
        $page = new AdminMassMailDetailsPage($this->_model, $id, $type, $pageno);
        $page->member = $member;
        return $page;
    }

    /**
     * edit a mass mailing
     *
     */
    public function massmailEdit() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILEDIT);
        $id = $this->route_vars['id'];
        $page = new AdminMassMailEditCreatePage($this->_model, $id);
        $page->member = $member;
        return $page;
    }

    /**
     * get admin units for country code used by enqueue to fill drop down
     *
     * @return json encoded list of admin units
     */
    public function getAdminUnits() {
        $countrycode = $this->route_vars['countrycode'];
        $adminunits = $this->_model->getAdminUnits($countrycode);
        header('Content-type: application/json');
        echo json_encode($adminunits) . "\n";
        exit;
    }

    /**
     * get places for country code and admin unit used by enqueue to fill drop down
     *
     * @return json encoded list of places
     */
    public function getPlaces() {
        $countrycode = $this->route_vars['countrycode'];
        $adminunit = $this->route_vars['adminunit'];
        $places = $this->_model->getPlaces($countrycode, $adminunit);
        header('Content-type: application/json');
        echo json_encode($places) . "\n";
        exit;
    }

    /**
     * Massmail enqueue callback function
     *
     */
    public function massmailEnqueueCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        if (empty($args->post))
        {
            return false;
        }
        $action = $this->_model->getEnqueueAction($args->post);
        $errors = $this->_model->massmailEnqueueVarsOk($args->post);
        if (!empty($errors)) {
            $mem_redirect->vars = $args->post;
            $mem_redirect->errors = $errors;
            $mem_redirect->action = $action;
            return false;
        }
        $count = $this->_model->enqueueMassMail($args->post);
        $massmail = $this->_model->getMassMail($args->post['id']);
        $_SESSION['AdminMassMailStatus'] = array( 'Enqueue', $massmail->Name, $count);
        return $this->router->url('admin_massmail', array(), false);
    }

    /**
     * enqueue a mass mailing
     *
     */
    public function massmailEnqueue() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILENQUEUE);
        $id = $this->route_vars['id'];
        $massmail = $this->_model->getMassmail($id);
        $page = new AdminMassMailEnqueuePage($this->_model, $massmail);
        $page->votersCount = $this->_model->getSuggestionsReminderCount();
        return $page;
    }

    /**
     * unqueue a mass mailing
     *
     */
    public function massmailUnqueue() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILENQUEUE);
        $id = $this->route_vars['id'];
        $count = $this->_model->unqueueMassMail($id);
        $massmail = $this->_model->getMassMail($id);
        $_SESSION['AdminMassMailStatus'] = array( 'Unqueue', $massmail->Name, $count);
        $this->redirectAbsolute($this->router->url('admin_massmail'));
    }

    /**
     * trigger a mass mailing
     *
     */
    public function massmailTrigger() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILTRIGGER);
        $id = $this->route_vars['id'];
        $count = $this->_model->triggerMassMail($id);
        $massmail = $this->_model->getMassMail($id);
        $_SESSION['AdminMassMailStatus'] = array( 'Trigger', $massmail->Name, $count);
        $this->redirectAbsolute($this->router->url('admin_massmail'));
    }

    /**
     * untrigger a mass mailing
     *
     */
    public function massmailUntrigger() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILTRIGGER);
        $id = $this->route_vars['id'];
        $count = $this->_model->untriggerMassMail($id);
        $massmail = $this->_model->getMassMail($id);
        $_SESSION['AdminMassMailStatus'] = array( 'Untrigger', $massmail->Name, $count);
        $this->redirectAbsolute($this->router->url('admin_massmail'));
    }

}
