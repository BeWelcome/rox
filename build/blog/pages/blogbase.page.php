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
     * @author Fake51
     */

    /** 
     * base page for all blog pages
     *
     * @package Apps
     * @subpackage Blog
     */

class BlogBasePage extends PageWithActiveSkin
{

    public function __construct($model)
    {
        parent::__construct();
        $this->_model = $model;
    }

    /**
     * creates a pager and inits it with some params
     *
     * @param int $elements
     * @param int $page
     * @access public
     */
    public function initPager($elements, $page = 1, $items_per_page = 5)
    {
        $params->strategy = new HalfPagePager('right');
        $params->items = $elements;
        $params->items_per_page = $items_per_page;
        $this->pager = new PagerWidget($params);
    }

    protected function leftSideBar()
    {
        if (!$this->_model->getLoggedInMember())
        {
            return false;
        }
        require SCRIPT_BASE . 'build/blog/templates/userbar.php';
    }

    protected function teaserContent()
    {
        if (!$this->member)
        {
            require SCRIPT_BASE . "build/blog/templates/teaser_public.php";
        }
        else
        {
            $userHandle = $this->member->Username;
            require SCRIPT_BASE . "build/blog/templates/teaser.php";
        }
    }

    protected function categories_list($categoryId, $username = false) {
        require SCRIPT_BASE . 'build/blog/templates/categories_list.php';
    }

    protected function sidebarRSS()
    {
        require SCRIPT_BASE . 'build/blog/templates/sidebar_rss.php';
    }

    /**
     * returns the link to the rss feed
     *
     * @access protected
     */
    protected function getCustomElements()
    {
        $return = array();
        if ($this->useRSS)
        {
            $request = PRequest::get()->request;
            $requestStr = htmlspecialchars(implode('/', $request), ENT_QUOTES);
            $return[] = "<link rel='alternate' type='application/rss+xml' title='RSS 2.0' href='rss/{$requestStr}' />";
        }
        return $return;
    }

    protected function getStylesheets()
    {
        $array = parent::getStylesheets();
        $array[] = "styles/css/minimal/screen/custom/blog.css?3";
        if (!$this->member)
        {
            $array[] = "styles/css/minimal/screen/custom/bw_basemod_blog_public.css";
        }
        return $array;
    }

    protected function categoriesList($category_id = false, $member = false)
    {
        $catIt = $this->_model->getCategoryArray($category_id, $member);
        require_once SCRIPT_BASE . 'build/blog/templates/categories_list.php';
    }
}
