<?php

/*
   This plugin encapsulates the database layer to fragment your database
   into several pieces. All pages from then will be created in separate
   namespaces. The global '$subwiki' variable makes the internal pagename
   prefix and must be set by yoursite.php (the layout wrapper).

   The initial pages are created as usual, but then exist multiple times
   in the database ("WikiOne:EditThisPage" and "WikiTwo:EditThisPage").
   You should preferrably use the colon as separator, as it perfectly
   matches the InterWiki syntax; define the interwiki monikers and
   wrappers at different URLs correctly to get a closed system.
*/

define("SUBWIKI_SEPARATOR", ":");


$ewiki_plugins["init"][] = "ewiki_database_subwiki_init";
# initialization timing is difficult here, this breaks binary upload support


function ewiki_database_subwiki_init()
{
   global $ewiki_plugins, $subwiki, $ewiki_db;

   #-- only engage wrapper if subwiki filtering requested
   if ($subwiki) {
      $ewiki_db = & new ewiki_database_subwiki($ewiki_db);
   }
}


#-- wrapper
class ewiki_database_subwiki {

   var $db;
   var $dot = SUBWIKI_SEPARATOR;
   
   function ewiki_database_subwiki(&$backend) {
      $this->db = & $backend;
   }


   function GET($id, $version=false) {
      global $subwiki;
      $id = $subwiki . $this->dot . $id;
      $r = $this->db->GET($id, $version);
      if ($r) {
         $r["id"] = substr($r["id"], strlen($subwiki) + 1);
      }
      return($r);
   }


   function HIT($id) {
      $id = $GLOBALS["subwiki"] . $this->dot . $id;
      return $this->db->HIT($id);
   }


   function DELETE($id, $version=false) {
      $id = $GLOBALS["subwiki"] . $this->dot . $id;
      return $this->db->DELETE($id, $version);
   }


   function WRITE($hash, $overburn=0) {
      $hash["id"] = $GLOBALS["subwiki"] . $this->dot . $hash["id"];
      return $this->db->WRITE($hash, $overburn);
   }


   function INIT() {
      $this->db->INIT();
   }

      
   function FIND($list) {
      global $subwiki;
      if ($subwiki) {
         $n = strlen($subwiki);
         foreach ($list as $i=>$s) {
            $list[$i] = $subwiki . $dot . $s;
         }
         $e = $this->db->FIND($list);
         $r = array();
         foreach ($e as $s=>$x) {
            $r[substr($s, $n+1)] = $x;
         }
         return($r);
      }
      return $this->db->FIND($list);
   }


   function SEARCH($field, $content, $ci=1, $regex=0, $mask=0, $filter=0) {
      
         $r = $dbf($func, $args, $f1, $f2);
         foreach ($r->entries as $i=>$d) {
            if (is_array($d) && (0==strncmp($d["id"], $subwiki, $n))) {
               $r->entries[$i]["id"] = substr($d["id"], $n+1);
            }
            elseif (is_string($d) && (0==strncmp($d, $subwiki, $n))) {
               $r->entries[$i] = substr($d, $n+1);
            }
            else {
               unset($i);
            }
         }
   }
   
   function GETALL($fields, $mask=0, $filter=0) {
   }
         


} // end of class


?>