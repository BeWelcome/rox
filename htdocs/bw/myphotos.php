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
require_once "lib/init.php";
require_once "layout/error.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/myphotos.php";
require_once "lib/prepare_profile_header.php";

// test if is logged, if not logged and forward to the current page
if (GetParam("PictForMember","")!="") {
		$SortPict=GetParam("PictNum",0)	;			  
		$Photo=LoadRow("select membersphotos.*,Username from membersphotos,members,memberspublicprofiles where members.id=".IdMember(GetParam("PictForMember"))." and members.id=memberspublicprofiles.IdMember and members.id=membersphotos.IdMember and membersphotos.SortOrder=".$SortPict);

		if (!isset($Photo->id)) {
		   $Photo=LoadRow("select membersphotos.*,Username from membersphotos,members,memberspublicprofiles where members.id=".IdMember("admin")." and members.id=memberspublicprofiles.IdMember and members.id=membersphotos.IdMember and membersphotos.SortOrder=0");
		}
		$fpath=$Photo->FilePath;
//		echo "readlink=",readlink("memberphotos"),"<br />";
//		$fpath=str_replace("/memberphotos/","/var/www/upload/images/",$Photo->FilePath);
//		$ff=fopen($fpath, 'rb');
//		if (!$ff) die ("cant open file ".$fpath);
//		fpassthru($ff);
//       fclose($ff);
		echo $_SYSHCVOL['SiteName'].$fpath;
		exit(0);
} 

// test if is logged, if not logged and forward to the current page
// exeption for the people at confirm signup state
if ((!IsLoggedIn()) and (GetParam("action") != "confirmsignup") and (GetParam("action") != "update")) {
	$ItsAPendingMember=false ;
	if (isset ($_SESSION['IdMember'])) { // if there is a IdMember in session (this can because of a memebr in pending state
	   $m = prepareProfileHeader($_SESSION['IdMember']," and (Status='Pending')"); // pending members can edit their profile
	   $ItsAPendingMember= ($m->Status=="Pending") ;
	}
	if (! $ItsAPendingMember) { // A pending member will be allowed to edit his picture
	   APP_User::get()->logout();
	   header("Location: " . $_SERVER['PHP_SELF']);
	   exit (0);
	}
}

if (!isset ($_SESSION['IdMember'])) {
	$errcode = "ErrorMustBeIndentified";
	DisplayError(ww($errcode));
	exit (0);
}

// Find parameters
$IdMember = $_SESSION['IdMember'];
if ((IsAdmin())or(CanTranslate(GetParam("cid", $_SESSION['IdMember'])))) { // admin or CanTranslate can alter other profiles 
	$IdMember = GetParam("cid", $_SESSION['IdMember']);
}

// manage picture photorank (swithing from one picture to the other)
$photorank = GetParam("photorank", 0);

switch (GetParam("action")) {
	case "update" :
		break;
	case "viewphoto" :
		$Photo=LoadRow("select membersphotos.*,Username from membersphotos,members where members.id=membersphotos.IdMember and membersphotos.id=".GetParam("IdPhoto"));
		$Photo->Comment=FindTrad($Photo->Comment);
		DisplayPhoto($Photo);
		exit(0);
	
	case "moveup" :
		// First recompute order of pictures
		$TData = array ();
		$ii = 0;
		$str = "select * from membersphotos where membersphotos.IdMember=" . $IdMember . " order by SortOrder asc";
		$qry = sql_query($str);
		while ($rr = mysql_fetch_object($qry)) { // Fix Sort numbers
			array_push($TData, $rr);
			$str = "update membersphotos set SortOrder=" . $ii . " where id=" . $rr->id . " and IdMember=" . $IdMember;
			sql_query($str);
			$ii++;
		}
		$max = $ii;
		$iPos = GetParam("iPos");
		if ($iPos > 0) { // if not to the up picture
			$str = "update membersphotos set SortOrder=" . $TData[$iPos -1]->SortOrder . " where id=" . $TData[$iPos]->id . " and IdMember=" . $IdMember;
			sql_query($str);
			$str = "update membersphotos set SortOrder=" . $TData[$iPos]->SortOrder . " where id=" . $TData[$iPos -1]->id . " and IdMember=" . $IdMember;
			sql_query($str);
			$TData[$ii]->SortOrder = $ii;
		}
		break;

	case "movedown" :
		// First recompute order of pictures
		$TData = array ();
		$ii = 0;
		$str = "select * from membersphotos where membersphotos.IdMember=" . $IdMember . " order by SortOrder asc";
		$qry = sql_query($str);
		while ($rr = mysql_fetch_object($qry)) { // Fix Sort numbers
			array_push($TData, $rr);
			$str = "update membersphotos set SortOrder=" . $ii . " where id=" . $rr->id;
			//				echo "str=$str<br />";
			sql_query($str);
			$TData[$ii]->SortOrder = $ii;
			$ii++;
		}
		$max = $ii;
		$iPos = GetParam("iPos");
		if (($iPos +1) < $max) { // if not to the up picture
			$str = "update membersphotos set SortOrder=" . $TData[$iPos +1]->SortOrder . " where id=" . $TData[$iPos]->id . " and IdMember=" . $IdMember;
			sql_query($str);
			$str = "update membersphotos set SortOrder=" . $TData[$iPos]->SortOrder . " where id=" . $TData[$iPos +1]->id . " and IdMember=" . $IdMember;
			sql_query($str);
		}
		break;

	case "deletephoto" :
		$str = "delete from membersphotos where IdMember=" . $IdMember . " and id=" . GetParam("IdPhoto");
		//			echo "str=$str<br />";
		sql_query($str);
		LogStr("delete picture #" . GetParam("IdPhoto"), "update profile");

		break;

	case "UpLoadPicture";
		if ($_FILES[userfile][error] != "") {
			echo "error ", $_FILES[userfile][error], "<br />";
		}

		LogStr("Upload of file <i>" . $_FILES[userfile][name] . "</i> " . $_FILES[userfile][size] . " bytes", "upload photo");
		$filename = $_FILES[userfile][name];
		$ext = strtolower(strstr($filename, "."));

		//			echo "ext=$ext<br />";
		//			echo "filename=$filename<br />";
		// test format of file
		if (($ext != ".jpg") and ($ext != ".png")) {
			$errcode = "ErrorBadPictureFormat";
			@ unlink($HTTP_POST_FILES[userfile][tmp_name]); // delete erroneous file
			DisplayError(ww($errcode, $ext));
			exit (0);
		}

		// test size of file

		if ($_FILES[userfile][size] >= $_SYSHCVOL['UploadPictMaxSize']) {
			$errcode = "ErrorPictureToBig";
			@ unlink($_FILES[userfile][tmp_name]); // delete erroneous file
			DisplayError(ww($errcode, ($_SYSHCVOL['UploadPictMaxSize'] / 1024)));
			exit (0);
		}

		// Compute a real name for this file
		$fname = fUsername($IdMember) . "_" . time() . $ext; // a uniqe name each time !;

		//			echo "fname=",$fname,"<br />";

		if (@copy($_FILES[userfile][tmp_name], $_SYSHCVOL['IMAGEDIR'] ."/". $fname)) { // try to copy file with its real name
			$str = "insert into membersphotos(FilePath,IdMember,created,SortOrder,Comment) values('" . "/memberphotos/" . $fname . "'," . $IdMember . ",now(),-1," . InsertInMTrad(GetStrParam("Comment")) . ")";
			sql_query($str);
			$ii=0;
		    $str = "select * from membersphotos where membersphotos.IdMember=" . $IdMember . " order by SortOrder asc";
			$qry = sql_query($str);
			while ($rr = mysql_fetch_object($qry)) { // Fix Sort numbers
				  $str = "update membersphotos set SortOrder=" . $ii . " where id=" . $rr->id . " and IdMember=" . $IdMember;
				  sql_query($str);
				  $ii++;
			}
		} else {
			echo "failed to copy " . $_FILES[userfile][tmp_name] . " to " . $_SYSHCVOL['IMAGEDIR'] . $fname;
		}

		//		  echo "Comment=",GetParam("Comment"),"<br />";
		break;

	case "updatecomment";
		$rr = LoadRow("select Comment,id from membersphotos where IdMember=" . $IdMember . " and id=" . GetParam("IdPhoto"));
		ReplaceInMTrad(GetStrParam("Comment"), $rr->Comment, $IdMember);
		LogStr("Updating comment for picture #" . $rr->id, "update profile");
		break;

}

$TData = array ();
// Try to load groups and caracteristics where the member belong to
$str = "select * from membersphotos  where membersphotos.IdMember=" . $IdMember . " order by SortOrder asc";
$qry = sql_query($str);
$TData = array ();
while ($rr = mysql_fetch_object($qry)) {
	array_push($TData, $rr);
}

$m = prepareProfileHeader($IdMember," and (Status='Active' or Status='Pending'or Status='ActiveHidden'or Status='NeedMore')"); // pending members can edit their profile 

DisplayMyPhotos($m,$TData, $lastaction);
?>
