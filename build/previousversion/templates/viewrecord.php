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

$data=$this->_data ; // Retrieve the data to display (set by the controller)

$words = new MOD_words();
$styles = array( 'highlight', 'blank' ); // alternating background for table rows

if (!empty($data->sQuery)) {
	echo "<h3>",$data->sQuery,"</h3>" ;
}
?>

<form name="viewrecord" action="previousversion/viewrecord"  id="idviewrecord" method="post">
<p>
<!-- The following will disable the nasty PPostHandler -->
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>

<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
Id Record: <input type="text" name="IdRecord"  
<?php
	if (!empty($data->IdRecord)) echo " value=\"".$data->IdRecord."\"" ;
?>
/>
Id Member: <input type="text" name="IdMember" 
<?php
	if (!empty($data->IdMember)) echo " value=\"".$data->IdMember."\"" ;
?>
/>
Table name: <input type="text" name="TableName" 
<?php
	if (!empty($data->TableName)) echo " value=\"".$data->TableName."\"" ;
?>
/>
String to find: <input type="text" name="String" 
<?php
	if (!empty($data->String)) echo " value=\"".$data->String."\"" ;
?>
/>

<input type="submit" size="100" value="view">
</form>

<?php
if (isset($data->previousvalues)) {
	$count=count($data->previousvalues) ;
	echo "<br />",$count," values" ;
	echo "<table>" ;
	echo "<tr>" ;
	echo "<th>Field</th>" ;
	foreach ($data->previousvalues as $vv) {
		echo "<td>",$vv->created,"</td>" ;
	}
	echo "</tr>" ;
	$iStyle=0 ;
	foreach ($data->Fields as $Field) {
		$iStyle++ ;
		echo "<tr valign=\"top\" class=\"",$styles[$iStyle%2],"\">" ;
		echo "<td>",$Field->Name,"</td>" ;
		echo "<td>",$Field->Value,"</td>" ;
		echo "</tr>" ;
	}
	echo "</table>"  ;
}
?>