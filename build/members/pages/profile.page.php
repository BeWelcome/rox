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
     * @author Micha
     * @author Globetrotter_tt
     */

    /** 
     * members base page
     * 
     * @package    Apps
     * @subpackage Members
     * @author     Micha
     * @author     Globetrotter_tt
     */

class ProfilePage extends MemberPage
{
    protected function getSubmenuActiveItem()
    {
        return 'profile';
    }

    /*
     * Creates login link displayed to not loggedin users in stead of hidden content
     *
     * @param string $url Link to forward to after login
     * @param string $code Wordcode to show after logintext
     * @param Word $words Translation functionality
     * @return string Text for login including link
     */
    public function getLoginLink($url,$code){

    $loginUrlOpen = '<a href="login' . $url . '#login-widget">';
    $loginUrlClose = '</a>';

    return $this->words->get($code,
                             $this->words->getSilent('ProfileShowLogin'),
                             $loginUrlOpen,
                             $loginUrlClose);
    
    }
}
