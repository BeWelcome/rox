<?php
/*
   Instead of using the default Wiki list from 'intermap.php', you can
   use this plugin to load an "intermap.txt" file, like every other Wiki
   engine does.
   Place it into _this_ directory, into EWIKI_VAR or the ewiki basedir.
*/

if (file_exists($fn = EWIKI_VAR."/intermap.txt")
 or file_exists($fn = EWIKI_BASE_DIR."/intermap.txt")
 or file_exists($fn = dirname(__FILE__)."/intermap.txt"))
{
   foreach (file($fn) as $uu) {
      $ewiki_config["interwiki"][strtok($uu, " =\t:")] = trim(strtok("\r\n"));
   }
}

?>