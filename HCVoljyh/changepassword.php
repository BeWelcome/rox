<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;
require_once "layout/changepassword.php" ;
require_once "layout/main.php" ;

  $action=GetParam("action") ;
	$password=ltrim(rtrim(GetParam("NewPassword"))) ;
	$OldPassword=ltrim(rtrim(GetParam("OldPassword"))) ; ;
	$SecPassword=ltrim(rtrim(GetParam("SecPassword"))) ;
	
	MustLog() ;
	
	$CurrentError="" ;
  switch($action) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
	  case "changepassword" :
			$rCheckId=LoadRow("select id from members where id=".$_SESSION["IdMember"]." and PassWord=PASSWORD('".$OldPassword."')" );

			if (!isset($rCheckId->id)) $CurrentError.=ww('BadPassworErrorCheck')."<br>" ;
			if ((($password!=$SecPassword)or($password==""))or(strlen($password)<8)) $CurrentError.=ww('SignupErrorPasswordCheck')."<br>" ;

			if ($CurrentError!="") {
        DisplayChangePasswordForm($CurrentError) ; // call the layout
				exit(0) ;
			}
			
			$str="update members set password=PASSWORD('".$password."') where id=".$_SESSION["IdMember"] ;
			sql_query($str) ;
			LogStr("changing password","change password") ;

			$m=LoadRow("select * from members where id=".$_SESSION["IdMember"]) ; 
			DisplayMain($m,ww("PasswordSuccesfulyChanged",$m->Username)) ;
			exit(0) ;
			break ;
	}
	

  DisplayChangePasswordForm($CurrentError) ; // call the layout
	
	

?>