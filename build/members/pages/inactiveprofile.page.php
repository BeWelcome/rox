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

class InactiveProfilePage extends MemberPage
{
    protected function getSubmenuActiveItem()
    {
        return 'profile';
    }

    protected function leftsidebar() {
        // TODO: move HTML to a template
        $member = $this->member;
        $words = $this->getWords();
        $thumbnail_url = 'members/avatar/'.$member->Username.'?150';
        $picture_url = 'members/avatar/'.$member->Username.'?500';
        ?>

        <div id="profile_pic" >
            <a href="<?=$picture_url?>" id="profile_image"><img src="<?=$thumbnail_url?>" alt="Picture of <?=$member->Username?>" class="framed" height="150" width="150"/></a>
            <div id="profile_image_zoom_content" class="hidden">
                <img src="<?=$picture_url?>" alt="Picture of <?=$member->Username?>" />
            </div>
            <script type="text/javascript">
                // Activate FancyZoom for profile picture
                // (not for IE, which don't like FancyZoom)
                if (typeof FancyZoom == "function" && is_ie === false) {
                    new FancyZoom('profile_image');
                }
            </script>
        </div> <!-- profile_pic -->
    <?php
    }

}
