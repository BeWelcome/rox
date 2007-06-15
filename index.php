<?php
require_once "lib/init.php";
require_once "layout/index.php";

if (GetParam("IndexMicha","no")=="") {
  DisplayIndex();
  exit(0);
} 

if (IsLoggedIn()) {
/*  DisplayIndexLogged($_SESSION["Username"]); */
  DisplayIndex();
}
else {
  DisplayIndex();
}
?>
