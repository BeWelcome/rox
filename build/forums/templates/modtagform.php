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

$words = new MOD_words();

?>
<h2>Editing Tag # 
<?php
echo $DataTag->IdTag ;
if (isset($DataTag->Tag->tag)) echo " [".$DataTag->Tag->tag."]" ;
?>
</h2>
<p>
<?php
if (!empty($DataTag->Error)) {
	echo "<h2 style=\"color:#ff0033;\" >",$DataTag->Error,"</h2>" ;
}

$request = PRequest::get()->request;
$uri = implode('/', $request);


echo "<table bgcolor=lightgray align=left>" ;
echo "<tr bgcolor=#ccffff><th> <a href=\"forums/t".$DataTag->Tag->id."\">go to tag</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"forums\">forum main page</a></th><th colspan=2> " ;
echo " This tag is used by ",$DataTag->NbThread," thread(s)</th>" ;

// print_r($DataTag) ;
// Display the various content for this tag in various languages
$max=count($DataTag->Names) ;
echo "<tr bgcolor=#663300 ><td colspan=3></td></tr>" ;
if (isset($DataTag->Tag->description)) echo "<tr><td>tag (old TB way)</td><td colspan=2>" ,$DataTag->Tag->description,"</i></td>" ;
echo "<tr><th colspan=3  align=left>Content of tag ($max translations)</th>" ;
foreach ($DataTag->Names as $Content) {
	
	echo "<form method=\"post\" action=\"forums/modedittag/".$Content->id."\" id=\"modtagforum\">" ;
	echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
	echo "<input type=\"hidden\" name=\"IdTag\"  value=\"".$DataTag->IdTag."\"/>" ;
	$ArrayLanguage=$this->_model->LanguageChoices($Content->IdLanguage) ;
	echo "<tr><td>" ;
	echo "<select Name=\"IdLanguage\">" ;
//	echo "<option value=\"-1\">-</option>" ;
	
	foreach ($ArrayLanguage as $Choices) {
			echo "<option value=\"",$Choices->IdLanguage,"\"" ;
			if ($Choices->IdLanguage==$Content->IdLanguage) echo " selected ";
			echo "\">",$Choices->EnglishName,"</option>" ;
	}
	echo "</select>\n" ;

	
	echo "</td><td><textarea name=\"SentenceTag\" cols=\"80\" rows=\"5\">",$Content->Sentence,"</textarea>\n" ;
	echo "<input id=\"IdForumTrads\" type=\"hidden\" name=\"IdForumTradsTag\" value=\"".$Content->IdForumTrads."\"></td>" ;
	echo "<td><input type=\"submit\" name=\"submit\"  value=\"update\"><br /><input type=\"submit\" name=\"submit\"  value=\"delete\"></td>" ;
	echo "</form>\n" ;
}
// Display the various description for this tag in various languages
$max=count($DataTag->Descriptions) ;
echo "<tr bgcolor=#663300 ><td colspan=3></td></tr>" ;
echo "<tr><th colspan=3  align=left>Content of descriptions ($max translations)</th>" ;
foreach ($DataTag->Descriptions as $Content) {
	if (empty($Content->IdLanguage)) {
		$Content->IdLanguage=0 ; // force to english
	}
	echo "<form method=\"post\" action=\"forums/modedittag/".$Content->id."\" id=\"modtagforum\">" ;
	echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
	echo "<input type=\"hidden\" name=\"IdTag\"  value=\"".$DataTag->IdTag."\"/>" ;
	$ArrayLanguage=$this->_model->LanguageChoices($Content->IdLanguage) ;
	echo "<tr><td>" ;
	echo "<select Name=\"IdLanguage\">" ;
//	echo "<option value=\"-1\">-</option>" ;
	
	foreach ($ArrayLanguage as $Choices) {
			echo "<option value=\"",$Choices->IdLanguage,"\"" ;
			if ($Choices->IdLanguage==$Content->IdLanguage) echo " selected ";
			echo "\">",$Choices->EnglishName,"</option>" ;
	}
	echo "</select>\n" ;

	
	echo "</td><td><textarea name=\"SentenceDescription\" cols=\"80\" rows=\"5\">",$Content->Sentence,"</textarea>\n" ;
	echo "<input id=\"IdForumTradsDescription\" type=\"hidden\" name=\"IdForumTradsDescription\" value=\"".$Content->IdForumTrads."\">" ;
	echo "</td><td><input type=\"submit\" name=\"submit\"  value=\"update\"><br /><input type=\"submit\" name=\"submit\" value=\"delete\"></td>" ;
	echo "</form>\n" ;
}

// Now propose the to add a translation
echo "<tr bgcolor=#663300 ><td colspan=3></td></tr>" ;
echo "<form method=\"post\" action=\"forums/modedittag/".$Content->id."\" id=\"modtagforum\">" ;
echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
echo "<input type=\"hidden\" name=\"IdTag\"  value=\"".$DataTag->IdTag."\"/>" ;
echo "<input type=\"hidden\" name=\"IdName\"  value=\"".$DataTag->Tag->IdName."\"/>" ;
echo "<input type=\"hidden\" name=\"IdDescription\"  value=\"".$DataTag->Tag->IdDescription."\"/>" ;
if (!isset($Content->IdLanguage)) {
	die ("Bug in modtagform.php \$Content->IdLanguage is not set !") ;
}
$ArrayLanguage=$this->_model->LanguageChoices($Content->IdLanguage) ;
echo "<tr><td>" ;
echo "<select Name=\"NewIdLanguage\">" ;
//	echo "<option value=\"-1\">-</option>" ;
	
foreach ($ArrayLanguage as $Choices) {
			echo "<option value=\"",$Choices->IdLanguage,"\"" ;
			if ($Choices->IdLanguage==$Content->IdLanguage) echo " selected ";
			echo "\">",$Choices->EnglishName,"</option>" ;
}
echo "</select>\n" ;

	
echo "</td><td>Name <input type=\"text\" name=\"SentenceTag\"><br />Description<textarea name=\"SentenceDescription\" cols=\"80\" rows=\"5\"></textarea>\n</td><td><input type=\"submit\" name=\"submit\" value=\"add translation\"></td>" ;
echo "</form>\n" ;

// Now propose to replace another tag with this one
echo "<tr bgcolor=#663300 ><td colspan=3></td></tr>" ;
echo "<form method=\"post\" action=\"forums/modedittag/".$Content->id."\" id=\"modtagforum\">" ;
echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
echo "<input type=\"hidden\" name=\"IdTag\"  value=\"".$DataTag->IdTag."\"/>" ;
echo "<tr><td colspan=2>" ;
echo "USE CAREFULLY !<br />here you can enter the #id of a tag which will be deleted and will have all its entries in forum treads replaced by the current tag (<b>".$words->fTrad($DataTag->Tag->IdName)."</b>)" ;
echo "</td><td>numeric Id of the tag to delete and replace <input type=\"text\" name=\"IdTagToReplace\" size=3><br /><input type=\"submit\" name=\"submit\" value=\"replace tag\"></td>" ;
echo "</form>\n" ;

echo "</table>" ;
?>
</p>
