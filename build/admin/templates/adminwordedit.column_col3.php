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
$trSent  = (isset($this->data->TrSent)?$this->data->TrSent:(isset($_SESSION['form']['Sentence'])?$_SESSION['form']['Sentence']:''));
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
        <span class="smallXtext"><?= $dat->EngDesc ?></span></td>
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
    if ($engDnt == "yes") {
        echo '<span class="awdntwarning">Do not translate</span>';
    }
?>
</td></tr>
<tr><td class="label">Description:</td><td>
<em><?= $engDesc ?></em>
</td></tr>
    
<tr><td class="label" >English source: </td>
<?php
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
$NbRows = 3 + strlen($trSent)/75;
echo ' rows='.$NbRows.'>'. $trSent .'</textarea></td>';
?>
  </tr>
  <tr>
    <td class="label"><label for="lang">Language:</label> </td>
    <td>

    <select id="lang" name="lang"><option value=""></option>
<?php
    foreach($this->langarr as $language) {
        echo '<option value="' . $language->ShortCode . '"';
        if ($this->nav['shortcode'] == $language->ShortCode) {
            echo ' selected="selected"';
        }
        echo '>' . trim($language->EnglishName) . ' (' . $language->ShortCode . ')</option>';
    }
?>
</select></td></tr>
  <tr>
    <td colspan="2" align="center">
      <input class="button" type="submit" id="submit1" name="DOACTION" value="Submit">
      <input class="button" type="submit" id="submit2" name="DOACTION" value="Find">
      <?php //<input class="button" type="submit" id="submit3" name="DOACTION" value="Delete" onclick="confirm('Are you sure you want to delete this?');"> ?>
    </td>
  </tr>
</table>
</form>
<?php }} ?>