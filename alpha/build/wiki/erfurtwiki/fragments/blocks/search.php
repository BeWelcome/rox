<?php

/*
   Prints out a small search <form>.
*/


if (true) {

   $pid = defined("EWIKI_PAGE_POWERSEARCH") ? EWIKI_PAGE_POWERSEARCH : EWIKI_PAGE_SEARCH;

   echo '<form action="' . url_script("", $pid)
      . '" method="GET" accept-charset="ISO-8859-1">'
      . '<input type="text" name="q" size="12">'
      . '<input type="submit" value="?">'
      . '</form>' . "\n";
}

?>