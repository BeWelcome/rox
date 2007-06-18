<?php


// This simple program will fetch the username of the current user and the 
// will just display hello world in the environment of bewelcome providing 
// a parameter $Data which is a structure with a $Data->Username field 

require_once "lib/init.php"; // require_once the DBaccess routines, + lib files + session managment
require_once "layout/helloworld2.php"; // Must require_once the proper layout file	

MustLogIn(); // This will force the user to be log (call the login if he he is not)

// When a user is logged, the session variable $_SESSION['IdMember'] always contain
// the id of its entry in the members table 

$Data->Username = fUsername($_SESSION['IdMember']); // This will retrieve the username of the current logged user
// fUsername() is one of the many tool function in FunctionsTools.php

// Call the layout (with the parameter)
DisplayHelloWorld2($Data);
?>