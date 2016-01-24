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
class AdminMassmailController extends AdminBaseController
{
    const MASSMAILEDIT    = 1;
    const MASSMAILCREATE  = 1;
    const MASSMAILENQUEUE = 1;
    const MASSMAILTRIGGER = 5;

    public function __construct()
    {
        parent::__construct();
        $this->model = new AdminMassmailModel();
    }

    /**
     * overview page for mass mailings
     *
     */
    public function massmail() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILEDIT);
        $page = new AdminMassmailPage($this->model);
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
        $errors = $this->model->massmailEditCreateVarsOk($args->post);
        if (!empty($errors)) {
            $mem_redirect->vars = $args->post;
            $mem_redirect->errors = $errors;
            return false;
        }
        if ($args->post['Id'] == 0) {
            $this->model->createMassmail($args->post['Name'], $args->post['Type'],
                $args->post['Subject'], $args->post['Body'], $args->post['Description']);
            $_SESSION['AdminMassMailStatus'] = array( 'Create', $args->post['Name']);
        } else {
            $this->model->updateMassmail($args->post['Id'], $args->post['Name'],
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
        $page = new AdminMassmailEditCreatePage($this->model);
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
        $page = new AdminMassmailDetailsPage($this->model, $id);
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
        $page = new AdminMassmailDetailsPage($this->model, $id, $type, $pageno);
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
        $page = new AdminMassmailEditCreatePage($this->model, $id);
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
        $adminunits = $this->model->getAdminUnits($countrycode);
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
        $places = $this->model->getPlaces($countrycode, $adminunit);
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
        $action = $this->model->getEnqueueAction($args->post);
        $errors = $this->model->massmailEnqueueVarsOk($args->post);
        if (!empty($errors)) {
            $mem_redirect->vars = $args->post;
            $mem_redirect->errors = $errors;
            $mem_redirect->action = $action;
            return false;
        }
        $count = $this->model->enqueueMassMail($args->post);
        $massmail = $this->model->getMassMail($args->post['id']);
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
        $massmail = $this->model->getMassmail($id);
        $page = new AdminMassmailEnqueuePage($this->model, $massmail);
        $page->votersCount = $this->model->getSuggestionsReminderCount();
        $page->mailToConfirmCount = $this->model->getMailToConfirmCount();
        return $page;
    }

    /**
     * unqueue a mass mailing
     *
     */
    public function massmailUnqueue() {
        list($member, $rights) = $this->checkRights('MassMail', self::MASSMAILENQUEUE);
        $id = $this->route_vars['id'];
        $count = $this->model->unqueueMassMail($id);
        $massmail = $this->model->getMassMail($id);
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
        $count = $this->model->triggerMassMail($id);
        $massmail = $this->model->getMassMail($id);
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
        $count = $this->model->untriggerMassMail($id);
        $massmail = $this->model->getMassMail($id);
        $_SESSION['AdminMassMailStatus'] = array( 'Untrigger', $massmail->Name, $count);
        $this->redirectAbsolute($this->router->url('admin_massmail'));
    }
}
