<?php
/*
   Sends out a binary backup in the format accepted by the ../t_transfer
   utility. You only must configure a recipient.
*/

// define("BINBACKUP_TO", "you@example.com");   // who gets the backup
   define("BINBACKUP_TEXTONLY", 1);   // include ?binary entries or not


#-- go
if (defined("BINBACKUP_TO")) {

   // this is fixed
   define("EWIKI_TRANSFER_IDF", "EWBF00000025");    // file magic

   #-- start output
   $mail = "";
   $mail .= EWIKI_TRANSFER_IDF;

   $result = ewiki_db::GETALL(array("id","version","flags"));
   while ($row = $result->get()) {

      $id = $row["id"];
      for ($v=$row["version"]; $v>0; $v--) {

         $row = ewiki_db::GET($id, $v);

         if (BINBACKUP_TEXTONLY && !(EWIKI_DB_F_TEXT & $row["flags"]))
         {
            continue;
         }

         if ($row && ($row = serialize($row))) {
             $mail .= "\n" . strlen($row) . "\n" . $row;
         }

      }
   }
   
   #-- send
   $mail = gzencode($mail);
   $mail = base64_encode($mail);
   mail(
      BINBACKUP_TO,
      "[backup] " . EWIKI_NAME . ":",
      $mail,
       "Content-Transfer-Encoding: base64\n"
      ."Content-Encoding: deflate\n"
      ."Content-Type: application/x.vnd.ewiki.transfer-file\n"
      ."X-Mailer: ewiki/".EWIKI_VERSION."\n"
      ."From: ewiki@$_SERVER[SERVER_NAME]\n"
      ."Reply-To: trashbin@example.com\n"
   );
   unset($mail);

}


?>