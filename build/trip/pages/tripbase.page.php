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

class TripBasePage extends PageWithActiveSkin
{

    public function __construct()
    {
        parent::__construct();
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
        $params = new StdClass;
        $params->strategy = new HalfPagePager('right');
        $params->items = $elements;
        $params->items_per_page = $items_per_page;
        $params->page_url = "/trip/";
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $this->pager = new PagerWidget($params);
    }

    protected function leftSideBar()
    {
        if (!$this->member)
        {
            return false;
        }
        require SCRIPT_BASE . 'build/trip/templates/userbar.php';
    }

    protected function teaserContent()
    {
        if (!$this->member)
        {
            require SCRIPT_BASE . "build/trip/templates/teaser_public.php";
        }
        else
        {
            $userHandle = $this->member->Username;
            require SCRIPT_BASE . "build/trip/templates/teaser.php";
        }
    }

    protected function getStylesheets()
    {
        $array = parent::getStylesheets();
        $array[] = "styles/css/minimal/screen/custom/trip.css?3";
        return $array;
    }
}
