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
$words = new MOD_words();
?>
<h3>
    <?php
        // TODO: this doesn't work with the latest db, the code is archived! 
        echo $this->words->get('VolunteerToolsBarTitle');
    ?>
</h3>
<!-- TODO: how to insert ../../templates/adminoverview.leftsidebar.php? -->
</br>
<h3><?php echo $words->get('Action'); ?></h3>
<ul class="linklist">
    <li><a href="<?php echo $this->router->url('admin_comments_list'); ?>">Negative comments</a></li>
    <?php
        $right_names = array_keys($this->rights);
        if (in_array('Comments', $right_names) && 1==1 /* TODO: placeholder for: Scope must be "AdminAbuser" aka "Abusive" */ )
        {
    ?>
        <li><a href="<?php echo $this->router->url('admin_comments_list') .'?action=showAbusive'; ?>">Abusive comments</a></li>
    <?php } ?>
    <li><a href="<?php echo $this->router->url('admin_comments_list') . '?action=showAll'; ?>">All comments</a></li>
</ul>
