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


JeanYves notes : This is what is used to manage the report to moderators

*/
$words = new MOD_words();

?>
<h2>Report to moderator</h2>

<?php
if (!empty($DataPost->Error)) {
    echo "<h2 style=\"color:#ff0033;\" >",$DataPost->Error,"</h2>" ;
}

$request = PRequest::get()->request;
$uri = implode('/', $request);

?>

<table bgcolor="lightgray" align="left" border="3">
<?
if (isset($DataPost->Thread->title))
?>
        <form method="post" action="forums/modeditpost/<?=$DataPost->Post->id;?>" id="modpostforum">
        <input type="hidden" name="<?=$callbackId;?>"  value="1" />
        <input type="hidden" name="IdThread"  value="<?=$DataPost->Thread->id;?>" /><br />
        <input type="hidden" name="IdPost"  value="<?=$DataPost->Post->id;?>"/>
<?
		echo "<tr><td>" ;
		if (isset($DataPost->UserNameStarter)) echo "thread started by member ".$DataPost->UserNameStarter;
		echo "</td>" ;
		echo "<td>post  by member <a href=\"bw/member.php?cid=".$DataPost->Post->UserNamePoster,"\">".$DataPost->Post->UserNamePoster."</a></td><td><a href=\"forums/s<",$DataPost->Thread->id,"/#",$DataPost->Report->IdPost,"\">go to post</a></td>" ;
		echo "</tr>" ;
		echo "<tr><td colspan=\"3\">",$DataPost->Thread->Title[0]->Sentence,"</td></tr>" ;
		echo "<tr><td colspan=\"3\" >",$DataPost->Post->Content[0]->Sentence,"</td></tr>" ;
		if (isset($DataPost->Report->PostComment))  {
			echo "<tr><td colspan=\"3\" bgcolor=\"#FFFFFF\">",$DataPost->Report->PostComment,"</td></tr>" ;
			$PostComment=$DataPost->Report->PostComment ;
		}
		echo "<tr><td colspan=\"3\"><textarea name='PostComment' cols=80 rows=8></textarea>",$DataPost->Post->Content[0]->Sentence,"</td></tr>" ;
		echo "<tr><td colspan=\"1\">" ;
		echo "Status <select Name='Status'>" ;
		if (isset($DataPost->Report->Status)) $Status=$DataPost->Report->Status ; else $Status="" ;
		echo "<option value='Open'" ;
		if ($Status=='Open') echo " selected" ;
		echo ">Open</option>" ;
		echo "<option value='OnDiscussion'" ;
		if ($Status=='OnDiscussion') echo " selected" ;
		echo ">OnDiscussion</option>" ;
		echo "<option value='Closed'" ;
		if ($Status=='Closed') echo " selected" ;
		echo ">Closed</option>" ;
		echo "</select></td>" ;
		$IdReporter=0 ;
		if (isset($DataPost->Report->IdReporter)) $IdReporter=$DataPost->Report->IdReporter ; 
		echo "<input type='hidden' name='IdReporter' value='".$IdReporter."'>" ;
		if ($this->BW_Right->HasRight("ForumModerator")) {
			echo "<td colspan=\"1\">" ;
			echo "Type <select Name='Type'>" ;
			if (isset($DataPost->Report->Type)) $Type=$DataPost->Report->Type ; else $Type="" ;
			echo "<option value='SeeText'" ;
			if ($Status=='SeeText') echo " selected" ;
			echo ">SeeText</option>" ;
			echo "<option value='AllowMeToEdit'" ;
			if ($Status=='AllowMeToEdit') echo " selected" ;
			echo ">AllowMeToEdit</option>" ;
			echo "<option value='Insults'" ;
			if ($Status=='Insults') echo " selected" ;
			echo ">Insults</option>" ;
			echo "<option value='RemoveMyPost'" ;
			if ($Status=='RemoveMyPost') echo " selected" ;
			echo ">RemoveMyPost</option>" ;
			echo "<option value='Others'" ;
			if ($Status=='Others') echo " selected" ;
			echo ">Others</option>" ;
			echo "</select></td>" ;
			echo "<td colspan=\"1\"></td></tr>" ;
		}
		else {
			echo "<td></td>" ;
		}
		echo "</tr>" ;
		

echo "<th valign=center align=center colspan=3><input type=\"submit\" name=\"submit\" value=\"add to report\"></th>" ;


echo "</form>" ;
echo "</table>" ;
?>

