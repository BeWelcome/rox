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
 * @author Matthias Heß <globetrotter_tt>
 */
$words = new MOD_words($this->getSession());
$userRights = MOD_right::get();
$scope = $userRights->RightScope('Comments');

?>

<h3><?php echo $words->get('Action'); ?></h3>
<ul class="linklist">
    <li><a href="<?php echo $this->router->url('admin_comments_list_subset', array('subset' => 'negative')); ?>">Negative comments</a></li>
    <?php
        if ($scope=="AdminAbuser" || $scope=='"All"')
        {
    ?>
        <li><a href="<?php echo $this->router->url('admin_comments_list_subset', array('subset' => 'abusive')); ?>">Abusive comments</a></li>
    <?php } ?>
    <li><a href="<?php echo $this->router->url('admin_comments_list_subset', array('subset' => 'all')); ?>">All comments</a></li>
</ul>
