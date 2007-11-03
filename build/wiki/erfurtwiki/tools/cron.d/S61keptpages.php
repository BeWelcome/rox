<?php
/*
   This controls how many days a page must be unchanged before one of the
   automatic deletion algorithms kills it.
*/

define("KEPTPAGES", 21);   // in days
$keptpages = KEPTPAGES * 24 * 3599;
$keepuntil = time() - $keptpages;

?>