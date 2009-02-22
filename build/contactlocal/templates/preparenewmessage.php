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

if (!isset($vars['errors']) || !is_array($vars['errors'])) {
    $vars['errors'] = array();
}

$list=$this->_data ; // Retrieve the data to display (set by the controller)
$PossibleLocations=$list->PossibleLocations ;
$PossibleLanguages=$list->PossibleLanguages ;
if (!empty($list->IdMess)) {
	$ChosenLocations=$list->localmessage->ChosenLocations ;
	$IdMess=$list->IdMess ;
	$TitleText=$words->fTrad($list->localmessage->IdTitleText);
	$MessageText=$words->fTrad($list->localmessage->IdMessageText);
	$PurposeDescription=$list->localmessage->PurposeDescription ;
	$ListMessage=$list->localmessage->ListMessageText ;
	$ListTitle=$list->localmessage->ListTitleText ;
//	print_r($list) ;
	
}
else {
	$TitleText="" ;
	$MessageText="" ;
	$PurposeDescription="" ;
	$IdMess=0 ;
}



$words = new MOD_words();
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($list) ; // This retrieve the number of polls
?>

<p class="note">
This page allows you to create a new message for a location

You will need to choose a location, a title, the text of the message

do not forget to choose in which language you wrote this message

The following page will allow you to translate this message in other languages
</p>

<?php
if (empty($IdMess)) { // We will only propose this when a new message is created
?>
<form name="preparenewmessage" action="contactlocal/recordnewmessage"  id="idpreparenewmessage" method="post">
<p>
<!-- The following will disable the nasty PPostHandler -->
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
<input type="hidden" name="IdMess"  value="<?=$IdMess?>"/>
<table><tr><td>
Language: <select  name="IdLanguage">
<?php
    foreach ($PossibleLanguages as $ll) {
		echo "<option value=\"".$ll->IdLanguage."\" " ;
		if ( isset($IdLanguage) and ($IdLanguage==$ll->id)) {
			echo " selected" ;
		}
		echo ">",$ll->LanguageName,"</option>\n" ;
	}
?>
</select><br/>
Title of message <input type="Text" name="Title"  size="100" value="<?=$TitleText?>"/><br />
Text of message <br/><textarea name="MessageText" cols="120" rows="5"><?=$MessageText?></textarea><br />
</td>
<tr><td>
	Purpose of this message (will not be sent to members fill in english)<br/>
	<textarea name="PurposeDescription" cols="120" rows="5"><?=$PurposeDescription?></textarea><br />
</td></tr>
<tr><td colspan="2">
<p class="center"><input type="submit" value="save this message"></p>
</td></tr>

<p/>
</table>
</form>
<?php
}
else { // In this case we are updating an existing message
	$bgcolor[0]="#ffffcc";
	$bgcolor[1]="#ffff99" ;
	for ($ii=0;$ii<count($ListMessage);$ii++) {
	?>
	<table bgcolor="<?=$bgcolor[$ii%2]?>">
	<form name="updatetranslation" action="contactlocal/updatetranslation"  id="idupdatetranslation" method="post">
	<!-- The following will disable the nasty PPostHandler -->
	<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

	<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
	<input type="hidden" name="IdMess"  value="<?=$IdMess?>"/>
	<input type="hidden" name="IdLanguage"  value="<?=$ListMessage[$ii]->IdLanguage?>"/>
	<tr><td>
	Title : <input type="text" size="100" name="IdTitleText" value="<?=$ListTitle[$ii]->Sentence?>">
	</td>
	<tr><td>
	Message : <textarea cols="120" rows="5" name="IdMessageText"><?=$ListMessage[$ii]->Sentence ?></textarea>
	</td>
	<tr><td align="center">
	<input type="submit" size="100" value="Update This Message in <?=$ListMessage[$ii]->EnglishName?>">

	</form>
	<form name="deletetranslation" action="contactlocal/deletetranslation"  id="iddeletetranslation" method="post">
	<!-- The following will disable the nasty PPostHandler -->
	<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

	<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
	<input type="hidden" name="IdMess"  value="<?=$IdMess?>"/>
	<input type="hidden" name="IdLanguage"  value="<?=$ListMessage[$ii]->IdLanguage?>"/>
	<tr><td align="center">
	<input type="submit" size="100" value="delete this <?=$ListMessage[$ii]->EnglishName?> translation"  onclick="return confirm('are you sure ?')">
	</td>
	</form>
	</table>
	<?php
	} // end for $ii
	?>
	<table bgcolor="<?=$bgcolor[$ii%2]?>">
	<form name="addtranslation" action="contactlocal/addtranslation"  id="idaddtranslation" method="post">
	<p>
	<!-- The following will disable the nasty PPostHandler -->
	<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

	<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
	<input type="hidden" name="IdMess"  value="<?=$IdMess?>"/>
	<tr><td>
	Title : <input type="text" size="100" name="IdTitleText" value="">
	</td>
	<tr><td>
	Message : <textarea cols="120" rows="5" name="IdMessageText"></textarea>
	</td>
	<tr><td align="center">
	Language: <select  name="IdLanguage">
	<?php
    foreach ($PossibleLanguages as $ll) {
		$AlreadyExist=false ;
		for ($ii=0;$ii<count($ListMessage);$ii++) { // First check that the translation is not already available
			if ($ListMessage[$ii]->IdLanguage==$ll->IdLanguage) {
				$AlreadyExist=true ;
			}
		}
		if ($AlreadyExist) continue ;
		echo "<option value=\"".$ll->IdLanguage."\" " ;
		echo ">",$ll->LanguageName,"</option>\n" ;
	}
	?>
	<input type="submit" size="100" value="Add Translation">
	</td>
	</form>
	</table>

<?php
	foreach ($ChosenLocations as $loc) {
?>
	<form name="dellocation" action="contactlocal/dellocation"  id="iddellocation" method="post">
	<p>
	
	<!-- The following will disable the nasty PPostHandler -->
	<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
	

	<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
	<input type="hidden" name="IdMess"  value="<?=$IdMess?>"/>
	<?=$loc->Choice?>
	<input type="hidden" name="IdLocation" value="<?=$loc->id?>">
	<input type="submit" value="Del location" onclick="return confirm('are you sure ?')">
	</form>
<?php
	}
?>
	

	<?php
	if (count($PossibleLocations)>0) {
	?>
	<form name="addlocation" action="contactlocal/addlocation"  id="idaddlocation" method="post">
	<p>
	Add a Location :
	<!-- The following will disable the nasty PPostHandler -->
	<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

	<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
	<input type="hidden" name="IdMess"  value="<?=$IdMess?>"/>
	<select  name="IdLocation">
	<?php
		foreach ($PossibleLocations as $loc) {
			echo "<option value=\"".$loc->id."\" " ;
			if ( isset($IdLocation) and ($IdLocation==$loc->id)) {
				echo " selected" ;
			}
			echo ">",$loc->Choice,"</option>\n" ;
		}
	?>
	</select> 
	<input type="submit" value="Add the location">
	<?php
	} // end if (count($PossibleLocations)>0) 
	?>
	</form>
<?php
} // end if (!empty($IdMess)) 
?>


