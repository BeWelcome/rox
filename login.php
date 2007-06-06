<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "lib/prepare_profile_header.php";

$nextlink = urldecode(GetStrParam("nextlink"));
if (($nextlink == "") or ($nextlink == "login.php"))
	$nextlink = "main.php";
	
switch (GetParam("action")) {
	case "login" :
		Login(GetStrParam("Username"), GetStrParam("password"), $nextlink);
		break;

	case "confirmsignup" : // case a new signupper confirm his mail
		$m = prepareProfileHeader(IdMember(GetParam("username"))," and Status='MailToConfirm' "); // pending members can edit their profile
		if (isset ($m->id)) {

			$key = CreateKey($m->Username, ReadCrypted($m->LastName), $m->id, "registration"); // retrieve the nearly unique key

//			echo "key=", $key, "<br>";
//			echo " GetParam(\"key\")=", GetParam("key"), "<br>";
//			echo "ReadCrypted(\$m->LastName)=", ReadCrypted($m->LastName), "<br>";
//			echo "\$m->Username=", $m->Username, "<br>";

			if ($key != GetStrParam("key")) {
				$errcode = "ErrorBadKey";
				LogStr("Bad Key", "hacking");
				DisplayError(ww($errcode));
				exit (0);
			}

			if (GetParam("StopBoringMe",0)==1) { // Case in fact the member doesn't want to be signup, but want to be removed
				 $str = "update members set Status='StopBoringMe' where id=" . $m->id;
				 LogStr("While his mail was not yet confirmed, member has ask us to stop boring him with confirmation request","Signup") ;
				 echo "OK, <b>",$m->Username,"</b> we will not send you this confirmation request anymore, thanks for visiting us" ;
			}


			$str = "update members set Status='Pending' where id=" . $m->id;
			sql_query($str);
			if ($m->IdCity > 0) {
				$rWhere = LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=" . $m->IdCity);
			}
			require_once "layout/editmyprofile.php";
			$profilewarning = ww("YouCanCompleteProfAndWait", $m->Username);
			DisplayEditMyProfile($m, "", "", 0, $rWhere->cityname, $rWhere->regionname, $rWhere->countryname, $profilewarning, array ());
		}
		exit (0);
	case "logout" :
		Logout("index.php");
		exit (0);
}

require_once "layout/login.php";
DisplayLogin($nextlink);
?>
