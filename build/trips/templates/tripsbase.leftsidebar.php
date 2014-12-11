<?php
/*

Copyflag (c) 2007 BeVolunteer

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
?>
<h3><?php echo $this->words->get('TripsSideBarTitle'); ?></h3>
<ul class="linklist">
<?php
    foreach($this->sidebar as $key => $item) { ?>
    <li>
    <?php
        if ($this->current == $key) { ?>
            <span><?= $this->words->get($key) ?></span>
        <?php } else { ?>
            <a href="<?= $item ?>"><?= $this->words->get($key) ?></a>
        <?php } ?>
        </li>
<?php } ?>
</ul>