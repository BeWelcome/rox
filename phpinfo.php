<?php
require_once "lib/init.php";
require_once "layout/error.php";

// test if is logged, if not logged and forward to the current page
MustBeAdmin(); // need to be log
phpinfo();

?>
