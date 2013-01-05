<?php
/*

This is the form which manage the MODERATOR FULL EDIT POST

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
<h2>Full Moderator Editing post #m
<?php
echo $DataPost->IdPost ;
echo " Thread #s" ;
echo $DataPost->Thread->id ;
?>
</h2>

<?php
if (!empty($DataPost->Error)) {
    echo "<h2 style=\"color:#ff0033;\" >",$DataPost->Error,"</h2>" ;
}

$request = PRequest::get()->request;
$uri = implode('/', $request);

?>

<table bgcolor="lightgray" align="left" border="3">"
<?
if (isset($DataPost->Thread->title))
?>
    <tr bgcolor="#ccffff">
        <th><a href="forums/s<?=$DataPost->Thread->id;?>">go to thread</a></th>
        <?       
        if (isset($DataPost->Thread->title));?>
        <th>TB oldway Title <i><?=$DataPost->Thread->title;?></i>
            <form method="post" action="forums/modeditpost/<?=$DataPost->Post->id;?>" id="modpostforum">
                <input type="hidden" name="<?=$callbackId;?>"  value="1" />

                stickyvalue (default 0, the most negative will be the first visible)
                <input type="text" name="stickyvalue" size="1" value="<?=$DataPost->Thread->stickyvalue;?>" /><br />

                expiration date (close the thread)
                <input type="text" name="expiredate" value="<?=$DataPost->Thread->expiredate;?>" /><br />

                Thread Visibility: 
                <select name="ThreadVisibility" >
				<option value="NoRestriction"
				<?php
				if ($DataPost->Thread->ThreadVisibility=="NoRestriction") {
					echo " selected" ;
				}
				?>
				>Everybody (including google)</option>
				<option value="MembersOnly"
				<?php
				if ($DataPost->Thread->ThreadVisibility=="MembersOnly") {
					echo " selected" ;
				}
				?>
				>BeWelcome Members only</option>
				<option value="GroupOnly"
				<?php
				if ($DataPost->Thread->ThreadVisibility=="GroupOnly") {
					echo " selected" ;
				}
				?>
				>Members of group</option>
				<option value="ModeratorOnly"
				<?php
				if ($DataPost->Thread->ThreadVisibility=="ModeratorOnly") {
					echo " selected" ;
				}
				?>
				>Moderators only</option>
				</select> 

                Group: <select name="IdGroup">
                    <option value="0"> no group</option>
                    <?
                    foreach ($DataPost->PossibleGroups as $Group) {
                        echo "<option value=\"".$Group->IdGroup."\"" ;
                        if ($Group->IdGroup==$DataPost->Thread->IdGroup) {
                            echo " selected" ;
                        }
                        echo ">",$Group->Name,"</option>\n" ;
                    };?>
                </select><br />
                Who can reply: 
                <select name="WhoCanReply" >
				<option value="MembersOnly"
				<?php
				if ($DataPost->Thread->WhoCanReply=="MembersOnly") {
					echo " selected" ;
				}
				?>
				>All members</option>
				<option value="GroupMembersOnly"
				<?php
				if ($DataPost->Thread->WhoCanReply=="GroupMembersOnly") {
					echo " selected" ;
				}
				?>
				>Group Members only</option>
				<option value="ModeratorOnly"
				<?php
				if ($DataPost->Thread->WhoCanReply=="ModeratorOnly") {
					echo " selected" ;
				}
				?>
				>Moderators only</option>
				</select> <br />
				
                Thread deleted: 
                <select name="ThreadDeleted">
				<option value="Deleted"
				<?php
				if ($DataPost->Thread->ThreadDeleted=="Deleted") {
					echo " selected" ;
				}
				?>
				>Deleted</option>
				<option value="NotDeleted"
				<?php
				if ($DataPost->Thread->ThreadDeleted=="NotDeleted") {
					echo " selected" ;
				}
				?>
				>Not Deleted</option>
				</select><br />


                <input type="hidden" name="IdThread"  value="<?=$DataPost->Thread->id;?>" /><br />
                <input type="hidden" name="IdPost"  value="<?=$DataPost->Post->id;?>"/>
            </th>
<?
echo "<th valign=center align=center><input type=\"submit\" name=\"submit\" value=\"update thread\"><br/>(thread id #s".$DataPost->Thread->id.")</th>" ;


echo "</form>" ;

echo "</th>" ;

if (isset($DataPost->UserNameStarter)) echo "<tr><td colspan=3>thread started by member ".$DataPost->UserNameStarter,"</td>" ;
echo "<tr><td colspan=3>post  by member <a href=\"/members/".$DataPost->Post->UserNamePoster,"\">".$DataPost->Post->UserNamePoster."</a> [".$DataPost->Post->memberstatus."]</td>" ;

// Display the various title for this post in various languages
$max=count($DataPost->Thread->Title) ;
echo "<tr><th colspan=3 align=left>Title of thread ($max translations)</th>" ;
foreach ($DataPost->Thread->Title as $Title) {
    echo "<form method=\"post\" action=\"forums/modeditpost/".$DataPost->Post->id."\" id=\"modpostforum\">" ;
    echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
    echo "<input type=\"hidden\" name=\"IdPost\"  value=\"".$DataPost->Post->id."\"/>" ;
    $ArrayLanguage=$this->_model->LanguageChoices($Title->IdLanguage) ;
    echo "<tr><td>" ;
    echo "<select Name=\"IdLanguage\">" ;
//  echo "<option value=\"-1\">-</option>" ;

    foreach ($ArrayLanguage as $Choices) {
            echo "<option value=\"",$Choices->IdLanguage,"\"" ;
            if ($Choices->IdLanguage==$Title->IdLanguage) echo " selected ";
            echo "\">",$Choices->EnglishName,"</option>" ;
    }
    echo "</select>" ;
    echo "</td><td><textarea class=\"long\" name=\"Sentence\" cols=\"60\" rows=\"5\">",$Title->Sentence,"</textarea><input type=\"hidden\" name=\"IdForumTrads\" value=\"".$Title->IdForumTrads."\"></td><td><input type=\"submit\" value=\"update\"></td>" ;
    echo "</form>" ;
}

// Display a subform to allow to insert a new translation for the title
echo "<tr><th colspan=3 align=left>Title of thread ($max translations)</th>" ;
    echo "<form method=\"post\" action=\"forums/modeditpost/".$DataPost->Post->id."\" id=\"modpostforum\">" ;
    echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
    echo "<input type=\"hidden\" name=\"IdPost\"  value=\"".$DataPost->Post->id."\"/>" ;
    echo "<input type=\"hidden\" name=\"IdThread\"  value=\"".$DataPost->Thread->id."\"/><br />" ;
    echo "<input type=\"hidden\" name=\"IdTrad\"  value=\"".$DataPost->Thread->IdTitle."\"/><br />" ;

    $ArrayLanguage=$this->_model->LanguageChoices(0) ;
    echo "<tr><td>" ;
    echo "<select Name=\"IdLanguage\">" ;
//  echo "<option value=\"-1\">-</option>" ;

    foreach ($ArrayLanguage as $Choices) {
            echo "<option value=\"",$Choices->IdLanguage,"\"" ;
            echo "\">",$Choices->EnglishName,"</option>" ;
    }
    echo "</select>" ;
    echo "</td><td>New Title<br /><textarea class=\"long\" name=\"NewTranslatedTitle\" cols=\"60\" rows=\"5\"></textarea>" ;
    echo "<td><input type=\"submit\" name=\"submit\" value=\"add translated title\"></td>" ;
    echo "</form>" ;

// Display the main properties for this post (and allow to change them)
$max=count($DataPost->Post->Content) ;
echo "<tr bgcolor=#663300 ><td colspan=3></td></tr>" ;
if (isset($DataPost->Post->message)) echo "<tr><td>message (old TB way)</td><td colspan=2>" ,$DataPost->Post->message,"</i></td>" ;

    echo "<tr>" ;
    echo "<form method=\"post\" action=\"forums/modeditpost/".$DataPost->Post->id."\" id=\"modpostforum\">" ;
    echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
    echo "<input type=\"hidden\" name=\"IdPost\"  value=\"".$DataPost->Post->id."\"/>" ;
    echo "<td align=left colspan=2>Can Owner edit: \n<select type=\"text\" name=\"OwnerCanStillEdit\">" ;
    echo "<option value=\"Yes\"" ;
    if ($DataPost->Post->OwnerCanStillEdit=="Yes") echo " selected" ;
    echo ">Yes</option>" ;
    echo "<option value=\"No\"" ;
    if ($DataPost->Post->OwnerCanStillEdit=="No") echo " selected" ;
    echo ">No</option>" ;
    echo "</select>&nbsp;&nbsp;" ;
	
	echo " Has Votes: \n<select type=\"text\"  name=\"HasVotes\">\n" ;
    echo "<option value=\"Yes\"" ;
	if ($DataPost->Post->HasVotes=="Yes") {
		echo " \"selected\"" ;
	}
	echo ">Yes</Option>" ;				
    echo "<option value=\"No\"" ;
	if ($DataPost->Post->HasVotes=="No") {
		echo " selected" ;
	}
	echo ">No</Option>" ;				
    echo "</select> <br />" ;

	?>
                Post Visibility: 
                <select name="PostVisibility" >
				<option value="NoRestriction"
				<?php
				if ($DataPost->Post->PostVisibility=="NoRestriction") {
					echo " selected" ;
				}
				?>
				>Everybody (including google)</option>
				<option value="MembersOnly"
				<?php
				if ($DataPost->Post->PostVisibility=="MembersOnly") {
					echo " selected" ;
				}
				?>
				>BeWelcome Members only</option>
				<option value="GroupOnly"
				<?php
				if ($DataPost->Post->PostVisibility=="GroupOnly") {
					echo " selected" ;
				}
				?>
				>Members of group</option>
				<option value="ModeratorOnly"
				<?php
				if ($DataPost->Post->PostVisibility=="ModeratorOnly") {
					echo " selected" ;
				}
				?>
				>Moderators only</option>
				</select>  
				
                Post deleted: 
                <select name="PostDeleted">
				<option value="Deleted"
				<?php
				if ($DataPost->Post->PostDeleted=="Deleted") {
					echo " selected" ;
				}
				?>
				>Deleted</option>
				<option value="NotDeleted"
				<?php
				if ($DataPost->Post->PostDeleted=="NotDeleted") {
					echo " selected" ;
				}
				?>
				>Not Deleted</option>
				</select><br />
	<?php
	echo "</td>"  ;
    echo "<td><input name=\"submit\" type=\"submit\" value=\"update post\"></td>" ;
    echo "</form>\n" ;



// Display the various content for this post in various languages
echo "<tr><th colspan=3  align=left>Content of post ($max translations) beware of html inside !</th>" ;
foreach ($DataPost->Post->Content as $Content) {

    echo "<form method=\"post\" action=\"forums/modeditpost/".$DataPost->Post->id."\" id=\"modpostforum\">" ;
    echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
    echo "<input type=\"hidden\" name=\"IdPost\"  value=\"".$DataPost->Post->id."\"/>" ;
    $ArrayLanguage=$this->_model->LanguageChoices($Content->IdLanguage) ;


    echo "<tr><td>" ;
    echo "<select Name=\"IdLanguage\">" ;
//  echo "<option value=\"-1\">-</option>" ;

    foreach ($ArrayLanguage as $Choices) {
            echo "<option value=\"",$Choices->IdLanguage,"\"" ;
            if ($Choices->IdLanguage==$Content->IdLanguage) echo " selected ";
            echo "\">",$Choices->EnglishName,"</option>" ;
    }
    echo "</select>\n" ;


    echo "</td><td><textarea class=\"long\" name=\"Sentence\" cols=\"60\" rows=\"5\">",$Content->Sentence,"</textarea>\n<input id=\"IdForumTrads\" type=\"hidden\" name=\"IdForumTrads\" value=\"".$Content->IdForumTrads."\"></td><td><input type=\"submit\" value=\"update\"></td>" ;
    echo "</form>\n" ;
}


// Display the form to propose to create a new translation for the post

    echo "<form method=\"post\" action=\"forums/modeditpost/".$DataPost->Post->id."\" id=\"modpostforum\">" ;
    echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
    echo "<input type=\"hidden\" name=\"IdPost\"  value=\"".$DataPost->Post->id."\"/>" ;
    echo "<input type=\"hidden\" name=\"IdTrad\"  value=\"".$DataPost->Post->IdContent."\"/><br />" ;
    $ArrayLanguage=$this->_model->LanguageChoices() ;


    echo "<tr><td>" ;
    echo "<select Name=\"IdLanguage\">" ;
//  echo "<option value=\"-1\">-</option>" ;

    foreach ($ArrayLanguage as $Choices) {
            echo "<option value=\"",$Choices->IdLanguage,"\"" ;
            echo "\">",$Choices->EnglishName,"</option>" ;
    }
    echo "</select>\n" ;


    echo "</td><td>new translation<br /><textarea class=\"long\" name=\"NewTranslatedPost\" cols=\"60\" rows=\"5\"></textarea>\n</td><td><input type=\"submit\" value=\"add translated post\" name=\"submit\"></td>" ;
echo "</form>" ;



$max=count($DataPost->Tags) ;
echo "<tr bgcolor=\"#ffcc99\"><th colspan=\"3\"  align=left>Used tags (".$max.")</th></tr>" ;


echo "<form method=\"post\" action=\"forums/modeditpost/".$DataPost->Post->id."\" id=\"modpostforum\">" ;
echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;

foreach ($DataPost->Tags as $Tag) {

    echo "<tr bgcolor=\"#ffcc99\">" ;
    echo "<form method=\"post\" action=\"forums/modeditpost/".$DataPost->Post->id."\" id=\"modpostforum\">" ;
    echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
    echo "<input type=\"hidden\" name=\"IdThread\"  value=\"".$DataPost->Thread->id."\"/>" ;
    echo "<input type=\"hidden\" name=\"IdPost\"  value=\"".$DataPost->Post->id."\"/>" ;
    echo "<input type=\"hidden\" name=\"IdTag\"  value=\"".$Tag->IdTag."\"/>" ;
    echo "<td><a href=\"forums/t".$Tag->IdTag."-".$words->fTrad($Tag->IdName)."\">",$words->fTrad($Tag->IdName),"</a></td>" ;
    echo "<td align=\"left\"><input type=\"submit\"  Name=\"submit\" value=\"delete Tag\"></td>" ;
    echo "<td><a href=\"forums/modedittag/".$Tag->IdTag."\">edit tag #t".$Tag->IdTag."</a></td>" ;
    echo "</form>\n" ;
    echo "</tr>\n" ;
}

echo "<tr bgcolor=\"#ffcc99\"><td>Select a Tag</td>"  ;
echo "<form method=\"post\" action=\"forums/modeditpost/".$DataPost->Post->id."\" id=\"modpostforum\">" ;
echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
echo "<input type=\"hidden\" name=\"IdThread\"  value=\"".$DataPost->Thread->id."\"/><br />" ;
echo "<input type=\"hidden\" name=\"IdPost\"  value=\"".$DataPost->Post->id."\"/>" ;
echo "<td>" ;
echo "<select Name=\"IdTag\">" ;
echo "<option value=\"0\">Choose a Tag to add</option>" ;
foreach ($DataPost->AllNoneTags as $Tag) {
    echo "<option value=\"".$Tag->IdTag."\">",$words->fTrad($Tag->IdName)."(".$Tag->cnt.")","</option>\n" ;
}

echo "</select>" ;
echo "</td>" ;
echo "<td><input type=\"submit\" Name=\"submit\" value=\"Add Tag\"></td>" ;
echo "</form>\n" ;

echo "</table>" ;
?>

