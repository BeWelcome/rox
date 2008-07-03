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

	// Prepare the $MenuAction for ShowAction()  
    if (isset($_SESSION["IdMember"]) && $_SESSION["IdMember"] == $IdMember) {
        echo "<li><a href=\"mypreferences.php?cid=" . $IdMember . "\">" . $words->getFormatted("MyPreferences") . "</a></li>\n";
        echo "<li><a href=\"editmyprofile.php\">" . $words->getFormatted("EditMyProfile") . "</a></li>\n";        
    }
    else {
        echo "          <li class=\"icon contactmember16\"><a href=\"contactmember.php?cid=" . $IdMember . "\">" . $words->getFormatted("ContactMember") . "</a></li>\n";
        echo "          <li class=\"icon addcomment16\"><a href=\"addcomments.php?cid=" . $IdMember . "\">" . $words->getFormatted("addcomments") . "</a></li>\n";
        if (MOD_layoutbits::GetPreference("PreferenceAdvanced")=="Yes") {
            if ($m->IdContact==0) {
       	        echo "          <li class=\"icon mylist16\"><a href=\"mycontacts.php?IdContact=" . $IdMember . "&amp;action=add\">".$words->getFormatted("AddToMyNotes")."</a> </li>\n";
            }
            else {
                echo "          <li class=\"icon mylist16\"><a href=\"mycontacts.php?IdContact=" . $IdMember . "&amp;action=view\">".$words->getFormatted("ViewMyNotesForThisMember")."</a> </li>\n";
            }
        }
        if (MOD_layoutbits::GetPreference("PreferenceAdvanced")=="Yes") {
            if ($m->IdRelation==0) {
                echo "        <li class=\"icon myrelations16\"><a href=\"myrelations.php?IdRelation=" . $IdMember . "&amp;action=add\">".$words->getFormatted("AddToMyRelations")."</a> </li>\n";
            }
            else {
                echo "        <li class=\"icon myrelations16\"><a href=\"myrelations.php?IdRelation=" . $IdMember . "&amp;action=view\">".$words->getFormatted("ViewMyRelationForThisMember")."</a> </li>\n";
            }
        }
    }
	if ((APP_User::LoggedIn()) and ($m->NbForumPosts>0)) { // the number of post will only be displayer for logged member
	   echo "          <li class=\"icon forumpost16\"><a href=\"".PVars::getObj('env')->baseuri."forums/member/".$m->Username."\">".$words->getFormatted("ViewForumPosts",$m->NbForumPosts)."</a></li>\n";
	}

	if ($CanBeEdited) {
		echo "          <li><a href=\"editmyprofile.php?cid=" . $IdMember . "\">".$words->getFormatted("TranslateProfileIn",LanguageName($_SESSION["IdLanguage"]))." ".FlagLanguage(-1,$title="Translate this profile")."</a> </li>\n";
	}
    
?>