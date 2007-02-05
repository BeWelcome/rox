<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";

$action = GetParam("action");

switch ($action) {
	case "logout" :
		Logout("main.php");
		exit (0);
}

$errcode = "ErrorTodoPage";
DisplayError(ww($errcode, $_SERVER["PHP_SELF"]));
exit (0);
?>
