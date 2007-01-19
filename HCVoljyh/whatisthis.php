<?php
include "lib/dbaccess.php";
require_once "lib/FunctionsTools.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/Error.php";
include "layout/whatisthis.php";

switch ($action) {
	case "logout" :
		Logout("main.php");
		exit (0);
}

DisplayWhatisthis();
?>
