<?php

   site_prot();

#  This include() script implements anti-leech and anti-bot functionality,
#  and can probably be used with any web site, and is especially not tied
#  to ewiki. Its approach isn't proxy friendly, and therefore will likely
#  knock out AOL users and the like.
#  This script can be used concurrently on a shared webserver (common for
#  nowadays web space providers), because the the version numbers protect
#  from broken lock data - and after all sharing the data only can benefit
#  this protection system.
#
#  You can knock out a user (if you detect unwanted behaviour) by just
#  calling site_prot(+1); from anywhere in yoursite.


function site_prot($bad_behaviour_detected=0) {

   #-- config
   $max_hits = 1000;           # absolute hit-limit per IP
   $rm_stale_locks = 120;      # seconds, after which lock files invalidate
   $delay = 3;                 # request slow down time (bot brake)
   $a_ratio = 5/1;             # max allowed accesses per second
   $ignore_humans = 1;         # humans may perform all requests
$friends="/|127.0.0.1|216.239.*.*|64.68.*.*|204.123.*.*|204.152.*.*|128.177.*.*||";

   #-- bots rarely send Cookies, and also avoid the POST request method
   $not_a_bot = count($_COOKIE)
             || ($_SERVER["REQUEST_METHOD"] == "POST");

   #-- this allows us to ignore non-ewiki-page-requests
   $ignore_hit = (@$_REQUEST["binary"])  // image and _BINARY database entries
             || (@$_REQUEST["q"])        // PowerSearch
             || (@$_REQUEST["**************"]);
   if ($ignore_hit) {
      return(NULL);
   }

   #-- directory for lock files
   (defined("EWIKI_TEMP") and ($tmp=EWIKI_TEMP))
   or ($tmp = @$_SERVER["TEMP"]) or ($tmp = @$_SERVER["TEMP_DIR"])
   or ($tmp = "/tmp");
   $tmp .= "/site_prot/";
   if (!file_exists($tmp)) {
      mkdir($tmp, 0770);
   }

   #-- aggressor
   ($ip = $_SERVER["REMOTE_ADDR"])
   or ($ip = $_SERVER["X_FORWARDED_FOR"]);   # or even start traceroute here?
   $half_ip = substr($ip, 0, @strpos($ip, ".", 4));   # (incorrect, but works for the default $friends list)
   if (strpos($friends, "|$ip|") || strpos($friends, "|$half_ip.*.*|")) {
      return(+5);
   }

   #-- data file
   $lockfile = $tmp . strtr($ip, ":", "+");
   $data = array(
      1,           // hits
      time(),      // last_modified
      time(),      // creation_time
      0,           // bad_guest
      0,           // is_no_bot
   );
   $data[3] |= ((int)$bad_behaviour_detected);
   $data[4] |= ($not_a_bot?1:0);

   #-- read info about guest
   if (file_exists($lockfile)) {
      $last_access = filemtime($lockfile);
      $first_access = filectime($lockfile);

      #-- remove old lockfile
      if (($ignore_humans && $not_a_bot) || (@$_REQUEST["site_prot_unlock"])
      || ($last_access + $rm_stale_locks < time())) {
         unlink($lockfile);
         return(+0);
      }

      #-- read-in and update data
      if ($f = fopen($lockfile, "r+")) {
         if ($data = unserialize(fread($f, 65536))) {
            $data[0] += 1;
            $data[1] = time();
            $data[4] |= ($not_a_bot?1:0);
            fseek($f, 0);
            fwrite($f, serialize($data));
         }
         fclose($f);
      }

      #-- keep annoying the bad guys
      if ($data[3]) {
         site_prot_trap($delay << 1);
      }

      #-- check for too many requests,
      #   lockfile usually were already deleted after that many requests
      #   (this is the proxy trap)
      if ($data[0] >= $max_hits) {
         site_prot_trap($delay);
      }

      #-- ignore following checks for humans
      if ($ignore_humans && $data[4]) {
         return(+1);
      }

      #-- too many requests per time,
      #   measured according to the time slice the current lockfile exists
      if ($data[0] > ($data[1]-$data[2]) / $a_ratio) {
         site_prot_trap($delay);
      }

      return(+2);
   }
   else {
      if ($f = fopen($lockfile, "w")) {
         fwrite($f, serialize($data));
         fclose($f);

         return(+1);
      }
   }

   return(-1);
}


#-- called if we want to stop the client
function site_prot_trap($delay=3) {

   #-- we want to slow down the bot, but not ourselfes
   ignore_user_abort(0);
   set_time_limit(30);

   #-- disable any establish output buffering
   if (function_exists("ob_end_clean")) {
      while (function_exists("ob_get_level") && ob_get_level() || ob_get_length()) {
         ob_end_clean();
      }
   }

   #-- send out a warning message for humans (to also allow for lock removal)
   echo<<<EOF
<!--[site_prot_trap()]-->
<h1>Lock</h1>
Your <a href="http://google.com/search?q=IP+address">IP</a>
has been locked and you cannot make further<br>
requests to this site.<br>
<br>
<form action="{$_SERVER[REQUEST_URI]}" method="POST" enctype="application/x-www-form-urlencoded">
<input type="checkbox" name="site_prot_unlock" value="true"> No, please
<input type="submit" name="site_prot_unlock_button" value="unlock me!!">
</form>
EOF;
   flush();

   #-- should we flood our syslog here?
   /*
     ...
   */

   #-- slow down the bot, and exit the script
   sleep($delay);
   die(34);
}


?>