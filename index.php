<?php
require_once "lib/init.php";
include "layout/index.php";

if (IsLoggedIn()) {
  DisplayIndexLogged($_SESSION["Username"]);
}
else {
  DisplayNotLogged();
}
?>
