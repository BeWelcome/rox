<?php
/*
   Sends out a backup MIME/multipart archive containing only the latest
   versions of all text pages. This cannot be easily deciphered by an
   ordinary mail reader, and there is yet no commandline utility for
   this kind of mime archives; but it is anyhow a good format for backup
   purposes (because an import tool is quickly made).
   Please see also "S92binbackup" which sends backups already useful with
   the t_transfer utility.
*/

// define("MIMEBACKUP_TO", "you@example.com");   // who gets the backup


#-- go
if (defined("MIMEBACKUP_TO")) {

   #-- collect pages
   $parts = array();
   echo "[$cron]: Searching _TEXT pages to generate MIME/multipart backup mail...\n";

   #-- search for _TEXT pages
   $result = ewiki_db::GETALL(array("id", "version", "flags"));
   while ($row = $result->get(0, 0x1037)) {

      $row = ewiki_db::GET($row["id"]);
      if (!$row || !$row["id"] || !$row["content"]) {
         continue;
      }

      #-- add (we could run out of memory with this!)
      $parts[] = $row;
   }
   echo "[$cron]: Found " . count($parts) . " pages\n";

   #-- send
   $BND = "cut-here-".md5(time())."-cut-here";
   $mail = "--$BND\n"
      ."Content-Type: text/plain\n"
      ."\n"
      ."This is a backup of " . EWIKI_NAME . ". All text pages are contained\n"
      ."in this mail as MIME/multipart embedded files; use a better mail reader\n"
      ."if you cannot decompose it easily.\n"
      ."\n--$BND\n"
      . multipart_parallel($parts, $boundary, $inj_header)
      . "\n--$BND--\n";
   unset($parts);
   echo "[$cron]: Sending backup data to " . MIMEBACKUP_TO . "\n";
   mail(
      MIMEBACKUP_TO,
      "[backup] " . EWIKI_NAME . ":",
      $mail,
      "Content-Type: multipart/mixed; boundary=$BND\n"
      ."X-Mailer: $ewiki_config[ua] run-parts/$cron\n"
      ."From: ewiki@$_SERVER[SERVER_NAME]\n"
      ."Reply-To: trashbin@example.com\n"
   );
   unset($mail);

}



#-- produces a (probably invalid) multipart stream
function multipart_parallel(&$parts) {
   global $ewiki_t;
   $boundary = preg_replace("/(........)/", '$1-', md5(serialize($parts)))
             . "ewiki-mimebackup";   //@FIXME: must be checked for being unique
   $text = "";
   foreach ($parts as $i=>$row) {
      $fn = urlencode($row["id"]);
      $created = gmstrftime($ewiki_t["C"]["DATE"], $row["created"]);
      $lm = gmstrftime($ewiki_t["C"]["DATE"], $row["lastmodified"]);
      $text .= "--$boundary\n"
             . "Content-Type: text/x-wiki; variant=ewiki; charset=iso-8859-1\n"
             . "Content-Disposition: inline; filename=\"$fn\"\n"
             . "X-Flags: $row[flags]\n"
             . "Content-Version: $row[version]\n"  // HTTP header, but hey!
             . "X-Created: $created\n"
             . "Last-Modified: $lm\n"  // also only allowed in HTTP exactly
             . "\n"
             . $row["content"]
             . "\n";
      unset($parts[$i]);
   }
   $text .= "--$boundary--\n";

   #-- encode
   $text = wordwrap(base64_encode(gzencode($text)), 78, "\n", 1);

   #-- prep headers
   $text
         = "Content-Type: x-multipart/parallel; boundary=$boundary\n"
         . "Content-Disposition: attachment; filename=".EWIKI_NAME.".mar.gz\n"
         . "Content-Encoding: gzip\n"
         . "Content-Transfer-Encoding: base64\n"
         . "\n" . $text;

   return($text);
}  


?>