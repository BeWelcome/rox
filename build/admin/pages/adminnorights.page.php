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
     * page telling people they can't access the admin app as they have no rights
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminNoRightsPage extends AdminBasePage
{
    protected function column_col3() 
    {
        $words = new MOD_words();
        echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("Volunteer_Join"),"</h3>";
		echo "<p>",$words->get("Volunteer_JoinText"),"</p>";
		echo "</div>\n";
    }

    protected function leftSidebar()
    {
        
    }
}
