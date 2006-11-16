<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;

  if (isset($_GET['action'])) {
    $action=$_GET['action'] ;
  }
  if (isset($_POST['action'])) {
    $action=$_POST['action'] ;
  }
	
  switch($action) {
	  case "login" :
		  Login($_POST['Username'],$_POST['password'],"Main.php") ;
			break ;
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}


if (IsLogged()) {
  $m=LoadRow("select * from members where id=".$_SESSION['IdMember']) ;
  include "layout/Main.php" ;
  DisplayMain($m) ;
}
else {
  include "layout/Login.php" ;
  DisplayLogin() ;
}

?>
