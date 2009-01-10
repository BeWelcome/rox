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
// get current request
$request = PRequest::get()->request;

$words = new MOD_words();

if (!isset($vars['errors']) || !is_array($vars['errors'])) {
    $vars['errors'] = array();
}

$Data=$this->_data ; // Retrieve the data to display (set by the controller)
$list=$Data->Choices ; // Retrieve the possible choices 

echo "<p>",$words->fTrad($Data->rPoll->Title); 
echo "<br /><i>",$words->fTrad($Data->rPoll->Description),"</i><br />" ;
		if ($Data->rPoll->Anonym=="Yes") {
			echo "<br />",$words->getFormatted("pols_IsAnonymExplanation")  ;
		}
		else {
			echo "<br />",$words->getFormatted("pols_IsNotAnonymExplanation")  ;
		}
		echo "</p>" ;


$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($list) ; // This retrieve the number of polls
$IdPoll=$Data->rPoll->id ;
?>

<p><form name="contribute" action="polls/vote"  id="idcontribute" method="post">
<input type="hidden" name="IdPoll" value="<?=$IdPoll ?>">
<!-- The following will disable the nasty PPostHandler -->
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
    
<input type="hidden" name="<?=$callbackId ?>"  value="1"/>

<table class="full" width="60%">

<?php if ($list != false) { ?>
    <tr>
        <th><?=$words->getFormatted("polls_choice")." (".$words->getFormatted("polls_typechoice_".$Data->rPoll->TypeOfChoice).")" ?></th>
        <th><?=$words->getFormatted("poll_yourchoice") ?></th>
    </tr>
<?php }
?>


<?php
for ($ii = 0; $ii < $iiMax; $ii++) {
    $p = $list[$ii];
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td align=left><? echo $words->fTrad($p->IdChoiceText); ?></td>
        <td align="left" width="10%">
						<? 
						if ($Data->rPoll->TypeOfChoice=="Exclusive") {
							echo "<input type=\"radio\" name=\"ExclusiveChoice\" value=\"".$p->id."\">\n" ;
						}
						if ($Data->rPoll->TypeOfChoice=="Inclusive") {
							echo "<input type=\"checkbox\" name=\"choice_$p->id\">\n" ;
						}
						?>
        </td>
    </tr>
		
    <?php
}
if ($Data->rPoll->AllowComment=="Yes") {
	echo "<tr><td colspan=2 align=left>",$words->getFormatted("polls_comment"),"</td></tr>" ;
	echo "<tr><td colspan=2 align=center><textarea name=\"Comment\" cols=\"60\"  rows=\"4\"></textarea></td></tr>" ;
}
else {
	echo "<input type=\"hidden\" name=\"Comment\" value=\"\">" ;
} 
echo "<tr><td colspan=2 align=center><input type=submit value=\"",$words->getFormatted("polls_vote"),"\"></td></tr>" ;
?>
</table>
</form>