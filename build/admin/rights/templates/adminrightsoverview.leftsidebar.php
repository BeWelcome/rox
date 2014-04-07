<?php
/*

Copyright (c) 2007 BeVolunteer

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
$words = new MOD_words();
?>
<h3><?php echo $words->get('AdminRightsToolsBarTitle'); ?></h3>
<ul class="linklist">
    <li><a href="admin/rights"><?php echo $words->get('AdminRightsOverview'); ?></a></li>
    <li><a href="admin/rights/assign"><?php echo $words->get('AdminRightsAssign'); ?></a></li>
    <li><a href="admin/rights/list/member"><?php echo $words->get('AdminRightsListByMember'); ?></a></li>
    <li><a href="admin/rights/list/right"><?php echo $words->get('AdminRightsListByRight'); ?></a></li>
    <li><a href="admin/rights/create"><?php echo $words->get('AdminRightsCreate'); ?></a></li>
</ul>