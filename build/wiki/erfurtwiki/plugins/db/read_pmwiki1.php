<?php

/*
   This DB plugin allows to import PmWiki flat file databases. It is hard
   to extend it as general backend, because that database format isn't all
   too suiteable for ewiki (_BINARY entries are impossible with it and we
   need random version access, not linear scan-through).
   Importing goes by only reading a given sub database (it's always divided
   into fragments). You eventually could access all by using "ext_subwiki"
   and complicating the yoursite wrapper script (must extract PMWIKI_GROUP
   from ?id= string much like PmWiki does itself).

   In the _DIRS constant you define the paths to PmWikis wiki.d/ data
   stores (there are typically two of them), where the first gets used
   as active (writeable) directory.
*/


#-- config
define("SUBWIKI_SEPARATOR", ".");
define("PMWIKI_DB_DIRS", "../pmwiki/wiki.d/:../pmiki/wikilib.d/");
define("PMWIKI_GROUP", "Main");


#-- run time
$subwiki = $_REQUEST["group"];
if (!$subwiki) {
   $subwiki = PMWIKI_GROUP;
}


#-- register
$ewiki_plugins["database"][0] = "ewiki_database_pmwiki";


#-- backend
class ewiki_database_pmwiki {

   var $dirs;
   var $group;

   function ewiki_database_pmwiki ($dirs=PMWIKI_DB_DIRS, $group=PMWIKI_GROUP) {
      $this->dirs = explode(":", $dirs);
      $this->group = $group;
   }


   #-- retrieve/decode pages
   function GET($id, $version=false) {

      static $last_id, $last;
      $diffs = array();
      $other = array();

      #-- speedy access
      if ($id == $last_id) {
      
         list($hash, $diffs, $other) = $last;
         
      }
      #-- generic read
      elseif ( ($fn = $this->FN($id)) && ($f = fopen($fn, "r")) )
      {
         $hash = array();
         $nl = "\n";
         do {
         
           #-- read line per line
           $line = fgets($f, 1<<18);  // 256K
           if ($l = strpos($line, "=")) {
              $field = trim(substr($line, 0, $l));
              $line = rtrim(substr($line, $l+1), "\n");
           }
           else {
              // broken file?
              $field = ":eof:";
              continue;
           }

           #-- special field           
           if ($field == "newline") {
              $nl = $line;
           }
           #-- old revision fields
           elseif (strpos($field, ":")) {
              list($fi,$n1,$n2,$n3) = explode(":", $field);
              if ($n2) {
                 $diffs[$n2] = $line;
              }
              else {
                 $other[$fi][$n1] = $line;
              }
           }
           #-- ordinary entries
           else {
              switch ($field) {
                 case "text":
                    $hash["content"] = $line;
                    break;
                 case "name":
                    if ($l = strpos($line, ".")) {
                       $l = substr($line, $l+1);
                    }
                    $hash["id"] = $line;
                    break;
                 case "time":
                    $hash["lastmodified"] = $line;
                    break;
                 case "host":
                    if (empty($hash["author"])) {
                       $hash["author"] = $line;
                    }
                    else {
                       $hash["author"] .= " ($line)";
                    }
                    break;
                 case "author":
                    if (empty($hash["author"])) {
                       $hash["author"] = $line;
                    }
                    else {
                       $hash["author"] = $line . " ($hash[author])";
                    }
                    break;
                 case "rev":
                    $hash["version"] = $line;
                    break;
                 default:
                    if ($field=="agent") {
                       $field = "user-agent";
                    }
                    $hash["meta"][$field] = $line;
              }
           }

         } while (!feof($f));
         fclose($f);
         
         $last_id = $id;
         $last = array($hash, $diffs, $other);
         
      }// read-in


      #-- validity check
      if ($hash["lastmodified"] && $hash["version"]) {

         #-- guess missing information
         $hash["created"] = filectime($fn);
         $uu = array_flip(array_keys($diffs));
         $last_diff_time = each($uu);
//         $hash["created"] = $last_diff_time;
         $hash["hits"] = 0;
         $hash["flags"] = EWIKI_DB_F_TEXT;
         $hash["refs"] = "\n\n";
         $hash["meta"] = (array)$hash["meta"];

         #-- if prev version requested
         if ($version && ($hash["version"] > $version)) {

            #-- apply patches until it matches requested {version}
            foreach ($diffs as $mtime=>$rpatch) {
               if ($hash["version"] == $version) {
                  break;
               }

               $hash["author"] = trim(
                  $others[$mtime]["author"] . ' ('.$others[$mtime]["host"].')'
               );
               $hash["content"] = $this->PATCH($hash["content"], $rpatch);
               $hash["version"]--;
            }
                
            if ($version < $hash["version"]) {
               $hash = false;
            }
         }
          
      }
      else {
         $hash = NULL;
      }

      return($hash);    
   }


   #-- works
   function GETALL($fields, $mask=0, $filter=0) { 
      $r = new ewiki_dbquery_result($fields);
      foreach ($this->LS() as $fn) {
         if ($data = $this->GET($fn)) {
            $r->add($data);
         } 
      }
      return $r;
   }


   #-- stub
   function HIT($id) {
   }


   #-- works
   function FIND($list) {
      $r = array();
      $ls = $this->LS();
      foreach ($list as $id) {
         $r[$id] = in_array($id, $ls);
      }
      return $r;
   }
   

   #-- search for given filename
   function FN($base_id) {
      $grp = $this->group;
      $dot = SUBWIKI_SEPARATOR;
      foreach ($this->dirs as $dir) {
         if (file_exists($fn = "$dir/$grp$dot$base_id")) {
            return $fn;
         }
      }
   }

   #-- returns list of all existing files
   function LS() {
      $cmp = $this->group . SUBWIKI_SEPARATOR;
      $ncmp = strlen($cmp);
      $r = array();
      foreach ($this->dirs as $dir) if ($dh = opendir($dir)) {
         while ($fn = readdir($fn)) if (!is_dir($fn)) {
            if (strncmp($fn, $cmp, $ncmp) == 0) {
               $r[] = substr($fn, $ncmp);
            }
         }
         closedir($dh);
      }
      return $r;
   }
   
   #-- for decoding older versions
   function PATCH($content, $rpatch) {
      $f1 = EWIKI_TMP."/ewiki-pmwiki-db-import-".crc32($content)."-".time();
      $f2 = $f1 . ".patch";
      fwrite($f = fopen($f1, "w"), $content) && fclose($f);
      fwrite($f = fopen($f2, "w"), $rpatch) && fclose($f);
      `patch $f1 $f2`;
      $content = fread($f = fopen($f1, "r"), 1<<18); fclose($f);
      unlink($f1);
      unlink($f2);
      return $content;
   }


} // end of class



?>