<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;

  // test if is logged, if not logged and forward to the current page
  if (!IsLogged()) {
    Logout($_SERVER['PHP_SELF']) ;
	  exit(0) ;
  }

	if (!isset($_SESSION['IdMember'])) {
	  $errcode="ErrorMustBeIndentified" ;
	  DisplayError(ww($errcode)) ;
		exit(0) ;
	}


// Find parameters
	$IdMember=$_SESSION['IdMember'] ;
	if (IsAdmin()) { // admin can alter other profiles
	  $IdMember=GetParam("cid",$_SESSION['IdMember']) ;
	}

// manage picture photorank (swithing from one picture to the other)
  $photorank=GetParam("photorank",0) ;


	switch(GetParam("action")) {
	  case "update" :
		  
		  $m=LoadRow("select * from members where id=".$IdMember) ;
			
		  $str="update members set ProfileSummary=".ReplaceInMTrad(addslashes($_POST['ProfileSummary']),$m->ProfileSummary,$IdMember) ;
		  $str.=",AdditionalAccomodationInfo=".ReplaceInMTrad(addslashes($_POST['AdditionalAccomodationInfo']),$m->AdditionalAccomodationInfo,$IdMember) ;
			$str.=",Accomodation='".$_POST['Accomodation']."'" ;
		  $str.=",Organizations=".ReplaceInMTrad(addslashes($_POST['Organizations']),$m->Organizations,$IdMember) ;
			$str.=" where id=".$IdMember ;
	    sql_query($str) ;
//			echo "str=$str<br>" ;
			
			// updates groups
			$max=count($TGroups) ;
			for ($ii=0;$ii<$max;$ii++) {
			  $ss=addslashes($_POST["Group_".$TGroups[$ii]->Name]) ;
//				 echo "replace $ss<br> for \$TGroups[",$ii,"]->Comment=",$TGroups[$ii]->Comment," \$IdMember=",$IdMember,"<br> " ; continue ;
				
			  $IdTrad=ReplaceInMTrad($ss,$TGroups[$ii]->Comment,$IdMember) ;
//				echo "replace $ss<br> for \$IdTrad=",$IdTrad,"<br>é ; ;
				if ($IdTrad!=$TGroups[$ii]->Comment) {
				  sql_query("update membersgroups set Comment=".$IdTrad." where id=".$TGroups[$ii]->id) ;
				}
			}
			
			
			if ($IdMember==$_SESSION['IdMember']) LogStr("Profil update by member himself","Profil update") ;
			else LogStr("update of another profil","Profil update") ;
			break ;
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}

  $TData=array() ;
// Try to load groups and caracteristics where the member belong to
  $str="select * from membersphotos  where membersphotos.IdMember=".$IdMember." order by SortOrder ;
	$qry=sql_query($str) ;
	$TData=array() ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TData,$rr) ;
	}

  include "layout/MyPhotos.php" ;
  DisplayMyPhotos($TData,$action,$IdMember,$lastaction) ;

?>
