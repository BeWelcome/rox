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

/*
This file is to be include when a volunteer board is needed somewhere
the volunteer board must be created manually using phpmyadmin

*/

// This retrieve and display in a text area the current content of BoardName
function DisplayVolunteer_Board($BoardName) {
	$str="select * from volunteer_boards where Name='".$BoardName."'" ;
	$qry=mysql_query($str) ;
	if (!$qry) {
	   die ("failing in DisplayVolunteer_Board(\$".$BoardName.")") ;
	}
	else {
		 $rr=mysql_fetch_object($qry) ;
		 if (!isset($rr->Name)) {
	   	 	die ("failing in DisplayVolunteer_Board(\$".$BoardName.") probably there is no such board !") ;
		 }
	}
	
	echo "\n<form name=\"Volunteer_board_".$BoardName."\" method=\"post\">\n" ;
	echo "<table  width=\"95%\" bgcolor=\"#ffffcc\">" ;
	echo "<tr><th align=\"center\">";
	echo $rr->PurposeComment,"</th></tr>\n" ;
	echo "<tr><td align=\"center\">";
	echo "<textarea name=\"content_".$BoardName."\" rows=\"5\" cols=\"100\" style=\"font-size:8pt;\">",$rr->TextContent,"</textarea>" ;
	echo "</td></tr>\n" ;
	echo "<tr><td align=\"center\">";
	echo "<input type=hidden name=\"action\" value=\"UpdateBoard_".$BoardName."\"><input type=\"submit\" name=\"Update Board\" value=\"Update Board\">" ;
	echo "</td></tr>\n" ;
	echo "</table>" ;
	echo "</form>\n" ;
	
} // end of DisplayVolunteer_Board
	

// This does the update of the board BoardName if needed
// it return true if the board was updated
function UpdateVolunteer_Board($BoardName) {
//die("here $BoardName") ;


	if ((isset($_POST["action"])) and ($_POST["action"]=="UpdateBoard_".$BoardName)) {
	
//	   $TextContent="updated by ".fUsername($_SESSION["IdMember"])." on  ".date("l jS \of F Y h:i:s A")." (server time)\n :".$_POST["content_".$BoardName] ;
	   $TextContent=date("Y/n/j H:i ").fUsername($_SESSION["IdMember"])." said:".$_POST["content_".$BoardName] ;
	
	   $str="select * from volunteer_boards where Name='".$BoardName."'" ;
	   $qry=mysql_query($str) ;
	   $rr=mysql_fetch_object($qry) ;
	   LogStr("Previous content for board <b>".$BoardName."</b><br>".$TextContent,"Updating Board") ;
	   
	   $str="update volunteer_boards set TextContent='".mysql_escape_string($TextContent)."' where Name='".$BoardName."'" ;
//	   echo "str=$str" ;
	   mysql_query($str) ;
	   return(true) ;
	 }
	 else {
	   return(false) ;
	 }
} // end of UpdateVolunteer_Board

?>
