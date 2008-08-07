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
$words = new MOD_words();
?>

  <ul id="steps">
   <li class="<?=($step=='1') ? 'active' : '' ?>"><div class="number">1</div><div class="desc"><a href="signup/1" <?=($step =='1') ? 'onclick="$(\'user-register-form\').action = \'signup/1\'; $(\'user-register-form\').submit(); return false"' : '' ?>><?php echo $words->getFormatted('LoginInformation')?></a></div></li>
   <li class="<?=($step=='2') ? 'active' : '' ?>"><div class="number">2</div><div class="desc"><a href="signup/2" <?=($step <='2') ? 'onclick="$(\'user-register-form\').action = \'signup/2\'; $(\'user-register-form\').submit(); return false"' : '' ?>><?php echo $words->getFormatted('SignupName')?></a></div></li>
   <li class="<?=($step=='3') ? 'active' : '' ?>"><div class="number">3</div><div class="desc"><a href="signup/3" <?=($step <='3') ? 'onclick="$(\'user-register-form\').action = \'signup/3\'; $(\'user-register-form\').submit(); return false"' : '' ?>><?php echo $words->getFormatted('Location')?></a></div></li>
   <li class="<?=($step=='4') ? 'active' : '' ?>"><div class="number">4</div><div class="desc"><a href="signup/4" <?=($step <='4') ? 'onclick="$(\'user-register-form\').action = \'signup/4\'; $(\'user-register-form\').submit(); return false"' : '' ?>><?php echo $words->getFormatted('SignupSummary')?></a></div></li>
  </ul>
