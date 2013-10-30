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
} else {
$engSent = (isset($this->data->EngSent)?$this->data->EngSent:'');
$trSent  = (isset($this->data->TrSent)?$this->data->TrSent:'');
$engDesc = (isset($this->data->EngDesc)?$this->data->EngDesc:'');
$engCode = (isset($this->data->EngCode)?$this->data->EngCode:'');
$engPrio = (isset($this->data->EngPrio)?$this->data->EngPrio:'');
$engDnt  = (isset($this->data->EngDnt)?$this->data->EngDnt:'');

$formkit = $this->layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('AdminWordController', 'trEditCreateCallback');


// searchresults
if (isset($_SESSION['trData'])){
    $data = $_SESSION['trData'];
    unset($_SESSION['trData']);
}

if (isset($data[0])){
?>

<table>
<tr><th>Code & Description</th><th>Sentence</th></tr>
<?php
foreach($data as $dat){
?>
<tr><td><b>
<?php
if ($dat->inScope){
    echo '<a href="/admin/word/edit/'.$dat->EngCode.'/'.$dat->TrShortcode.'">';
    echo $dat->EngCode.' - '.$words->get("lang_".$dat->TrShortcode).'</a>';    
} else {
    echo $dat->EngCode.' - '.$words->get("lang_".$dat->TrShortcode);
}
?>
</b><br>
        <span class="awlistdesc"><?= $dat->EngDesc ?></span></td>
    <td><?= $dat->TrSent ?></td></tr>
<?php } ?>
</table>
<?php } else {
// end searchresults

?>

<form method="post" name="TrEdit">
<?= $callback_tag ?>
<table class="admin" border="0">
  <tr>
    <td class="label"><label for="code">Code:</label> </td>
    <td><input name="code" id="code" value="<?= $engCode ?>" size="56">
<?php
if ($this->nav['level'] >= 10) { // Level 10 allow to change/set description
?>
    <br><select name="donottranslate">
    <option value="no"
<?php if ($engDnt == "no") echo " selected"; ?>
    >translatable</option>
    <option value="yes"
<?php if ($engDnt == "yes") echo " selected"; ?>
    >not translatable</option>
    </select>

    &nbsp;&nbsp;Translation Priority <input type="text" name="TranslationPriority" value="
    <?= $engPrio ?> " size="3">
<?php } else {
    if ($engDnt == "yes") {
        echo '<span class="awdntwarning">Do not translate</span>';
    }
    echo '<input type="hidden" name="donottranslate" value="'.$engDnt.'">';
    } ?>
</td></tr>
<tr><td class="label">Description:</td><td>
<?php 

    if ($this->nav['idLanguage'] == 0 AND $this->nav['level'] >= 10) {
        echo '<textarea name="Description" cols="60" class="long" rows="4">';
        echo $engDesc.'</textarea>';
    } else {
        echo "<em>\n", $engDesc.'</em>';
    }
    echo '</td></tr>';
    
    echo "<tr><td class=\"label\" >English source: </td>\n";
$tagold = array("&lt;", "&gt;");
$tagnew = array("<font color=\"#ff8800\">&lt;", "&gt;</font>");
echo '<td>'. str_replace("\n","<br />",
                str_replace($tagold,$tagnew,
                    htmlentities($engSent, ENT_COMPAT | ENT_HTML401, 'UTF-8'))).'</td>';
?>
</tr>
<tr>
<td class="label"><label for="Sentence">Translation:</label> </td>
<td><textarea name="Sentence" id="Sentence" class="long" cols="60"
<?php
$NbRows = 3*((substr_count($engSent, '\n') +
              substr_count($engSent, '<br />') +
              substr_count($engSent, '<br />'))+1);
echo ' rows='.$NbRows.'>'. $trSent .'</textarea></td>';
?>
  </tr>
  <tr>
    <td class="label"><label for="lang">Language:</label> </td>
    <td>

    <select id="lang" name="lang"><option value=""></option>
<?php
    $showMinorMajor = '';
    foreach($this->langarr as $language) {
        echo '<option value="' . $language->ShortCode . '"';
        if ($this->nav['shortcode'] == $language->ShortCode) {
            echo ' selected="selected"';
        }
        // if English is within scope then print the choice between minor/major
        if ($language->ShortCode=='en'){
            $showMinorMajor = '
                <tr><td>Changetype<br>This is only relevant when updating an English translation</td>
                <td><input type="radio" name="changetype" value="minor"> Minor change - old translations remain valid<br>
                <input type="radio" name="changetype" value="major"> Major change - old translations are invalidated
                </td></tr>';
            }
        echo '>' . trim($language->EnglishName) . ' (' . $language->ShortCode . ')</option>';
    }
?>
</select></td></tr>
<?= $showMinorMajor ?>
  <tr>
    <td colspan="2" align="center">
      <input class="button" type="submit" id="submit1" name="DOACTION" value="Submit">
      <input class="button" type="submit" id="submit2" name="DOACTION" value="Find">
      <input class="button" type="submit" id="submit3" name="DOACTION" value="Delete" onclick="confirm('Are you sure you want to delete this?');">
    </td>
  </tr>
</table>
</form>
<?php }} ?>