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
 */

/**
 * admincomments controller
 *
 * @package apps
 * @subpackage Admin
 */
class AdminCommentsController extends AdminBaseController
{
    // TODO: is it not enough we've got this in parent classes?
    // TODO: should this not be $_model - but why is it often not $_model
    // in other classes? or is it not?
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new AdminCommentsModel();
    }

    public function __destruct() {
        unset($this->model);
    }

    /**
     * comments overview method
     *
     * @access public
     * @return object
     */
    public function listComments()
    {
        list($member, $rights) = $this->checkRights('Comments');
    
        $action = $this->args_vars->get['action'];
        $page = new AdminCommentsPage($action);
        $page->member = $member;
        
        $page->comments = $this->model->get($action);
               
        $params = new StdClass();
        $params->strategy = new HalfPagePager('left');
        $params->items = count($page->comments);
        $params->items_per_page = 25;
        $page->pager = new PagerWidget($params);

        return $page;
    }
    
    /**
     * Button "Show" resp. button "Hide"
     * 
     * @see routes.php
     * @return page
     */
    public function toggleHide()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $this->model->toggleHide($this->args_vars->get['id']);
        return $this->_singleComment($this->args_vars->get['id'], "toggleHide", $member);
    }
    
    /**
     * Button "Allow Editing" resp. button ?! 
     * @see routes.php
     * @return page
     */
    public function toggleAllowEdit()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $this->model->toggleAllowEdit($this->args_vars->get['id']);
        return $this->_singleComment($this->args_vars->get['id'], "toggleAllowEdit", $member);
    }
    
    /**
     * Button "Mark as Checked"
     * 
     * @return type
     */
    public function markChecked()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $this->model->markChecked($this->args_vars->get['id']);
        return $this->_singleComment($this->args_vars->get['id'], "markChecked", $member);
    }
    
    /**
     * Button "Mark as Abuse"
     * 
     * @see routes.php
     * @return type
     */
    public function markAdminAbuserMustCheck()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $this->model->markAdminAbuserMustCheck($this->args_vars->get['id']);
        return $this->_singleComment($this->args_vars->get['id'], "markAdminAbuserMustCheck", $member);
    }


    /**
     * Button "Move to Negative"
     * 
     * @see routes.php
     * @return type
     */
    public function markAdminCommentMustCheck()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $this->model->markAdminCommentMustCheck($this->args_vars->get['id']);
        return $this->_singleComment($this->args_vars->get['id'], "markAdminCommentMustCheck", $member);
    }


    public function updateCallback(StdClass $args, ReadOnlyObject $action,
            ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        list($member, $rights) = $this->checkRights('Comments');
        
        // TODO: another option would be
        //$vars = PPostHandler::getVars($callbackId);
        // why is the one below better?

        $errors = $this->model->checkUpdate($args->post);
        if (count($errors) > 0) {
            // show form again
            $vars['errors'] = $errors;
            $mem_redirect->post = $vars;
            return false;
        }

        $comment = $this->model->getSingle($args->post['id']);
        $this->model->update($comment, $args->post);
        
        $msg = ""; // TODO
        MOD_log::get()->write($msg, 'AdminComments');
        return $this->_singleComment($args->post['id'], "update", $member);
    }
    
    public function delete()
    {
        list($member, $rights) = $this->checkRights('Comments');
        // TODO: test this scope check!
        if ((stripos($rights['Rights']['Scope'], 'DeleteComment') === false) &&
             (stripos($rights['Rights']['Scope'], 'All') === false)) {
            $msg = "You%20don%27t%20have%20the%20right%20to%20delete%20comments";
            $this->redirectAbsolute($this->router->url('admin_comments_list'), "msg=".$msg);
        }
        // TODO: why not use this?
        // $userRights = MOD_right::get();
        // $scope = $userRights->RightScope('Accepter');
        // see volunteerbar.model.php
        
        $id = $this->args_vars->get['id'];
        $oldComment = $this->model->getSingle($id);
        $msg = "Deleting comment #". $id . " previous where=" . $oldComment->TextWhere . 
                " previous text=" . $oldComment->TextFree . " previous Quality=" . 
                $oldComment->Quality;
        MOD_log::get()->write($msg, 'AdminComment');
        $this->model->delete($this->args_vars->get['id']);
        return $this->_singleComment(null, "delete", $member);
    }
    
    private function _singleComment($id, $action, $member)
    {
        $page = new AdminCommentsPage($action);
        $page->member = $member;
        $comments = array();
        $comments[] = $this->model->getSingle($id);
        $page->comments = $comments;

        // TODO: a paging mechanism is hardly the best way for a single item
        // - but this enables us to leave the template as it is
        $params = new StdClass();
        $params->strategy = new HalfPagePager('left');
        $params->items = 1;
        $params->items_per_page = 1;
        $page->pager = new PagerWidget($params);
        return $page;
    }
}
