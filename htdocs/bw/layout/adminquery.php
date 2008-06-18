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


require_once ("menus.php");

// This form displays the list of the possible user for a specific query 
function DisplayUsers($rQuery,$TResult,$Message="") {

  global $title;
  if (isset($rQuery->Name)) { // If the query was successfull and if it has a name
  	  $title=$rQuery->Name ;
  }
  else {
  		$title = "FailedQuery";
  }
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminquery.php", ww('MainPage')); // Displays the second menu

  if (HasRight("SqlForVolunteers") >= 1) {
      $MenuAction  = "            <li><a href=\"http://www.bevolunteer.org/wiki/Queries_for_volunteers\">wiki explanations</a></li>\n";
  }
//  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";

  DisplayHeaderShortUserContent("See user who can execute the query #".$rQuery->id);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info\">\n";

  if (!empty($Message)) {
    echo "<h2>$Message</h2>";
  }

  $bgcolor[0]="#ffffcc" ;
  $bgcolor[1]="#ffccff" ;

  echo "<center><p><table>\n" ;
  $max=count($TResult) ;
  if ($max>0) {
  	  echo "<tr bgcolor=\"#ff9966\">" ;
  	  echo "<th colspan=3>" ;
  	  echo "Users able to execute : ",$rQuery->Name ;
  	  echo "</th>" ;
  	  echo "<tr bgcolor=\"#ff9966\">" ;
  	  echo "<td colspan=3>" ;
  	  echo $rQuery->Query ;
  	  echo "</td>" ;
  	  echo "</tr>" ;
  	  echo "<tr bgcolor=\"#ff9966\" align=\"left\">" ;
  	  echo "<th>Username</th><th>Scope</th><th>action</th>" ;
  	  echo "</tr>" ;
	  
  	  for ($ii=0;$ii<$max;$ii++) {
		echo "<tr align=left valign=center bgcolor=\"".  $bgcolor[$ii%2]."\">" ;
		$rr=$TResult[$ii] ;
		echo "<td>",LinkWithUsername($rr->Username),"</td>" ;
		echo "<td>",$rr->Scope,"</td>" ;
		echo "<td>" ;
	  	if (HasRight("Rights","SqlForVolunteers")) {
		   echo "<form  method=\"post\" action=\"adminquery.php\"><input type=\"hidden\" value=\"".$rr->IdMember."\" name=IdMember><input type=hidden value=\"".$rQuery->id."\" name=IdQuery><input type=submit name=\"action\" value=\"remove access\"></form>" ;
		}
		echo "</td>" ;
		echo "</tr>" ;
	  } // end of for $ii
	  echo "</table></p>\n" ;
	  if (HasRight("Rights","SqlForVolunteers")) {
	  	 echo "<br /><p>" ;
	  	 echo "<table>" ;
  	  	 echo "<tr bgcolor=\"#ff9966\">" ;
		 echo "<td> Grant this query to a new user</td>" ; 
		 echo "<form  method=\"post\" action=\"adminquery.php\"><tr><td>Username <input type=text name=\"Username\"></td></tr>" ;
	  	 echo "<tr><td align=center><input type=\"hidden\" value=\"".$rQuery->id."\" name=IdQuery><br><input type=submit name=\"action\" value=\"grant query\"></td></tr></form></table>" ;
	  	 echo "</p>\n" ;
	  }
	}
	else {
		 echo "<p>Nobody has right for this</p>\n" ;
	}

  echo "</center>";
  require_once "footer.php";
} // end of DisplayUsers

// This form displays the list of the possible queries for the current user 
function DisplayMyQueryList($TList,$Message="") {

  global $title;
  $title = "Queries for BW volunteers";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminquery.php", ww('MainPage')); // Displays the second menu

  if (HasRight("SqlForVolunteers") >= 10) {
      $MenuAction  = "            <li><a href=\"http://www.bevolunteer.org/wiki/Queries_for_volunteers\">wiki explanations</a></li>\n";
  }
//  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info\">\n";

  if (!empty($Message)) {
    echo "<h2>$Message</h2>";
  }

  $bgcolor[0]="#ffffcc" ;
  $bgcolor[1]="#ffccff" ;
		echo "<p><table>\n" ;
		$max=count($TList) ;
		echo "<tr><th colspan=4>you have ",$max," possible queries</th></tr>\n" ;
		
		echo "<tr align=left bgcolor=\"#ff9966\"><th>Query</th><th>param1</th><th>param2</th><th>action</th></tr>\n" ;
		for ($ii=0;$ii<$max;$ii++) {
				$rr=$TList[$ii] ;
				echo "<form method=\"post\" action=\"adminquery.php\">" ;
			    echo "<input type=\"hidden\" name=\"IdQuery\" value=\"".$rr->id."\">" ;
				echo "<tr align=left valign=center bgcolor=\"".  $bgcolor[$ii%2]."\">" ;
				echo "<td>",$rr->Name,"</td>" ;

				if (!empty($rr->param1)) {
					 	 echo "<td>" ;
					 	 echo $rr->param1,":" ;
						 echo "<input type=\"texte\" name=\"param1\">" ;
						 echo "</td>" ;
				}
				else {
					 	 echo "<td bgcolor=gray>" ;
						 echo "-<input type=\"hidden\" name=\"param1\">" ;
						 echo "</td>" ;
				}

				if (!empty($rr->param2)) {
					 	 echo "<td>" ;
					 	 echo $rr->param2,":" ;
						 echo "-<input type=\"texte\" name=\"param2\">" ;
						 echo "</td>" ;
				}
				else {
					 	 echo "<td bgcolor=gray>" ;
						 echo "-<input type=\"hidden\" name=\"param2\">" ;
						 echo "</td>" ;
				}
				
				echo "<td>" ;
				echo "<input type=\"submit\" name=\"action\" value=\"execute\">" ;
				if (HasRight("Rights","SqlForVolunteers")) {
					 echo " <input type=\"submit\" name=\"action\" value=\"See Users\">" ;
				}
				echo "</td>" ;
				echo "</tr>\n" ;
				echo "</form>" ;
		}
		echo "</table></p>\n" ;

  echo "</center>";
  require_once "footer.php";

} // end of DisplayMyQueryList

// This form displays the results of the possible queries for the current user 
function DisplayMyResults($TResult,$TTitle,$rQuery,$Message) {

  global $title;
  if (isset($rQuery->Name)) { // If the query was successfull and if it has a name
  	  $title=$rQuery->Name ;
  }
  else {
  		$title = "FailedQuery";
  }
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminquery.php", ww('MainPage')); // Displays the second menu

  if (HasRight("SqlForVolunteers") >= 10) {
      $MenuAction  = "            <li><a href=\"http://www.bevolunteer.org/wiki/Queries_for_volunteers\">wiki explanations</a></li>\n";
  }
//  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info\">\n";

  if (!empty($Message)) {
    echo "<h2>$Message</h2>";
  }
  
  $iCount=count($TTitle) ;

  $bgcolor[0]="#ffffcc" ;
  $bgcolor[1]="#ffccff" ;

		echo "<p><table>\n" ;
		$max=count($TResult) ;
		echo "<tr bgcolor=\"#ff9966\">" ;
		for ($ii=0;$ii<$iCount;$ii++) {
			echo "<th>",$TTitle[$ii],"</th>" ;
		}
		echo "</tr>" ;
		for ($jj=0;$jj<$max;$jj++) {
			$rr=$TResult[$jj] ;
			echo "<tr align=left valign=center bgcolor=\"".  $bgcolor[$jj%2]."\">" ;
			for ($ii=0;$ii<$iCount;$ii++) {
				$FieldName=$TTitle[$ii] ;
				echo "<td>",$rr[$ii],"</td>" ;
			}
			echo "</tr>" ;
		}
		echo "</table></p>\n" ;

  echo "</center>";
  require_once "footer.php";

} // end of DisplayMyResults

