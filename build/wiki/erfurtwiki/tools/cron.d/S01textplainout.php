<?php
/*
   Signalise that we only output plain text here, so you can start up
   the run-parts.php script with your browser and see what's going on.
*/

#-- but only if we got run with a direct URL
if (!$_GET && !$_POST) {
   header("Content-Type: text/plain");
   ini_set("html_errors", 0);
   ob_implicit_flush(1);
}

?>