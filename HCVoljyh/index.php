<?php
include "lib/dbaccess.php";
include "layout/index.php";

if (IsLogged()) {
  DisplayIndexLogged($_SESSION["Username"]);
}
else {
  DisplayNotLogged();
}
?>
