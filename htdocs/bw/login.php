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

	case "logout" :
		Logout("index.php");
		exit (0);
}

require_once "layout/login.php";
DisplayLogin($nextlink);
?>
