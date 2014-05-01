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
$callback_tag = $formkit->setPostCallback('AdminWordController', 'createCodeCallback');

$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    echo '<div class="error">';
    foreach($errors as $error) {
        echo $words->get($error) . "<br />";
    }
    echo "</div>";
}

if ($this->nav['level'] < 10){
    // create new wordcode on Normal Level
    echo '<h2>You do not have rights to create a new wordcode</h2>';
} else {


?>
<form method="post" name="TrEdit">
<?= $callback_tag ?>
<table class="admin" border="0">
  <tr>
    <td class="label"><label for="code">Code:</label> </td>
    <td><input name="EngCode" id="code" value="<?= htmlspecialchars($this->formdata['EngCode']) ?>" size="40">
  </tr>


<?php
include 'adminword.codevarsform.php';
?>
  <tr>
    <td class="label" >English text: </td>
    <td><textarea name="Sentence" class="long" cols="60"><?= htmlspecialchars($this->formdata['Sentence']) ?></textarea></td>
  </tr>

<input type=hidden name=changetype value=none>
  <tr>
    <td></td><td>
      <input class="button" type="submit" id="submit1" name="submitBtn" value="Create">
    </td>
  </tr>
</table>
<input type="hidden" name="lang" value="en">
</form>
<?php    } ?>
