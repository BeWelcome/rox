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
     * @author Tsjoek
     */

    /** 
     * words management translation template
     * 
     * @package Apps
     * @subpackage Admin
     */
    
// first value for updates, second one for inserts
//$isarch  = (isset($this->data->isarchived)?$this->data->isarchived:'0');
//$descri = (isset($this->data->description)?$this->data->description:'');
//$wrdcod = (isset($this->data->code)?$this->data->code:$_SESSION['form']['code']);
//$trprio = (isset($this->data->TranslationPriority)?$this->data->TranslationPriority:'5');
//$donotr  = (isset($this->data->Engdonottranslate)?$this->data->donottranslate:'no');


$formkit = $this->layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('AdminWordController', 'trEditEngCallback');

$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    echo '<div class="error">';
    foreach($errors as $error) {
        echo $words->get($error) . "<br />";
    }
    echo "</div>";
}

if ($this->noScope){
    echo '<h2>You do not have translation rights for this language</h2>';
} elseif ($this->status=='create' && $this->nav['level'] < 10){
    // create new wordcode on Normal Level
    echo '<h2>You do not have rights to create a new wordcode</h2>';
} else {


?>
You are about to <?= $this->status ?> the English wordcode : <?=htmlspecialchars($this->formdata['EngCode'])?>
<form method="post" name="TrEdit">
<?= $callback_tag ?>
<table class="admin" border="0">
<?php

    if ($this->nav['level'] >= 10 ) { 
    // On Admin Level show extra variables
?>
<tr><td>Description :</td><td class="smallXtext"><textarea style="margin:0;" cols=60 rows=3 name='EngDesc'><?=$this->formdata['EngDesc']?></textarea><br>
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
    </select></td></tr><tr><td>
Translation Priority</td><td><input type="text" name="EngPrio"
    value="<?=(int)$this->formdata['EngPrio'] ?>" size="2"></td></tr>
<?php } else {
?>
    <input type=hidden name=EngDesc value="<?=htmlspecialchars($this->formdata['EngDesc'])?>">
    <input type=hidden name=EngPrio value="<?=htmlspecialchars($this->formdata['EngPrio'])?>">
    <input type=hidden name=EngDnt value="<?=$this->formdata['EngDnt']?>">
<?php
}

    
if ($this->status=='update'){
    // updating an existing wordcode
?>
<tr><td>What kind of change is this?</td>
<td><input type="radio" name="changetype" value="minor"> Minor change - old translations remain valid<br>
<input type="radio" name="changetype" value="major"> Major change - old translations are invalidated
</td></tr>    
<?php
    } else echo '<input type=hidden name=changetype value=none>';
?>
  <tr>
    <td colspan="2" align="center">
      <input class="button" type="submit" id="submit3" name="DOACTION" value="Back">
      <input class="button" type="submit" id="submit1" name="DOACTION" value="Submit">
    </td>
  </tr>
</table>
<input type=hidden name="TrSent" value="<?= htmlspecialchars($this->formdata['TrSent']) ?>">
<input type=hidden name="EngSent" value="<?= htmlspecialchars($this->formdata['TrSent']) ?>">
<input type=hidden name="EngCode" value="<?= htmlspecialchars($this->formdata['EngCode']) ?>">
<input type=hidden name="lang" value="<?= htmlspecialchars($this->formdata['lang']) ?>">
</form>
<?php    } ?>
