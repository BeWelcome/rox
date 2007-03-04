<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";

$nextlink = urldecode(GetParam("nextlink")) ;
if (($nextlink == "") or ($nextlink == "login.php"))
	$nextlink = "main.php";
	
switch (GetParam("action")) {
	case "login" :
		Login(GetParam("Username"), GetParam("password"), $nextlink);
		break;

	case "confirmsignup" : // case a new signupper confirm his mail
		$m = LoadRow("select * from members where Username='" . GetParam("username") . "' and Status='MailToConfirm'");
		if (isset ($m->id)) {

			$key = CreateKey($m->Username, ReadCrypted($m->LastName), $m->id, "registration"); // retrieve the nearly unique key

			echo "key=", $key, "<br>";
			echo " GetParam(\"key\")=", GetParam("key"), "<br>";
			echo "ReadCrypted(\$m->LastName)=", ReadCrypted($m->LastName), "<br>";
			echo "\$m->Username=", $m->Username, "<br>";

			if ($key != GetParam("key")) {
				$errcode = "ErrorBadKey";
				LogStr("Bad Key", "hacking");
				DisplayError(ww($errcode));
				exit (0);
			}
			$str = "update members set Status='Pending' where id=" . $m->id;
			sql_query($str);
			if ($m->IdCity > 0) {
				$rWhere = LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=" . $m->IdCity);
			}
			include "layout/editmyprofile.php";
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
