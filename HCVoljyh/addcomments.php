<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;

  $TextWhere=GetParam("TextWhere") ;
  $TextFree=GetParam("Commenter") ;
  $Quality=addslashes(GetParam("Quality")) ;

	$max=count($_SYSHCVOL['LenghtComments']) ;
	$tt=$_SYSHCVOL['LenghtComments'] ;
	$LenghtComments="" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $var=$tt[$ii] ;
    if (isset($_POST["Comment_".$var])) {
		  if ($LenghtComments!="") $LenghtComments=$LenghtComments."," ;
      $LenghtComments=$LenghtComments.$var ;
    }
	}

	$IdMember=GetParam("cid",0) ;
	
	switch(GetParam("action")) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
	  case "add" :
      $str="select * from comments where IdToMember=".$IdMember." and IdFromMember=".$_SESSION["IdMember"] ; // if there is already a comment find it, we will be do an append
	    $qry=sql_query($str) ;
	    $TCom=mysql_fetch_object($qry) ;
			$newdate="<font color=gray><font size=1>comment date ".date("F j, Y, g:i a")." (UTC)</font></font><br>" ;
			
			$AdminAction="NothingNeeded"  ;
			if ($Quality=="Bad") {
			  $AdminAction="AdminCommentMustCheck'"  ;
			}
			if (!isset($TCom->id)) {
			  $TextWhere=$newdate.$TextWhere ;
			  $str="insert into comments(IdToMember,IdFromMember,Lenght,Quality,TextWhere,TextFree,AdminAction,created) values (".$IdMember.",".$_SESSION['IdMember'].",'".$LenghtComments."','".$Quality."','".addslashes($TextWhere)."','".addslashes($TextFree)."','".$AdminAction."',now())" ;
			}
			else {
			  $TextFree=$TCom->TextFree."<hr>".$newdate.$TextWhere."<br>".$TextFree ;
			  $str="update comments set AdminAction='".$AdminAction."',IdToMember=".$IdMember.",IdFromMember=".$_SESSION['IdMember'].",Lenght='".$LenghtComments."',Quality='".$Quality."',TextFree='".addslashes($TextFree)."' where id=".$TCom->id ;
			}
	    $qry=sql_query($str) or die("error<br>".$str) ;
			
			$m=LoadRow("select * from members where id=".$IdMember) ;
			$mCommenter=LoadRow("select Username from members where id=".$_SESSION['IdMember']) ;

			$defLanguage=GetDefaultLanguage($IdMember) ;
			$subj=wwinlang("NewCommentSubjFrom",$defLanguage,$mCommenter->Username) ;
			$text=wwinlang("NewCommentTextFrom",$defLanguage,$mCommenter->Username,ww("CommentQuality_".$Quality),GetParam("TextWhere"),GetParam("TextFree")) ;
			hvol_mail(GetEmail($IdMember),$subj,$text,"",$_SYSHCVOL['CommentNotificationSenderMail'],$defLanguage,"","","") ;
			
			if ($Quality=="Bad") {
			  $subj="Bad Comment from ".GetUsername($IdMember)." to ".$mCommenter->Username ;
			  $text=" Check the comment a bad comment has made by ".GetUsername($IdMember)."\n" ;
				$text.=$mCommenter->Username."\n".ww("CommentQuality_".$Quality)."\n".GetParam("TextWhere")."\n".GetParam("TextFree") ;
			  hvol_mail($_SYSHCVOL['CommentNotificationSenderMail'],$subj,$text,"",$_SYSHCVOL['CommentNotificationSenderMail'],$defLanguage,"","","") ;
			}

			LogStr("Adding a comment quality <b>".$Quality."</b> on ".$m->Username,"Comment") ;
			



			break ;
	}
	

// Try to load the Comments, prepare the layout data
// Try to load the member
	if (is_numeric($IdMember)) {
	  $str="select * from members where id=".$IdMember." and Status='Active'" ;
	}
	else {
		$str="select * from members where Username='".$IdMember."' and Status='Active'" ;
	}

	$m=LoadRow($str) ;

	if (!isset($m->id)) {
	  $errcode="ErrorNoSuchMember" ;
	  DisplayError(ww($errcode,$IdMember)) ;
//		die("ErrorMessage=".$ErrorMessage) ;
		exit(0) ;
	}

	$IdMember=$m->id ; // to be sure to have a numeric ID
	
// Load previous comments of the same commenter if any	
  $str="select comments.*,members.Username as Commenter from comments,members where IdToMember=".$IdMember." and members.id=IdFromMember and members.id=".$_SESSION["IdMember"] ;
//	echo "str=$str<br>" ;
	$qry=sql_query($str) ;
	$TCom=mysql_fetch_object($qry) ;
	
  require_once "layout/addcomments.php" ;
  DisplayAddComments($TCom,$m->Username,$IdMember) ; // call the layout

?>