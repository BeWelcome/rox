<?php ?>
<tr><td>Description :</td><td class="smallXtext">
<textarea style="margin:0;" cols=60 rows=3 name='EngDesc'><?=$this->formdata['EngDesc']?></textarea><br>
Make sure the code has a proper description. Make clear where this code shows up,
in which case it shows up, what the function is of the element where it shows up, etc.<br>
Describe also the function and possible values of all included placeholders.<br>
Do NOT copy the wordcode or the English text, that doesn't help anyone.</td></tr>
    <tr><td>Should this code be translated?</td><td><select name="EngDnt">
  <option value="no"
<?php if ($this->formdata['EngDnt'] == "no") echo " selected"; ?>
    >translatable</option>
  <option value="yes"
<?php if ($this->formdata['EngDnt'] == "yes") echo " selected"; ?>
    >not translatable</option>
    </select></td></tr><tr>
<td>Is this code still active?</td><td><select name="isarchived">
  <option value="0"
<?php if ($this->formdata['isarchived'] == "0") echo " selected"; ?>
    >active</option>
  <option value="1"
<?php if ($this->formdata['isarchived'] == "1") echo " selected"; ?>
    >archived</option>
    </select></td></tr>
    <tr><td>Translation Priority</td><td><select name="EngPrio">
<?php
for ($prio=1;$prio<=10;$prio++){
    echo '<option value='.$prio;
    $default = (empty($this->formdata['EngPrio'])?5:$this->formdata['EngPrio']);
    if ($prio==$default){echo ' selected';}
    echo '>'.$prio.'</option>';
}
?>
</select>