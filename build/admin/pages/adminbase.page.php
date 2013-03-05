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
     * base page for the admin app
     * all admin pages are extended from this
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminBasePage extends PageWithActiveSkin
{
    protected $model = false;
    protected $rights = false;
    protected $member = false;
    
    public function __construct($model = false) {
        parent::__construct();
        if ($model) {
            $this->model = $model;
            $this->member = $this->model->getLoggedInMember();
            $this->rights = $this->member->getOldRights();
        }
    }
    
    protected function getPageTitle() 
    {
        return 'Volunteer Pages - BeWelcome';
    }
    
    public function teaserHeadline()
    {
        return "<a href='admin'>{$this->words->get('AdminTools')}</a>";
    }
    
    protected function getStylesheets() 
    {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/admin.css';
       return $stylesheets;
    }
    

}
