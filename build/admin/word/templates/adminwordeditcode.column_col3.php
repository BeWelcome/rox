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
    
$formkit = $this->layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('AdminWordController', 'editCodeCallback');

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
<form method="post" name="TrEdit" action="">
<?= $callback_tag ?>
<table class="admin" border="0">

<?php
    if (empty($this->formdata['EngCode'])){
?>
  <tr><td class="label"><label for="code">Code:</label> </td>
  <td><input name="EngCode" id="code" value="<?= htmlspecialchars($this->formdata['EngCode']) ?>" size="56" /></tr>
<?php
    } else {
echo $words->getformatted(($this->status),htmlspecialchars($this->formdata['EngCode']));
?>
<input type=hidden name="Sentence" value="<?= htmlspecialchars($this->formdata['Sentence']) ?>" />
<input type=hidden name="EngSent" value="<?= htmlspecialchars($this->formdata['EngSent']) ?>" />
<input type=hidden name="EngCode" value="<?= htmlspecialchars($this->formdata['EngCode']) ?>" />
<input type=hidden name="lang" value="<?= htmlspecialchars($this->formdata['lang']) ?>" />
<?php
    }

    if ($this->nav['level'] >= 10 ) { 
        // On Admin Level show extra variables
        include 'adminword.codevarsform.php';
    } else {
?>
    <input type=hidden name=EngDesc value="<?=htmlspecialchars($this->formdata['EngDesc'])?>" />
    <input type=hidden name=EngPrio value="<?=htmlspecialchars($this->formdata['EngPrio'])?>" />
    <input type=hidden name=EngDnt value="<?=$this->formdata['EngDnt']?>" />
<?php
    }

    if ($this->status=='AdminWordUpdateCodeMsg'){
    // updating an existing wordcode
?>
<tr><td>What kind of change is this?</td>
<td><input type="radio" name="changetype" value="minor" /> Minor change - old translations remain valid<br />
<input type="radio" name="changetype" value="major" /> Major change - old translations are invalidated
</td></tr>    
<?php
    } else {
        echo '<input type="hidden" name="changetype" value="none" />';
    }
?>
  <tr>
    <td colspan="2" align="center">
      <input class="button" type="submit" id="submit3" name="DOACTION" value="Back" />
      <input class="button" type="submit" id="submit1" name="DOACTION" value="Submit" />
    </td>
  </tr>
</table>
</form>
<?php    } ?>
