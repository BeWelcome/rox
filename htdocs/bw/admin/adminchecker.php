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
chdir("..") ;
require_once "lib/init.php";
require_once "layout/error.php";
require_once "layout/adminchecker.php";

$username = fUsername(GetStrParam("username"));
$sResult="" ; // initialisation to avoid a warning
$RightLevel = HasRight('Checker'); // Check the rights
//if ($RightLevel < 1) {
//	echo "This Need the suffcient <b>Checker</b> rights<br>";
//	exit (0);
//}


// this function call the view of reported spam
function viewSpamSayMember($sResult="") { 
	   
	   $TMess=array() ;
	   $str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where mSender.id=IdSender and messages.SpamInfo='SpamSayMember' and mReceiver.id=IdReceiver and (mSender.Status='Active' or mSender.Status='Pending') order by messages.id desc limit 50";
		if (GetStrParam("IdSender","") !="") {
		   $str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where mSender.id=IdSender and mReceiver.id=IdReceiver and (mSender.Status='Active' or mSender.Status='Pending') and messages.SpamInfo='SpamSayMember' and messages.IdSender=".IdMember(GetStrParam("IdSender",0))." order by messages.id desc limit 20";
		}
		if (GetStrParam("IdReceiver","") !="") {
		   $str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where mSender.id=IdSender and mReceiver.id=IdReceiver and (mSender.Status='Active' or mSender.Status='Pending') and messages.SpamInfo='SpamSayMember' and messages.IdReceiver".IdMember(GetStrParam("IdReceiver",0))." order by messages.id desc limit 20";
		}
//		echo "str=$str<br>" ;
		$qry = sql_query($str);
		
		while ($rr = mysql_fetch_object($qry)) {
			  array_push($TMess,$rr);
		}
		DisplayMessages($TMess, $sResult,GetStrParam("IdSender","")); // call the layout
		exit(0) ; // exit after the layout has been called
} // end of viewSpamSayMember

$scope = RightScope('Checker');
$TMess = array ();

$lastaction = "";
$action=GetParam("action") ;
switch ($action) {
	case "logout" :
		Logout();
		exit (0);
		break;
	case "PendingSpammers" :
	    $str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,count(*) as cnt from messages,members as mSender where mSender.id=IdSender and messages.Status='ToCheck' group by mSender.Username order by mSender.Username desc";
		$qry = sql_query($str);
		
		$tot=0 ;
		while ($rr = mysql_fetch_object($qry)) {
			  array_push($TMess,$rr);
			  $tot++ ;
		}
		$sResult=$tot." pending potential spammers with messages to process" ;
		DisplayPendingMayBeSpammers($TMess, $sResult); // call the layout
		exit(0) ;
		 break ;
		 
		 
		
	case "view" :
	   $str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where mSender.id=IdSender and messages.Status='ToCheck' and mReceiver.id=IdReceiver order by messages.id desc limit 50";
		if (GetStrParam("IdSender","") !="") {
		   $str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where mSender.id=IdSender and mReceiver.id=IdReceiver and messages.Status='ToCheck' and messages.IdSender=".IdMember(GetStrParam("IdSender",0))." order by messages.id desc limit 20";
		}
		if (GetStrParam("IdReceiver","") !="") {
		   $str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where mSender.id=IdSender and mReceiver.id=IdReceiver and messages.Status='ToCheck' and messages.IdReceiver".IdMember(GetStrParam("IdReceiver",0))." order by messages.id desc limit 20";
		}
//		echo "str=$str<br>" ;
		$qry = sql_query($str);
		
		while ($rr = mysql_fetch_object($qry)) {
			  array_push($TMess,$rr);
		}
		DisplayMessages($TMess, $sResult,GetStrParam("IdSender","")); // call the layout
		exit(0) ;
		
	case "SpamReportsFor" :
		$str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where mSender.id=IdSender and mReceiver.id=IdReceiver and messages.SpamInfo!='NotSpam' and messages.IdSender=".IdMember(GetStrParam("IdSender",$_SESSION["IdMember"]))." order by messages.id desc limit 20";
		echo "str=$str<br>" ;
		$qry = sql_query($str);
		
		while ($rr = mysql_fetch_object($qry)) {
			  array_push($TMess,$rr);
		}
		DisplayMessages($TMess, $sResult,GetStrParam("IdSender","")); // call the layout
		exit(0) ;
		
	case "check" :
		// Load the Message list
		$ii = 0;
		if (GetStrParam("IdSender","") !="") {
			 $strlist = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where messages.Status='ToCheck' and mSender.id=IdSender and mReceiver.id=IdReceiver and messages.IdSender=".IdMember(GetStrParam("IdSender"))." order by messages.id desc";
//			 echo $strlist,"<br>\n" ;
		}
		else {
			 $strlist = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where messages.Status='ToCheck' and mSender.id=IdSender and mReceiver.id=IdReceiver order by messages.id desc";
		}
		$qry = sql_query($strlist);
		$count = 0;
		while (GetParam("IdMess_" . $ii,0)!=0) {
			$ss="select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where mSender.id=IdSender and mReceiver.id=IdReceiver and messages.id=".GetParam("IdMess_" . $ii) ;
			$rr=LoadRow($ss) ;
//	    echo "checking :",$rr->id," [",GetStrParam("Approve_" . $ii)."] IdMess_".$ii,"=",GetParam("IdMess_" . $ii),"<br> " ;
			if (GetParam("IdMess_" . $ii) == $rr->id) { // If this message is in the list of checked message
				//				  echo "Approve_",$ii,"=",GetStrParam("Approve_".$ii),"<br>";
				$SpamChange = "";
				if (($rr->SpamInfo == "NotSpam") and (GetStrParam("Mark_Spam_" . $ii) == "on")) { // If it was not considered as spam, but checker say it is a spam
					$SpamChange = ",SpamInfo='SpamSayChecker'";
				}
				if (($rr->SpamInfo == "SpamBlkWord") and (GetStrParam("Mark_Spam_" . $ii) == "")) { // If it was considered as spam, but checker say it is not
					$SpamChange = ",SpamInfo='NotSpam'";
				}
				if (GetStrParam("Approve_" . $ii) == "on") {
					$count++;
					$str = "update messages set IdChecker=" . $_SESSION['IdMember'] . ",Status='ToSend'" . $SpamChange . " where id=" . $rr->id;
//											echo "str=$str","<br>";
					sql_query($str);

				}
				if (GetStrParam("Processed_" . $ii) == "on") {
					$count++;
					$SpamChange = ",SpamInfo='".$rr->SpamInfo.",ProcessedBySpamManager'";
					$str = "update messages set IdChecker=" . $_SESSION['IdMember'] . $SpamChange . " where id=" . $rr->id;
//											echo "str=$str","<br>";
					sql_query($str);

				}

				if (GetStrParam("Freeze_" . $ii) == "on") {
					$count++;
					$str = "update messages set IdChecker=" . $_SESSION['IdMember'] . ",Status='Freeze'" . $SpamChange . " where id=" . $rr->id;
					//						echo "str=$str","<br>";
					sql_query($str);

				}
			} // end of If this message is in the list of checked message
			$ii++;
		}
		$sResult = $count . " Message processed";
		if ($count > 0)
			LogStr($sResult, "checking"); // Log the number of checked message if any
		// end of Load the Message list

	    viewSpamSayMember($sResult) ;
		break ;
		
	case "viewSpamSayMember" :
	   viewSpamSayMember($sResult) ;
	   break ;
	   
	case "update" :
		break;
}

// Load the Message list
$str = "select messages.*,messages.Status as MessageStatus,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where (messages.Status='ToCheck' and messages.WhenFirstRead='0000-00-00 00:00:00') and mSender.id=IdSender and mReceiver.id=IdReceiver order by messages.Status,messages.id desc limit 20";
if (IsAdmin()) echo "$str<br>" ;
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	//	  if not scope test continue; // Skip not allowed rights  todo manage an eventual scope test
	array_push($TMess,$rr);
}
// end of Load the Message list

DisplayMessages($TMess, $sResult); // call the layout
?>