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
 * @author Tsjoek
 * 
 *  
 * @package Apps
 * @subpackage Admin
 */
$words = new MOD_words();
?>
<h3>Action</h3>
<ul class="linklist">
  <li><a href="admin/word">AdminWord</a></li>
  <li><a href="admin/word/stats">Show stats</a></li>
  <li><b><em><?= $this->nav['currentLanguage'] ?></em> :</b><br></li>
  <li>&nbsp;&nbsp;<a href="admin/word/list/all">All Words</a></li>
  <li>&nbsp;&nbsp;<a href="admin/word/list/missing">Only Missing</a></li>
  <li>&nbsp;&nbsp;<a href="admin/word/list/update">Update Needed</a></li>

</ul>