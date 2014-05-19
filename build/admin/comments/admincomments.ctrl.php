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
    
        $page = new AdminCommentsPage($action);
        $page->member = $member;
        
        $action = $this->args_vars->get['action'];
        
        switch($action) {
            case "delete":
                $page->comments = $this->model->deleteComment($this->args_vars->get['idComment']);
                // TODO: check what feedback the user gets in current implementation...
                // $page->say("Success");
                
                // drop through
            case "showAll":
                // drop through
            case "showAbusive":
                // drop through
            case "":
                // drop through
            case "showNegative":
                $page->comments = $this->model->getComments($action);
                break;
            case "toggleHide":
                $this->_model->toggleHideComment($this->args_vars->get['idComment']);
                $comments = array();
                $comments[] = $this->model->getComment($this->args_vars->get['idComment']);
                $page->comments = $comments;
                break;
            case "markChecked":
                //$this->_model->checkedComment($this->args_vars->get['idComment']);
                $comments = array();
                $comments[] = $this->model->getComment($this->args_vars->get['idComment']);
                $page->comments = $comments;
                break;
            case "update":
                //$this->_model->updatedComment($this->args_vars->get['idComment']);
                $comments = array();
                $comments[] = $this->model->getComment($this->args_vars->get['idComment']);
                $page->comments = $comments;
                break;
            default:
                // TODO: log error: unsupported param exception
                break;
        }
        
        // TODO: a paging mechanism is hardly the best way for single items
        $params = new StdClass();
        $params->strategy = new HalfPagePager('left');
        $params->items = count($page->bad_comments); // TODO: needed for single-item pages?
        $params->items_per_page = 25;
        $page->pager = new PagerWidget($params);

        return $page;
    }
}
