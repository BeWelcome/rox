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
$words = new MOD_words($this->getSession());
?>
<h3>Action</h3>
<ul class="linklist">
  <li><a href="admin/word">AdminWord</a></li>
  <li><a href="admin/word/find">Find Words</a></li>
<?php
if ($this->nav['level']>=10){
?>
  <li><a href="admin/word/createcode">Create Wordcode</a></li>
<?php } ?>
  <li><a href="admin/word/stats">Show Stats</a></li>
  <li><b><em><?= $this->nav['currentLanguage'] ?></em> :</b><br /></li>
  <li>&nbsp;&nbsp;<a href="admin/word/list/all/
<?php if ($this->nav['idLanguage']==0){
        echo 'long';
    } else {
        echo 'short';
    }
?>
  ">All Words</a></li>
<?php
if ($this->nav['idLanguage']>0){
?>
  <li>&nbsp;&nbsp;<a href="admin/word/list/missing/short">Only Missing</a></li>
  <li>&nbsp;&nbsp;<a href="admin/word/list/update/short">Update Needed</a></li>
<?php }





?>
</ul>