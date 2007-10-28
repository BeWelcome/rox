<?php

/*
   All admin/ plugins depend upon $ewiki_ring==0 to be set, and else
   refuse to work (so ordinary users cannot damage the database).
   This is now an examplary trigger which sets that state variable,
   it compares against the HTTP From: request header field, which is
   a highly insecure approach for access protection and won't work
   with current main stream browser (that do not support it). But
   instead of SERVER_ADMIN you could use a complicated fake email
   address for example.
*/

$server_admin = "passwordlikestring@example.com";
$server_admin = $_SERVER["SERVER_ADMIN"];

if ($_SERVER["HTTP_FROM"] == $server_admin) {
   $ewiki_ring = 0;
}

?>