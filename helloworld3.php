<?php


// This simple program will fetch the username and the profile summary of the current user
// In addition it will propose a form to enter another username to look at

require_once "lib/init.php"; // include the DBaccess routines, + lib files + session managment
require_once "layout/helloworld3.php"; // Must include the proper layout file	

MustLogIn(); // This will force the user to be logged (call the login if he he is not)

$IdMember = $_SESSION['IdMember']; // By default, the current logged member

switch (GetParam("action")) { // GetParam will retrieve $_POST and $_GET parameters
	case "show_new_user" : //
		$IdMember = IdMember(GetParam("Username")); // retrieve the Id of the member
		break;
}

$str = "select Username,ProfileSummary from members where id=" . $IdMember; // Build a request according to parameters
$rr = LoadRow($str); // Load one row according to the request $str
// This will result here in $rr->Username and $rr->ProfileSummary because of the request

// Call the layout (with the parameter)
DisplayHelloWorld3($rr);
?>