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

<form name="preparenewmessage" action="contactlocal/recordnewmessage"  id="idpreparenewmessage" method="post">
<p>
<!-- The following will disable the nasty PPostHandler -->
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
Location :
<?php
if (count($PossibleLocations)>0) {
?>
<select  name="Location">
</select><br />
Title of message <input type="Text" name="Title"  value=""/><br />
Text of message <textarea name="MessageText" cols="120" rows="5"></textarea><br />
<select  name="language">
<?php
print_r(PossibleLocations) ;
    foreach ($PossibleLocations as $loc) {
		echo "<option value=\"".$loc->id."\" " ;
		if ( isset($IdLocation) and ($IdLocation==$loc->id)) {
			echo " selected" ;
		}
		echo ">",$loc->Choice,"</option>\n" ;
	}
?>
</select>
<?php
} // end if (count($PossibleLocations)>0) 
?>

<p/>
<p class="center"><input type="submit" value="save this message"></p>
</form>
