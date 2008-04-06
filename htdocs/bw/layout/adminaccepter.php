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

// this function returns the number of time a pending members has been renotified
// @ Username to consider
function CountNotify($Username) {
  global $_SYSHCVOL ;
  $str="select count(*) as cnt from ".$_SYSHCVOL['ARCH_DB'].".logs where Type='resendconfirmyourmail' and Str like '%<b>".$Username."</b>%'" ;
//  echo "str=$str" ;
  $rr=LoadRow($str) ;
  return($rr->cnt) ;
} // end of CountNotify


// This function allow return a displayable member Status of the member status according to its value
// @Status : the value of the Status
// @return some html allowing to see immediately the value of the status
//
function HighLightStatus($Status) {
    switch ($Status) {
        case "Active" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Lime><td><b>".$Status."</b></td></tr></table>" ;
             break ;

        case "ActiveHidden" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Lime><td><b><font color=white>".$Status."</font></b></td></tr></table>" ;
             break ;

        case "ChoiceInactive" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Lime><td><b><font color=Silver>".$Status."</font></b></td></tr></table>" ;
             break ;

        case "Renamed" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Lime><td><i><font color=Silver>".$Status."</font></i></td></tr></table>" ;
             break ;

        case "OutOfRemind" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=lightgray><td><i><font color=Silver>".$Status."</font></i></td></tr></table>" ;
             break ;

        case "Pending" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Cyan><td><b>".$Status."</b></td></tr></table>" ;
             break ;

        case "MailToConfirm" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Cyan><td><i>".$Status."</i></td></tr></table>" ;
             break ;

        case "NeedMore" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=lightgray><td><i>".$Status."</i></td></tr></table>" ;
             break ;

        case "Rejected" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Black><td><font color=white><strike>".$Status."</strike></font></td></tr></table>" ;
             break ;

        case "Banned" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Black><td><font color=red><strike>".$Status."</strike></font></td></tr></table>" ;
             break ;

        case "DuplicateSigned" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Gray><td><strike>".$Status."</strike></td></tr></table>" ;
             break ;

        case "PassedAway" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Black><td><font color=Gray> + ".$Status." +</font></td></tr></table>" ;
             break ;

        case "TakenOut" :
        case "AskToLeave" :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Black><td><font color=Silver><strike>".$Status."</strike></font></td></tr></table>" ;
             break ;

        default :
             $ss="<table style=\"Display:inline\"><tr bgcolor=Purple><td>?? ".$Status." ??</td></tr></table>" ;
             break ;
    }
    return($ss) ;
} // end of HighLightStatus

function ShowList($TData,$bgcolor="white",$title="") {
  global $global_count;
  $max = count($TData);
  $count = 0;

  if ($title!="") echo "              <p class=\"highlight\">\n",("$max"),$title," \n";
  for ($ii = 0; $ii < $max; $ii++) {
    $m = $TData[$ii];
    $count++;
    echo "              \n";
    $info_styles = array(0 => "          <div class=\"info\">\n", 1 => "          <div class=\" info highlight\">\n");
    echo $info_styles[($ii%2)];
    $LastLogin=fsince($m->created)." ".localdate($m->LastLogin) ;
    echo "             <input type=hidden name=IdMember_".$global_count." value=".$m->id.">\n";
    echo "             <p> <font size=5>",LinkWithUsername($m->Username,$m->Status),"</font> ".HighLightStatus($m->Status)." (",ww($m->Gender),")", " (Created:",fsince($m->created)," ",localdate($m->created)," - LastLogin:",$LastLogin,")</p>\n";
    echo "             <p> <font size=4>",$m->FirstName," <i>",$m->SecondName,"</i> <b>",$m->LastName,"</b> </font>(<a href=\"",bwlink("admin/adminaccepter.php?IdEmail="),$m->IdEmail,"\" title=\"see user with same email\">",$m->Email,"</a>)</p>\n";
       echo "          <h4>", ww('ProfileSummary'), "</h4>\n";
    echo "          <p>", $m->ProfileSummary, "</p\n";
    echo "             <h4>", ww('Address'), "</h4>\n";
    echo "             <ul>\n";
    echo "               <li>", $m->HouseNumber, ", ", $m->StreetName, "</li>\n";
    echo "               <li>", $m->Zip, " ", $m->cityname, "</li>\n";
    echo "               <li>", $m->regionname, "</li>\n";
    echo "               <li>", $m->countryname, "</li>\n";
    echo "             </ul>\n";
    echo "            <br />\n";
  if ($m->FeedBack!="") echo "             <p>Feedback : <font color=green><b><i>", str_replace("\n","<br>",$m->FeedBack), "</i></b></font></p>\n";
    echo "             <p>\n";
    if ($m->Status == "Pending") {
       echo "               <input type=radio name=action_".$global_count." value=accept> accept<br>\n";
    }
    echo "               <input type=radio name=action_".$global_count." value=reject> reject<br>\n";
    if ($m->Status == "Pending") {
       echo "               <input type=radio name=action_".$global_count." value=needmore> need more<br>\n";
    }
    echo "               <input type=radio name=action_".$global_count." value=duplicated> duplicated<br>\n";
    echo "               <input type=radio name=action_".$global_count." value=nothing> nothing<br>\n";
    echo "<p>\n";
    echo "</p>\n";
    if ($m->Status == "Pending") {
       echo "needmore aditional text for emailing to member<br>";
       echo "                <textarea name=needmoretext_".$global_count." cols=60 rows=4>";
       echo "</textarea>\n";
    }
    echo "</p>\n";

    echo "             <ul class=\"linkist\">";
    if ($m->Status == "MailToConfirm") {
       echo "              <li><a href=\"../resendconfirmyourmail.php?Username=".$m->Username."\" onclick=\"return('Confirm you want to send again ? (beware not to spam members !) ');\">Send request for confirmation mail again</a>" ;
       $countnotify=CountNotify($m->Username) ;
       if ($countnotify==0) {
           echo "<i> never re-notified </i>" ;
       }
       else {
           echo "<b> already re-notified ",$countnotify," time</b>" ;
       }
       echo "</li>" ;
    }
    echo "               <li><a href=\"".bwlink("contactmember.php?cid=". $m->id). "\">contact</a></li>\n";
    echo "               <li><a href=\"".bwlink("updatemandatory.php?cid=". $m->id). "\">update mandatory</a></li>\n";
    echo "             </ul>\n";
    echo "           </div>\n";
    $global_count++;
  }
  echo "        <div class=\"info\">\n";
  echo "          <p>Total ", $count, "</p>\n";
} // end of ShowList

function DisplayAdminAccepter($TData) {
  global $countmatch;
  global $title;
  global $global_count;
  $title = "Accept members";
  global $AccepterScope ;
  global $StrLog; // StrLog will have the last recorded action

  $Status=GetStrParam("Status","Pending") ;

  $global_count=0 ;

  include "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminaccepter.php", ww('MainPage')); // Displays the second menu

  $MenuAction  = "            <li><a href=\"http://www.bevolunteer.org/wiki/Signup_Tool:_HowTo\" target=\"new\">Wiki HowTo</a</li>\n";

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";
  echo "          <p>", $StrLog,"</p>\n";
  echo "          <h2>your Scope : ", $AccepterScope, " </h2>\n";


//  if (!IsAdmin()) {
//    echo "temporarly disabled, under test";
//    include "footer.php";
//  }


  $tt=sql_get_enum("members","Status") ;
  $filterstatus="          <select name=Status>\n";
  for ($ii=0;$ii<count($tt);$ii++) {
      $filterstatus.="          <option value=\"".$tt[$ii]."\"";
       if ($tt[$ii]==$Status) $filterstatus.=" selected" ;
      $filterstatus.=">$tt[$ii]</option>\n";
  }
  $filterstatus.="          </select>  <input type=submit id=submit name=submit></p>\n" ;

  echo "          <form name=adminaccepter action=".bwlink("admin/adminaccepter.php").">\n";

  ShowList($TData,"#ffff66"," Members with status ".$filterstatus);
  echo "<div class=\"center\"><input type=submit id=submit name=submit></div>\n";

  echo "<input type=hidden name=action value=batchaccept>";
  echo "<input type=hidden name=global_count value=$global_count>";
  echo "          </form>\n";
  echo "        </div> <!-- info -->\n";

  include "footer.php";
} // end of DisplayAdminAccepter($Taccepted,$Tmailchecking,$Tpending)
