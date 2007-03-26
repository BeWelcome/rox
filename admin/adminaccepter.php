<?php
require_once "../lib/init.php";
require_once "../layout/adminaccepter.php";

function loaddata($Status, $RestrictToIdMember = "") {

	global $AccepterScope;

	$TData = array ();

	if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
		$InScope = "";
	} else {
		$InScope = "and countries.id in (" . $AccepterScope . ")";
	}

	$str = "select countries.Name as countryname,regions.Name as regionname,cities.Name as cityname,members.* from members,countries,regions,cities where members.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=cities.IdCountry " . $InScope . " and Status='" . $Status . "'";
	if ($RestrictToIdMember != "") {
		$str .= " and members.id=" . $RestrictToIdMember;
	}

	$qry = sql_query($str);
	while ($m = mysql_fetch_object($qry)) {

		$StreetName = "";
		$Zip = "";
		$HouseNumber = "";
		$rAddress = LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.Name as regionname,cities.Name as cityname,regions.id as IdRegion from addresses,countries,regions,cities where IdMember=" . $m->id . " and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=cities.IdCountry");
		if (isset ($rAddress->IdCity)) {
			$m->StreetName = AdminReadCrypted($rAddress->StreetName);
			$m->Zip = AdminReadCrypted($rAddress->Zip);
			$m->HouseNumber = AdminReadCrypted($rAddress->HouseNumber);
		}
		
		$m->Email=AdminReadCrypted($m->Email);

		$m->FirstName=AdminReadCrypted($m->FirstName);
		$m->LastName=AdminReadCrypted($m->LastName);
		$m->SecondName=AdminReadCrypted($m->SecondName);

		$m->ProfileSummary = FindTrad($m->ProfileSummary);
		$FeedBack = "";
		$qryFeedBack = sql_query("select * from feedbacks where IdMember=" . $m->id . " and IdFeedbackCategory=3 order by id desc");
		while ($rrFeedBack = mysql_fetch_object($qryFeedBack)) {
			if ($FeedBack != "") {
				$FeedBack .= "<hr>";
			}
			$FeedBack .= $rrFeedBack->Discussion;
		}
		if ($FeedBack == "") {
			$m->FeedBack = "no FeedBack";
		} else {
			$m->FeedBack = $FeedBack;
		}
		$m->Email=GetEmail($m->id);
		array_push($TData, $m);
	}

	return ($TData);

} // end of load data

//------------------------------------------------------------------------------

MustLogIn(); // need to be log

$IdMember = GetParam("cid");

$countmatch = 0;

$RightLevel = HasRight('Accepter'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>Accepter</b> rights<br>";
	exit (0);
}

$AccepterScope = RightScope('Accepter');
if ($AccepterScope != "All") {
	$AccepterScope = str_replace("\"", "'", $AccepterScope);
}

$lastaction = "";
switch (GetParam("action")) {
	case "batchaccept" :
		$max=GetParam("global_count");
		$StrAccept=$StrNeedMore=$StrReject="";
		$CountAccept=$CountNeedMore=$CountReject=0;
		for ($ii=0;$ii<$max;$ii++) {
			$IdMember=GetParam("IdMember_".$ii);
		   // todo change what need to be change to answer in member default language
		   $defLanguage=0;
			switch (GetParam("action_".$ii)) {
				case "accept" :
				   $m = LoadRow("select * from members where id=" . $IdMember);
				   $str = "update members set Status='Active' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
				   $qry = sql_query($str);

				   $Email = AdminReadCrypted($m->Email);
				   $subj = wwinlang("SignupSubjAccepted",$defaultlanguage, "http://".$_SYSHCVOL['SiteName']);
				   $loginurl = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']."/login.php?&Username=" . $m->Username;
				   $text = wwinlang("SignupYouHaveBeenAccepted",$defaultlanguage, $m->Username, "http://".$_SYSHCVOL['SiteName'], $loginurl);
				   bw_mail($Email, $subj, $text, "", $_SYSHCVOL['AccepterSenderMail'], $defLanguage, "yes", "", "");
				   $StrAccept.=$m->Username;
				   $CountAccept++;

				   break;
				case "reject" :
				   $m = LoadRow("select * from members where id=" . $IdMember);
				   $str = "update members set Status='Rejected' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
				   $qry = sql_query($str);

				   $Email = AdminReadCrypted($m->Email);
				   $subj = wwinlang("SignupSubjRejected",$defaultlanguage,$_SYSHCVOL['SiteName']);
				   $text = wwinlang("SignupYouHaveBeenRejected",$defaultlanguage, $m->Username,$_SYSHCVOL['SiteName']);
				   bw_mail($Email,$subj, $text, "", $_SYSHCVOL['AccepterSenderMail'],0, "yes", "", "");
				   $StrReject.=$m->Username." ";
				   $CountReject++;

				   break;
				case "needmore" :
				   $m = LoadRow("select * from members where id=" . $IdMember);
				   $needmoretext=GetParam("needmoretext_".$ii);
				   $urltoreply = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']. "login.php?Username=".$m->Username;
				   $m = LoadRow("select * from members where id=" . $IdMember);
				   $str = "update members set Status='NeedMore' where (Status='Pending' or Status='Active' or Status='CompletedPending') and id=" . $IdMember;
				   $qry = sql_query($str);
				   $Email = AdminReadCrypted($m->Email);
				   $subj = wwinlang("SignupNeedmoreTitle",$defaultlanguage,$_SYSHCVOL['SiteName']);
				   $text = wwinlang("SignupNeedMoreText",$defaultlanguage, $m->Username,$_SYSHCVOL['SiteName'],$needmoretext,$urltoreply);
				   bw_mail($Email,$subj, $text, "", $_SYSHCVOL['AccepterSenderMail'],0, "yes", "", "");
				   $StrReject.=$m->Username." ";
				   $CountReject++;
				   $StrNeedMore.=$m->Username." ";
				   $CountNeedMore++;
		   	  	   break;
			}
		} // end of for
		$StrLog=0;
		if ($CountAccept>0) {
		   $StrLog="(".$CountAccepted." accepted)".$StrAccept;
		}
		if ($CountNeedMore>0) {
		   if ($StrLog!="") $StrLog.="<br>\n";
		   $StrLog="(".$CountNeedMore." need more)".$StrNeedMore;
		}
		if ($CountStrReject>0) {
		   if ($StrLog!="") $StrLog.="<br>\n";
		   $StrLog="(".$CountStrReject." rejected)".$StrStrReject;
		}
		$lasaction=$Strlog;
		LogStr($StrLog,"accepting");
		break;
	case "accept" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		// todo change what need to be change to answer in member default language
		$defLanguage=0;
		$lastaction = "accepting " . $m->Username;
		$str = "update members set Status='Active' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);

		$Email = AdminReadCrypted($m->Email);
		// todo change what need to be change to answer in member default language
		$subj = ww("SignupSubjAccepted", "http://".$_SYSHCVOL['SiteName']);
		$loginurl = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']."/login.php?&Username=" . $m->Username;
		$text = ww("SignupYouHaveBeenAccepted", $m->Username, "http://".$_SYSHCVOL['SiteName'], $loginurl);
		bw_mail($Email, $subj, $text, "", $_SYSHCVOL['AccepterSenderMail'], $defLanguage, "yes", "", "");

		break;
	case "reject" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		// todo change what need to be change to answer in member default language
		$defLanguage=0;
		$lastaction = "rejecting " . $m->Username;
		$str = "update members set Status='Rejected' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);

		$Email = AdminReadCrypted($m->Email);
		$subj = ww("SignupSubjRejected",$_SYSHCVOL['SiteName']);
		$text = ww("SignupYouHaveBeenRejected", $m->Username,$_SYSHCVOL['SiteName']);
//		echo "$subj<br>$text<br> sent to $Email<br> from ".$_SYSHCVOL['AccepterSenderMail'];
//		bw_mail($Email,$subj,"text as test   ", "", $_SYSHCVOL['TestMail'], 0, "yes", "", "");
		bw_mail($Email,$subj, $text, "", $_SYSHCVOL['AccepterSenderMail'],0, "yes", "", "");

		break;
	case "needmore" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		$lastaction = "setting profile of  " . $m->Username . " from " . $m->Status . " to NeedMore";

		$str = "update members set Status='NeedMore' where (Status='Pending' or Status='Active' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);
		// to do manage the need more
		break;

	case "ShowOneMember" :
		$RestrictToIdMember = IdMember(GetParam("cid", 0));
		break;
}

$Taccepted = loaddata("Active", $RestrictToIdMember);
$Tmailchecking = loaddata("MailToConfirm", $RestrictToIdMember);
$Tpending = loaddata("Pending", $RestrictToIdMember);
$TNeedMore = loaddata("Needmore", $RestrictToIdMember);

DisplayAdminAccepter($Taccepted, $Tmailchecking, $Tpending, $TNeedMore, $lastaction); // call the layout
?>