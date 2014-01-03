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
    
if ($this->noScope){
    echo '<h2>You do not have translation rights for this language</h2>';
    echo 'Your scope: '.$this->showScope();
} else {
    $vars = $this->getRedirectedMem('vars');
   
    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('AdminWordController', 'EditTranslationCallback');
    
    $errors = $this->getRedirectedMem('errors');
    if (!empty($errors)) {
        echo '<div class="error">';
        foreach($errors as $error) {
            echo $words->get($error) . "<br />";
        }
        echo "</div>";
    }
    
/********************
***   edit form   ***
********************/

?>

<form method="post" name="TrEdit">
<?= $callback_tag ?>
<table class="admin" border="0">
  <tr>
    <td class="label"><label for="code">Code:</label> </td>
    <td><input name="EngCode" id="code" value="<?= htmlspecialchars($this->formdata['EngCode']) ?>" size="56">
<?php
    if ($this->formdata['EngDnt'] == "yes") {
        echo '<span class="awdntwarning">Do not translate</span>';
    }
?>
</td></tr>
<tr><td class="label">Description:</td><td>
<em><?= htmlspecialchars($this->formdata['EngDesc']) ?></em>
</td></tr>
    
<tr><td class="label" >English source: </td>
<?php
$tagold = array("&lt;", "&gt;");
$tagnew = array("<font color=\"#ff8800\">&lt;", "&gt;</font>");
echo '<td>'. str_replace("\n","<br />",
                str_replace($tagold,$tagnew,
                    htmlentities($this->formdata['EngSent'], ENT_COMPAT, 'UTF-8'))).'</td>';
?>
</tr>
<tr>
<td class="label"><label for="Sentence">Translation:</label> </td>
<td><textarea name="Sentence" class="long" cols="60"
<?php
$NbRows = 3 + strlen($this->formdata['Sentence'])/75;
echo ' rows='.$NbRows.'>'. htmlspecialchars($this->formdata['Sentence']) .'</textarea></td>';
?>
  </tr>
  <tr>
    <td class="label"><label for="lang">Language:</label> </td>
    <td>

    <select id="lang" name="lang"><option value=""></option>
<?php
    foreach($this->langarr as $language) {
        echo '<option value="' . htmlspecialchars($language->ShortCode) . '"';
        if ($this->formdata['lang'] == $language->ShortCode) {
            echo ' selected="selected"';
        }
        echo '>' . htmlspecialchars(trim($language->EnglishName)) . ' (' . htmlspecialchars($language->ShortCode) . ')</option>';
    }
?>
</select></td></tr>
  <tr>
    <input type=hidden name=EngDesc value="<?=htmlspecialchars($this->formdata['EngDesc'])?>">
    <input type=hidden name=EngSent value="<?=htmlspecialchars($this->formdata['EngSent']);?>">
    <input type=hidden name=EngDnt value="<?=$this->formdata['EngDnt']?>">
    <td></td><td>
      <input class="button" type="submit" name="findBtn" value="Find code">&nbsp;&nbsp;&nbsp;&nbsp;
      <input class="button" type="submit" name="submitBtn" value="Submit translation">
      <?php //<input class="button" type="submit" id="submit3" name="DOACTION" value="Delete" onclick="confirm('Are you sure you want to delete this?');"> ?>
    </td>
  </tr>
</table>
</form>
<?php } ?>