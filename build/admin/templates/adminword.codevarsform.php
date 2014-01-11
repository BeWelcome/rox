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
?>
<tr><td>Description :</td><td class="smallXtext">
<textarea style="margin:0;" cols=60 rows=3 name='EngDesc'><?=$this->formdata['EngDesc']?></textarea><br>
Make sure the code has a proper description. Make clear where this code shows up,
in which case it shows up, what the function is of the element where it shows up, etc.<br>
Describe also the function and possible values of all included placeholders.<br>
Do NOT copy the wordcode or the English text, that doesn't help anyone.</td></tr>
    <tr><td>Should this code be translated?</td><td><select name="EngDnt">
  <option value="no"
<?php if ($this->formdata['EngDnt'] == "no") echo " selected='selected'"; ?>
    >translatable</option>
  <option value="yes"
<?php if ($this->formdata['EngDnt'] == "yes") echo " selected='selected'"; ?>
    >not translatable</option>
    </select></td></tr><tr>
<td>Is this code still active?</td><td><select name="isarchived">
  <option value="0"
<?php if ($this->formdata['isarchived'] == "0") echo " selected='selected'"; ?>
    >active</option>
  <option value="1"
<?php if ($this->formdata['isarchived'] == "1") echo " selected='selected'"; ?>
    >archived</option>
    </select></td></tr>
    <tr><td>Translation Priority</td><td><select name="EngPrio">
<?php
for ($prio=1;$prio<=10;$prio++){
    echo '<option value='.$prio;
    $default = (empty($this->formdata['EngPrio'])?5:$this->formdata['EngPrio']);
    if ($prio==$default){echo ' selected="selected"';}
    echo '>'.$prio.'</option>';
}
?>
</select>