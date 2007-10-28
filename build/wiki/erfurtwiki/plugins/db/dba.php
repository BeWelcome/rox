<?php

## This database plugin utilizes PHPs "dba" extension. You can also use
#  the older and now deprecated "dbm" extension, if you just load the
#  plugins/lib/fakedba.php sript.
#
## Unlike the flat file databases all data is stored in one binary file,
#  as defined by the EWIKI_DBA constant. The filename extension of this
#  tells which dba database type to use:
#   - .flatfile      almost always supported by PHP, recommended
#   - .db3           most wide-spread variant
#   - .db2           is also still in wide use
#   - .gdbm          from the GNU project
#   - .db4 or .db5   if you have enough memory to waste
#   - .ndbm          also not the best
#   - .dbm           very old format (the original BerkelyDB)
#   - .inifile       is not binary safe, avoid or disable compression
#
## That database file will get opened automatically when needed (unlike
#  most other ewiki db interfaces). EWIKI_DBFILES_GZLEVEL says how much
#  time to spend on compressing the pages content.
#
## Try to avoid any BDB variants from Sleepycat - they haven't managed to
#  get something backwards compatible until today, and each version sucks
#  more memory. Use .flatfile if you can, because it is supported by all
#  PHP versions (regardless of how the interpreter was compiled).


#-- config
// define("EWIKI_DBA", "/tmp/mywiki.flatfile");  // or use extension .db2
// define("EWIKI_DBFILES_GZLEVEL", 0);   // if you want .inifile format (this is not binary safe - also disable image upload then!)


#-- plugin registration
$ewiki_plugins["database"][0] = "ewiki_database_dba";



#-- backend
class ewiki_database_dba {

   var $handle = false;
   var $gz = EWIKI_DBFILES_GZLEVEL;

   function ewiki_database_dba() {
      $this->handle = ewiki_database_dba::CONNECT(EWIKI_DBA);
   }



   function GET($id, $version=false) {
      if (!$version && !($version = $this->LASTVER($id))) {
         return;
      }
      if ($r = dba_fetch("$id.$version", $this->handle)) {
         if ($uu = gzuncompress($r)) {
            $r = $uu;
            unset($uu);
         }
         if ($r = unserialize($r)) {
            return($r);
         }
      }
   }


   function WRITE($hash, $overwrite=0) {
      $key = $hash["id"].".".$hash["version"];
      $ex = dba_exists($key, $this->handle);
      if (!$overwrite && $ex) {
         return;
      }
      $hash = serialize($hash);
      if ($this->gz) {
         $hash = gzcompress($hash, $this->gz);
      }
      if ($ex) {
         $r = dba_replace($key, $hash, $this->handle);
      } else {
         $r = dba_insert($key, $hash, $this->handle);
      }
      return($r);
   }


   function HIT($id) {
      $key = "$id.1";
      if ($r = unserialize(gzuncompress(dba_fetch($key, $this->handle)))) {
         $r["hits"] += 1;
         dba_replace($key, gzcompress(serialize($r), $this->gz), $this->handle);
      }
   }


   function FIND($list) {
      $r = array();
      foreach ($list as $id) {
         if (dba_exists("$id.1", $this->handle) || $this->LASTVER($id)) {
            $r[$id] = 1;
            if (EWIKI_DBFF_ACCURATE) {
               $row = $this->GET($id);
               if ($row["meta"]) {
                  $r[$id] = $row["meta"];
                  $r[$id]["flags"] = $row["flags"];
               } else {
                  $r[$id] = $row["flags"];
               }
            }
         }
      }
      return($r);
   }


   function GETALL($fields, $mask=0, $filter=0) {
      $r = new ewiki_dbquery_result($fields);
      foreach ($this->ALLFILES() as $id) {
         $row = $this->GET($id);
         $r->add($row);
      }
      return($r);
   }


   function SEARCH($field, $content, $ci="i", $regex=0, $mask=0, $filter=0) {
      if ($ci && !$regex)  {
         $content = strtolower($content);
      }
      $r = new ewiki_dbquery_result(array($field));
      foreach ($this->ALLFILES() as $id) {
         $page = ewiki_db::GET($id);
         if ($regex) {
            $check = preg_match("\007$content\007$ci", $page[$field]);
         }
         elseif ($ci) {
            $check = strpos(strtolower($page[$field]), $content)!==false;
         }
         else {
            $check = strpos($page[$field], $content)!==false;
         }
         if ($check) {
            $r->add($page);
         }
      }
      return($r);
   }


   function DELETE($id, $version) {
      dba_delete("$id.$version", $this->handle);
   }


   function INIT() {
      if (!$this->handle) {
         die("dba database not writable!\n");
      }
   }


//@FIXME: uh, oh, bad
// but everything else would be VERY VERY slow
   function LASTVER($id) {
      $n = 1;
      while (dba_exists("$id.$n", $this->handle)) {
         $n++;
      }
      return(--$n);
   }


   function ALLFILES() {
      $id = dba_firstkey($this->handle);
      while ($id != false) {
         $p = strrpos($id, ".");
         $id = substr($id, 0, $p);
         $r[$id] = $id;
         $id = dba_nextkey($this->handle);
      }
      $r = array_values($r);
      return($r);
   }


   #-- open dba connection
   function CONNECT($dba_file) {
      $avail = array_reverse(dba_handlers());
      $try = substr($dba_file, strrpos($dba_file, ".") + 1);
      $try = array_merge(array($try, "gdbm", "db7", "db3", "db2", "flatfile", "db6", "db4", "db5", "ndbm", "dbm"), $avail);
      $handle = false;
      foreach ($try as $dba_handler) {
         if (in_array($dba_handler, $avail)) {
            foreach (array("w", "c", "n") as $mode) {
               if ($handle = dba_open($dba_file, $mode, $dba_handler)) {
#echo "USING($dba_handler), ";
                  if ($mode != "w") {
                     dba_close($handle);
                     $handle = dba_open($dba_file, "w", $dba_handler);
                  }
                  break 2;
               }
            }
         }
      }
      return($handle);
   }


} // end of class


?>