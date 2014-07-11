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
 * admin comments controller
 * 
 * This controller is to view and update comments. Initially the user views a list of comments, based on a filter
 * ("all", "negative", "abusive", "from" (user), "to" (user)). The user can then update comments individually either 
 * by changing form values and clicking the "Update" button, or by clicking one of the other buttons. An update will 
 * display the updated comment, enabling the user to view the change and make further updates.
 *
 * @package apps
 * @subpackage Admin
 */
class AdminCommentsController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->model = new AdminCommentsModel();
    }

    public function __destruct() {
        unset($this->model);
    }

    /**
     * An html form triggers an update via form submit (POST). The function updates data of a specific comment and 
     * displays a message in the result page.
     * 
     * @param StdClass $args
     * @param ReadOnlyObject $action
     * @param ReadWriteObject $mem_redirect
     * @param ReadWriteObject $mem_resend
     * @return false (in case of a failure) or string pointing to a page that displays a single comment
     */
    public function updateCallback(StdClass $args, ReadOnlyObject $action,
            ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $errors = $this->model->checkUpdate($args->post);
        if (count($errors) > 0)
        {
            $mem_redirect->post = $args->post;
            $mem_redirect->errors = $errors;
            return false;
        }

        $a = $this->model->getSingle($args->post['id']);
        $c = $a[0];
        $msg = "Updating comment #" . $args->post['id'] . 
                " previous where=" . $c->TextWhere .
                " previous text=" . $c->TextFree . 
                " previous Quality=" . $c->Quality;
        MOD_log::get()->write($msg, 'AdminComments');
        
        $update = $this->model->update($c, $args->post);

        if($args->post['subset']=='from')
        {
            $mem_redirect->comments = $this->model->getFrom($this->route_vars['id']);
        }
        else if($args->post['subset']=='to')
        {
            $mem_redirect->comments = $this->model->getTo($this->route_vars['id']);
        }
        else
        {
            $mem_redirect->comments = $this->model->getSubset($args->post['subset']);
        }

        $this->setFlashNotice("Updated comment of " . $args->post['nameFrom'] .
                " about " . $args->post['nameTo'] . ".");
        return $this->router->url('admin_comments_list_single', array('id' => $args->post['id']), false);
    }
    
    /**
     * Display all, all negative or all abusive comments, depending on the given subset.
     * 
     * @return type
     */
    public function subset()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $page = new AdminCommentsPage($this->model);
        $page->setSubset($this->route_vars['subset']);
        $page->comments = $this->model->getSubset($this->route_vars['subset']);
        return $this->_buildPage($page);
    }
    
    /**
     * Display all comments created by a specific user.
     * 
     * @return type
     */
    public function from()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $page = new AdminCommentsPage($this->model);
        $page->setSubset("from");
        $page->comments = $this->model->getFrom($this->route_vars['id']);
        return $this->_buildPage($page);
    }
    
    /**
     * Display all comments about a specific user.
     * 
     * @return type
     */
    public function to()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $page = new AdminCommentsPage($this->model);
        $page->setSubset("to");
        $page->comments = $this->model->getTo($this->route_vars['id']);
        return $this->_buildPage($page);
    }
    
    /**
     * Display a specific comment. This is used in the wake of an update of a comment.
     * 
     * @return type
     */
    public function single()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $this->_processGet();
        $page = new AdminCommentsPage($this->model);
        $page->setSubset("single");
        $page->comments = $this->model->getSingle($this->route_vars['id']);
        return $this->_buildPage($page);
    }
    
    /**
     * Build a page listing max. 25 comments.
     * 
     * @param type $page
     * @return type
     */
    private function _buildPage($page)
    {
        $params = new StdClass();
        $params->strategy = new HalfPagePager('left');
        $params->items = count($page->comments);
        $params->items_per_page = 25;
        $page->pager = new PagerWidget($params);
        return $page;
    }

    /**
     * An html form triggers an update of a specific attribute of a comment (form GET). (These attributes are those 
     * that are changed by a single click of a button.) The function updates the attribute and displays a respective
     * message in the result page.
     */
    private function _processGet()
    {
        if($this->args_vars->get)
        {
            if(array_key_exists('toggleHide', $this->args_vars->get))
            {
                $update = $this->model->toggleHide($this->route_vars['id']);
                $this->_setUpdateMessage();
            }
            else if(array_key_exists('toggleAllowEdit', $this->args_vars->get))
            {
                $update = $this->model->toggleAllowEdit($this->route_vars['id']);
                $this->_setUpdateMessage();
            }
            else if(array_key_exists('markChecked', $this->args_vars->get))
            {
                $update = $this->model->markChecked($this->route_vars['id']);
                $this->_setUpdateMessage();
            }
            else if(array_key_exists('markAdminAbuserMustCheck', $this->args_vars->get))
            {
                $update = $this->model->markAdminAbuserMustCheck($this->route_vars['id']);
                $this->_setUpdateMessage();
            }
            else if(array_key_exists('markAdminCommentMustCheck', $this->args_vars->get))
            {
                $update = $this->model->markAdminCommentMustCheck($this->route_vars['id']);
                $this->_setUpdateMessage();
            }
            else if(array_key_exists('delete', $this->args_vars->get))
            {
                $update = $this->model->delete($this->route_vars['id']);
                $this->setFlashNotice("Deleted comment of " . $this->args_vars->get['nameFrom'] .
                    " about " . $this->args_vars->get['nameTo'] . ".");        
            }
        }
    }
    
    /**
     * Helper function that sets the flash notice in a result page.
     */
    private function _setUpdateMessage()
    {
        $this->setFlashNotice("Updated comment of " . $this->args_vars->get['nameFrom'] .
            " about " . $this->args_vars->get['nameTo'] . ".");        
    }
}
