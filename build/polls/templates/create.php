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

$words = $this->getWords();

if (!empty($errormessage)) {
    echo "<p><b>$errormessage</b></p>";
}
$words = new MOD_words();
$Data=$this->_data  ;
if (isset($Data->rPoll->id)) { // Form for update
	$rr=$Data->rPoll ;


?>
<p>
This is the page to update a poll

You need to fill the following fields

Use English language for now only

</p>

<p><form name="contribute" action="polls/doupdatepoll"  id="idupdatepoll" method="post">
<!-- The following will disable the nasty PPostHandler -->
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
    
<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
<input type="hidden" name="IdPoll"  value="<?=$Data->rPoll->id?>"/>

<table class="full" width="60%">

<tr><td>Poll Status
<?
$sChoice=array("Project","Open","Close") ;
if (empty($rr->Status)) $rr->Status="Project" ; // By default a poll will be at Project Status
	echo "<select name=\"Status\">\n" ;
for ($ii=0;$ii<count($sChoice);$ii++) {
	echo "<option value=\"".$sChoice[$ii]."\"" ;
	if ($sChoice[$ii]==$rr->Status) {
		echo " selected " ;
	}
	echo ">",$sChoice[$ii],"</option>\n" ;
}
echo "</select>\n"  ;
?>
</td></tr>


<tr><td>Type of choice
<?
$sChoice=array('Exclusive', 'Inclusive', 'Ordered') ;
if (empty($rr->TypeOfChoice)) $rr->TypeOfChoice="Exclusive" ; // By default a poll will be Exclusive
	echo "<select name=\"TypeOfChoice\">\n" ;
for ($ii=0;$ii<count($sChoice);$ii++) {
	echo "<option value=\"".$sChoice[$ii]."\"" ;
	if ($sChoice[$ii]==$rr->Status) {
		echo " selected " ;
	}
	echo ">",$sChoice[$ii],"</option>\n" ;
}
echo "</select>\n"  ;
?>
</td></tr>


<tr><td>Poll will end on :

<?
$ii=0 ;
if (empty($rr->Ended)) {
	$rr->Ended="0000-00-00 00:00:00" ; // By default a poll will not have a end
}
echo "<input name=\"Ended\" Value=\"" .$rr->Ended."\" type=\"text\">" ;
?>
</td></tr>
<tr><td>Allow comment when vote 
<?
if (empty($rr->AllowComment)) $rr->AllowComment="No" ; // By default a poll is not aimed to collect comments
$sChoice=array("Yes","No") ;
	echo "<select name=\"AllowComment\">\n" ;
for ($ii=0;$ii<count($sChoice);$ii++) {
	echo "<option value=\"".$sChoice[$ii]."\"" ;
	if ($sChoice[$ii]==$rr->AllowComment) {
		echo " selected " ;
	}
	echo ">",$sChoice[$ii],"</option>\n" ;
}
echo "</select>\n"  ;
?>
</td></tr>
<tr><td>Result visibility 
<?
if ($rr->ResultsVisibility=="") $rr->ResultsVisibility="No" ; // By default a poll is not aimed to collect comments
$sChoice=array("Not Visible","Visible","VisibleAfterVisit") ;
	echo "<select name=\"ResultsVisibility\">\n" ;
for ($ii=0;$ii<count($sChoice);$ii++) {
	echo "<option value=\"".$sChoice[$ii]."\"" ;
	if ($sChoice[$ii]==$rr->ResultsVisibility) {
		echo " selected " ;
	}
	echo ">",$sChoice[$ii],"</option>\n" ;
}
echo "</select>\n"  ;
?>
</td></tr>


<tr><td>Poll Title: <input type="text" name="Title" size="80" value="<?=$words->fTrad($Data->rPoll->Title)?>"/></td></tr>
<tr><td>Poll Description:<br /><textarea name="Description" cols="100" rows="5"><?=$words->fTrad($Data->rPoll->Description) ?></textarea></td>
<tr><td align="center"><input type="submit" value="go update">
</table>

</form>

<hr>
<table class="full" width="60%">
<?
for ($ii=0;$ii<count($Data->Choices);$ii++) {
	$cc=$Data->Choices[$ii] ;
	?>
	<p><form name="updatechoice_<?=$ii?>" action="polls/updatechoice"  id="idupdatechoice_<?=$ii?>" method="post">
	<!-- The following will disable the nasty PPostHandler -->
	<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
    
	<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
	<input type="hidden" name="<?=$IdLanguage ?>"  value="<?$_SESSION["IdLanguage"]?>"/>
	<input type="hidden" name="IdPoll"  value="<?=$Data->rPoll->id?>"/>
	<input type="hidden" name="IdPollChoice" value="<?=$cc->id?>"/>
	<input type="hidden" name="IdChoiceText" value="
	<?=$cc->IdChoiceText?>
	"/>
<tr><td valign=center><textarea name="ChoiceText" cols="100" rows="2">
<?=$words->fTrad($cc->IdChoiceText)?>
</textarea>
 <input type="submit" value="update choice"></tr>
</form>
	<?
}
?>
</table>

<table class="full" width="60%">
	<p><form name="addchoice" action="polls/addchoice"  id="idaddchoice" method="post">
	<!-- The following will disable the nasty PPostHandler -->
	<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
    
	<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
	<input type="hidden" name="<?=$IdLanguage ?>"  value="<?$_SESSION["IdLanguage"]?>"/>
	<input type="hidden" name="IdPoll"  value="<?=$Data->rPoll->id?>"/>
<tr><td valign=left>New possible choice</td></tr>
<tr><td valign=center><textarea name="ChoiceText" cols="100" rows="2"></textarea> <input type="submit" value="Add"></td></tr>
</form>
</table>



</p>
<?
}
else { // form for create
?>
<p>
This is the page to create a new poll

You need to fill the following fields

Use English language for now only

</p>

<p><form name="contribute" action="polls/createpoll"  id="idcreatepoll" method="post">
<!-- The following will disable the nasty PPostHandler -->
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
    
<input type="hidden" name="<?=$callbackId ?>"  value="1"/>

<table class="full" width="60%">
	<input type="hidden" name="<?=$IdLanguage ?>"  value="<?$_SESSION["IdLanguage"]?>"/>
<tr><td>Poll Title: <input type="text" name="Title" size="80"></td></tr>
<tr><td>Poll Description:<br /><textarea name="Description" cols="100" rows="5"></textarea></td>
<tr><td align="center"><input type="submit" name="go create">
</table>

</form>


</p>
<?
}
?>