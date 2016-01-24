<?php
/*

Copyflag (c) 2007-2009 BeVolunteer

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
 * adminFlags controller
 * deals with actions that are available exclusively for Flags managers
 *
 * @package apps
 * @subpackage Admin
 */
class AdminSubscriptionsController extends AdminBaseController
{
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new AdminSubscriptionsModel();
    }

    public function __destruct() {
        unset($this->model);
    }

    public function manageCallback(StdClass $args, ReadOnlyObject $action,
                                        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $errors = $this->model->checkManageVarsOk($vars);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        }
        if (isset($vars['AdminSubscriptionsEnable'])) {
            $this->model->manageSubscriptions($vars, true);
            $wordCode = 'AdminSubscriptionsEnabled';
        } else {
            $this->model->manageSubscriptions($vars, false);
            $wordCode = 'AdminSubscriptionsDisabled';
        }

        $this->setFlashNotice($this->getWords()->get($wordCode, $vars['username']));
        return true;
    }

    public function manage() {
        $this->checkRights('ManageSubscriptions');
        $page = new AdminSubscriptionsManagePage();
        $page->vars = array(
            'username' => ''
        );
        return $page;
    }
}