<?php
/*
   Implements an EMail-to-Wiki gateway, if you assign it a POP3 / IMAP
   account and/or MBOX spool file to read from. An mail sent to there
   will create a new page or append to an existing one, if either the
   To: field or the Subject: contains a WikiWord (everything else will
   get written onto "SpammerSubmissions"). Enriched mail bodys will be
   checked for convertability into WikiMarkup, but HTML emails and any
   multipart/* objects will get rejected outright.
*/


// define("MAIL2WIKI_POP3", "mailuser:pw@mail.example.com");  // for POP3+IMAP
// define("MAIL2WIKI_MBOX", "/var/spool/mail/towiki");  // from local mbox file
define("MAIL2WIKI_SPAM", "SpammerSubmissions");  // to save rejected stuff onto (as-is, in rfc822 format); you could also set this to false or zero to disable
define("MAIL2WIKI_ATONCE", 50);  // max amount of mails to process in one run


#-- start
$incoming = array();

#-- check remote mail account
if (defined("MAIL2WIKI_POP3")) {

   #-- open connection
   $username = strtok(MAIL2WIKI_POP3, ":");
   $password = strtok("@");
   $mailserver = strtok("/");
   ($box = strtok(".")) or
   ($box = "INBOX");
   if (($mx = imap_open('{'."$mailserver/pop3:110}$box", $username, $password))
   or ($mx = imap_open('{'."$mailserver}$box", $username, $password)))
   {
      echo "[$cron]: opened connection to $mailserver to check for submissions\n";
   
      #-- loop through all messages
      $msg_count = imap_num_msg($mx);
      echo "[$cron]: $msg_count found\n";
      if ($msg_count > MAIL2WIKI_ATONCE * 1.1) {
         $msg_count = MAIL2WIKI_ATONCE;
         echo "[$cron]: proeccsing only $msg_count now\n";
      }
      for ($msg=1; $msg<=$msg_count; $msg++) {

         #-- get complete msg
         $mbox = imap_fetchheader($server, $msg, FT_PREFETCHTEXT);
         imap_delete($server, $msg);

         #-- into our spool array
         $incoming[] = $mbox;
      }

      #-- close, delete messages
      imap_expunge($mx);
      imap_close($mx);
   }
}


#-- read on Unix server
if (defined("MAIL2WIKI_MBOX")) {
   // open for read-write or forget it
   if ($f = fopen(MAIL2WIKI_MBOX, "rw")) {
      flock($f, LOCK_EX);

      #-- try to read 2MB file,
      #   but fail if we really can do that (this is far too large!!)
      $mbox = fread($f, 1<<21);
      if (strlen($mbox) >= (1<<21)) {
         unset($mbox);
      }
      
      #-- split mbox file into individual messages, append to $incoming[] list
      elseif ($mbox
      and ($mbox = preg_split("/^From [^\s]+@[^\s]+ \w\w\w?,? \w\w\w \d\d \d\d:\d\d:\d\d \d\d\d\d$/m",
      $mbox)) ) {
         #-- append to processing list
         $incoming = array_merge($incoming, $mbox);
         unset($mbox);
      
         #-- clear inbox file completely
         fseek($f, 0);
         ftruncate($f, 0);
      }
      
      #-- go away
      flock($f, LOCK_UN);
      fclose($f);
   }
}


#-- store it --------------------------------------------------------------
if ($incoming) {
   echo "[$cron]: storing " . count($incoming) . " pages\n";
   $rx_wiki = "/^([".EWIKI_CHARS_U."]+[".EWIKI_CHARS_L."]+){2,})$/";
   
   foreach ($incoming as $mbox) {
   
      #-- convert line breaks
      $mbox = preg_replace('/(\r\n|\r)/', "\n", $mbox);
      $page = substr($mbox, strpos($mbox, "\n\n")+2);  // content body
      
      #-- check for WikiWord
      $ok = 0;
      if (preg_match('/^To:\s*"(.+)"|^To:\s*<?(.+?)@/i', $mbox, $uu)) {
         if ($id=$uu[1]) {
            $ok = preg_match($rx_wiki, $uu[1]);
         }
         elseif ($id=$uu[2]) {
            $ok = preg_match($rx_wiki, $uu[2]);
         }
      }
      if (!$ok && preg_match('/^Subject:[ ]*([^\s]+)[ ]*$/i', $mbox, $uu)) {
         $ok = preg_match($rx_wiki, $id=$uu[1]);
      }
      
      #-- check content-type
      $ok = $ok && preg_match('#^Content-Type:\s*([^.+0-9_a-z/]+)#i', $mbox, $uu);
      if ($ok) {
         $ct = strtok(strtolower($uu[1]), "/");
         $st = strtok("?");
         if ($ct != "text") {
            $ok = 0;
         }
         else {
            if (($st == "plain") || strstr($st, "wiki")) {
               $ok = 1;
            }
            elseif ($st == "enriched") {
               // how to decode that?
            }
            else {
               $ok = 0;
            }
         }
      }

      
      #-- save full msg as spam
      if (!$ok) {
         echo "[$cron]: spam/unwanted content detected\n";
         if (MAIL2WIKI_SPAM) {
            ewiki_db::APPEND(MAIL2WIKI_SPAM, "\n----\n$mbox\n");
         }
      }
      
      #-- save as new Wiki page
      elseif (!ewiki_db::GET($id)) {
         ewiki_db::APPEND($id, $page);
      }
      #-- as comment to existing one
      else {
         if (preg_match("/^From: [^\n]+/m", $mbox, $uu)) {
            $from = from_mail_or_link($uu[1]);
            $from = "[$from]: ";
         }
         else {
            $from = "----\n\n";
         }
         #-- do
         echo "[$cron]: storing mail from '$from' onto page '$id'\n";
         $page = trim($page);
         ewiki_db::APPEND($id, "\n\n$from$page\n");
      }

   }
}
unset($mbox);
unset($incoming);


#-- return a WikiPageUserName or [name|email@...] string back
function from_mail_or_link($from) {
   $from = strtr($from, "<>\"\'", "    ");
   $l = strpos($from, "@");
   if ($l = strrpos($from, " ", $l)) {
      $name = substr($from, 0, $l);
      $mail = substr($from, $l+1);
      $wiki = str_replace(" ", "", $name);
      if (ewiki_db::GET($wiki)) {
         return($wiki);
      }
      else {
         $mail = trim($mail);
         return("$wiki|$email");
      }
   }
   else {
      return($from);
   }
}

?>