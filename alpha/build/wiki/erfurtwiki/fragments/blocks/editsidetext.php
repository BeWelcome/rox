<?php

   #-- like in the ZendWiki example
   if ($data = ewiki_db::GET($editbar = "EditableSideText")) {
     echo ewiki_format($data["content"]);
     echo '<br>';
   }
   echo '<br><small><a href="'.ewiki_script("edit", $editbar).'">Add some text here...</a><br></small>' . "\n\n";

?>