<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;

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
			break ;
		case "moveup" :
		  // First recompute order of pictures
      $TData=array() ;
			$ii=0 ;
      $str="select * from membersphotos where membersphotos.IdMember=".$IdMember." order by SortOrder asc" ;
	    $qry=sql_query($str) ;
	    while ($rr=mysql_fetch_object($qry)) {  // Fix Sort numbers
	      array_push($TData,$rr) ;
				$str="update membersphotos set SortOrder=".$ii." where id=".$rr->id." and IdMember=".$IdMember ;
	      sql_query($str) ;
				$ii++ ;
	    }
			$max=$ii ;
			$iPos=GetParam("iPos") ;
			if ($iPos>0) { // if not to the up picture
				$str="update membersphotos set SortOrder=".$TData[$iPos-1]->SortOrder." where id=".$TData[$iPos]->id." and IdMember=".$IdMember ;
	      sql_query($str) ;
				$str="update membersphotos set SortOrder=".$TData[$iPos]->SortOrder." where id=".$TData[$iPos-1]->id." and IdMember=".$IdMember ;
	      sql_query($str) ;
				$TData[$ii]->SortOrder=$ii ;
			}
			break ;

		case "movedown" :
		  // First recompute order of pictures
      $TData=array() ;
			$ii=0 ;
      $str="select * from membersphotos where membersphotos.IdMember=".$IdMember." order by SortOrder asc" ;
	    $qry=sql_query($str) ;
	    while ($rr=mysql_fetch_object($qry)) {  // Fix Sort numbers
	      array_push($TData,$rr) ;
				$str="update membersphotos set SortOrder=".$ii." where id=".$rr->id ;
//				echo "str=$str<br>" ;
	      sql_query($str) ;
				$TData[$ii]->SortOrder=$ii ;
				$ii++ ;
	    }
			$max=$ii ;
			$iPos=GetParam("iPos") ;
			if (($iPos+1)<$max) { // if not to the up picture
				$str="update membersphotos set SortOrder=".$TData[$iPos+1]->SortOrder." where id=".$TData[$iPos]->id." and IdMember=".$IdMember ;
	      sql_query($str) ;
				$str="update membersphotos set SortOrder=".$TData[$iPos]->SortOrder." where id=".$TData[$iPos+1]->id." and IdMember=".$IdMember ;
	      sql_query($str) ;
			}
			break ;

		case "deletephoto" :
		  $str="delete from membersphotos where IdMember=".$IdMember." and id=".GetParam("IdPhoto") ;
//			echo "str=$str<br>" ;
      sql_query($str) ;
			LogStr("delete picture #".GetParam("IdPhoto"),"update profile") ;

			break ;
			
		case "UpLoadPicture" ;
		  if ($_FILES[userfile][error]!="") {
			  echo "error ",$_FILES[userfile][error],"<br>";
			}
		  
			LogStr("Upload of file <i>".$_FILES[userfile][name]."</i> ".$HTTP_POST_FILES[userfile][size]." bytes","upload photo");
 			$filename = $_FILES[userfile][name];
			$ext = strtolower(strstr($filename,"."));

//			echo "ext=$ext<br>" ;
//			echo "filename=$filename<br>" ;
// test format of file
			if (($ext!=".jpg")and($ext!=".png")) {
	      $errcode="ErrorBadPictureFormat" ;
			  @unlink ($HTTP_POST_FILES[userfile][tmp_name]); // delete erroneous file
	      DisplayError(ww($errcode,$ext)) ;
		    exit(0) ;
			}
			

// test size of file
			
 		 if ($_FILES[userfile][size]>=$_SYSHCVOL['UploadPictMaxSize']) {
        $errcode="ErrorPictureToBig" ;
			  @unlink ($_FILES[userfile][tmp_name]); // delete erroneous file
	      DisplayError(ww($errcode,($_SYSHCVOL['UploadPictMaxSize']/1024))) ;
		    exit(0) ;
			}
			
			// Compute a real name for this file
			$frealname="/memberphotos/".fUsername($IdMember)."_".time().$ext; // a uniqe name each time !
			
//			echo "frealname=",$frealname,"<br>" ;
			
			if (@copy($_FILES[userfile][tmp_name],$frealname)) { // try to copy file with its real name
			  $str="insert into membersphotos(FilePath,IdMember,created,SortOrder,Comment) values('".$frealname."',".$IdMember.",now(),0,".InsertInMTrad(addslashes(GetParam("Comment"))).")" ;
				sql_query($str) ;
			}
			else {
			  echo "fail to copy ".$_FILES[userfile][tmp_name]." to ".$frealname ;
			}
			
			
//		  echo "Comment=",GetParam("Comment"),"<br>" ;
			break ;

		case "updatecomment" ;
		  $rr=LoadRow("select Comment,id from membersphotos where IdMember=".$IdMember." and id=".GetParam("IdPhoto")) ;
		  ReplaceInMTrad(addslashes(GetParam("Comment")),$rr->Comment,$IdMember) ;
			LogStr("Updating comment for picture #".$rr->id,"update profile") ;
			break ;

			
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
	}

  $TData=array() ;
// Try to load groups and caracteristics where the member belong to
  $str="select * from membersphotos  where membersphotos.IdMember=".$IdMember." order by SortOrder asc" ;
	$qry=sql_query($str) ;
	$TData=array() ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TData,$rr) ;
	}

  require_once "layout/myphotos.php" ;
  DisplayMyPhotos($TData,$IdMember,$lastaction) ;

?>
