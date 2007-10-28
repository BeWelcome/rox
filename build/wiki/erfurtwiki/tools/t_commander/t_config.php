<?php
  header("Content-Type: text/html; charset=iso-8859-1");
  

  chdir("..");
  include("t_config.php");
  
  

  #-- extra utility code
  function flag_text($flags) {
     $str = array(
        EWIKI_DB_F_HTML => "H",
        EWIKI_DB_F_HIDDEN => "h",
        EWIKI_DB_F_MINOR => "m",
        EWIKI_DB_F_READONLY => "r",
        EWIKI_DB_F_WRITEABLE => "w",
        EWIKI_DB_F_EXEC => "x",
        EWIKI_DB_F_APPENDONLY => "a",
        EWIKI_DB_F_DISABLED => "d",
        EWIKI_DB_F_PART => "P",
        EWIKI_DB_F_SYSTEM => "Z",
        EWIKI_DB_F_BINARY => "B",
        EWIKI_DB_F_TEXT => "T",
     );
     $s = "";
     foreach ($str as $bit=>$add) {
        if ($flags & $bit) {
           $s .= $add;
        }
     }
     if (!strlen($s)) {
        $s = "0";
     }
     return $s;
  }
  
//  ewiki_db::$readonly = true;
?>