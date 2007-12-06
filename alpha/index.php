<?php

/* 
 *  This path should not be accessible through a browser.
 *  This file is deprecated and is just here for legacy reasons.
 */

header("HTTP/1.0 301 Moved Permanently");
header("Location: htdocs/");
header("Connection: close");
exit;

?>
