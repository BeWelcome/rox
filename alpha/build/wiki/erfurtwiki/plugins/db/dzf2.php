<?php

/*
   (lacks a fancy name, but DZF2 stands for dir/zlib/files/version2)
   
   This plugin implements a more advanced flat-file database backend,
   which is designed to be faster than the older db/flat_files and to
   use the compressed/serialized format per default. Additionally it
   works case-insensitive (ONLY!) even on Unix filesystems and the
   filename encoding is engaged per default.
   (summary: this database backend is optimized but inconfigureable)
   
   things you may do:
   - the CACHE file can be deleted, it is auto-recreated when needed
     (but this should not be necessary, except after a server crash)
*/


#-- configuration settings
// define("EWIKI_DBFILES_DIRECTORY", "pages/");  # like with plugins/db/flat_files
define("EWIKI_DBFF_ACCURATE", 1);   # makes FIND call return image sizes
define("DZF2_HIT_COUNTING", 1);     # enables hit-counting


#-- hard-coded settings (don't try to change this)
define("EWIKI_CASE_INSENSITIVE", true);   # this is hard-coded in here, so don't disable or everything would break
$ewiki_plugins["database"][0] = "ewiki_database_dzf2";


#-- db interface backend
class ewiki_database_dzf2 {

   var $dir = EWIKI_DBFILES_DIRECTORY;
   var $gz = EWIKI_DBFILES_GZLEVEL;



   function GET($id, $version=false) {
      if (!$version && !($version = $this->LASTVER($id))) {
         return;
      }
      #-- read file      
      $dbfile = $this->FN("$id.$version");
      $lock = fopen($dbfile, "rb");
      flock($lock, LOCK_SH);
      if ($f = @gzopen($dbfile, "rb")) {
         $r = unserialize(gzread($f, 1<<21));
         gzclose($f);
      }
      flock($lock, LOCK_UN);
      fclose($lock);
      if ($r && DZF2_HIT_COUNTING) {
         if ($f = @fopen($this->FN("$id.hits"), "rb")) {
            $r["hits"] = trim(fread($f, 10));
            fclose($f);
         }
      }
      return($r);
   }


   function WRITE($hash, $overwrite=0) {
      $id = $hash["id"];
      $version = $hash["version"];
      $dbfile = $this->FN("$id.$version");
      if (!$overwrite && file_exists($dbfile)) {
         return;
      }
      #-- read-lock
      if (file_exists($dbfile)) {
         $lock = fopen($dbfile, "rb");
         flock($lock, LOCK_EX);
      }
      #-- open file for writing, secondary lock
      if ($f = gzopen($dbfile, "wb".$this->gz)) {
         if (!lock) {
            flock($f, LOCK_EX);
         }
         $r = gzwrite($f, serialize($hash));
         gzclose($f);
         $this->SETVER($id, $version);
         $this->CACHE_ADD($id, $version);
         return(1);
      }
      #-- dispose lock
      if ($lock) {
         flock($lock, LOCK_UN);
         fclose($lock);
      }
      return(0);
   }


   function FIND($list) {
      $r = array();
      foreach ($list as $id) {
         $fn = $this->FN($id);
         if (file_exists($fn)) {
            $r[$id] = 1;
            if (EWIKI_DBFF_ACCURATE && (strpos($id, ":") || strpos($id, "."))) {
               $uu = $this->GET($fn);
               if ($uu["meta"]) {
                  $r[$id] = $uu["meta"];
                  $r[$id]["flags"] = $uu["flags"];
               } else {
                  $r[$id] = $uu["flags"];
               }
            }
         }
      }
      return($r);
   }


   function GETALL($fields, $mask=0, $filter=0) {
      $r = new ewiki_dbquery_result($fields);
      $r->entries = $this->ALL();
      return($r);
   }


   function SEARCH($field, $content, $ci="i", $regex=0, $mask=0, $filter=0) {
      if ($ci && !$regex) {
         $content = strtolower($content);
      }
      $r = new ewiki_dbquery_result($args);
      
      #-- fast title search
      if (!$mask && ($field == "id")) {
         foreach ($this->ALL() as $id) {
            if ($regex && preg_match("\007$content\007$ci", $id)
            or $ci && strpos(strtolower($id), $content)
            or !$ci && strpos($id, $content) )
            {
               $this->entries[] = $id;
            }
         }
      }
      #-- must load all files from disk
      else {
         foreach ($this->ALL() as $id) {
            $row = ewiki_database_dzf2::GET($id);
            if ($mask && ($filter != ($row["flags"] & $mask))) {
               continue;
            }
            if ($regex && preg_match("\007$content\007$ci", $row[$field])
            or $ci && strpos(strtolower($row[$field]), $content)
            or !$ci && strpos($row[$field], $content) )
            {
               $r->add($row);
            }
         }
      }
      return($r);
   }


   function DELETE($id, $version) {
      $fn = $this->FN($id);
      unlink("$fn.$version");
      if (!$this->LASTVER($id)) {
         @unlink("$fn");
         @unlink("$fn.hits");
         $this->ALL("_PURGE");
      }
   }


   function INIT() {
      if (!is_writeable($this->dir) || !is_dir($this->dir)) {
         mkdir($this->dir)
         or die("\nERROR in ewiki/db/dzf2: 'database' directory '$this->dir' is not world-writable (do the chmod 777 thing)!\n");
      }
      for ($c=97; $c<=122; $c++) { @mkdir($this->dir.'/'.chr($c)); }
      for ($c=48; $c<=57; $c++) { @mkdir($this->dir.'/'.chr($c)); }
      @mkdir($this->dir."/@");
   }



   #---------------------------------------------------------- internal ---

   function FN($id) {
      $id = ewiki_lowercase($id);
      $c0 = $id[0];
      if (($c0>="a") && ($c0<="z") || ($c0>="0") && ($c0<="9")) {
         $letter = $c0;
      }
      else {
         $letter = "@";
      }
      return(  $this->dir . "/$letter/" . rawurlencode($id)  );
   }

   function LASTVER($id, $count_through=0) {
      $ver = NULL;
      $fn = $this->FN($id);
      if (file_exists($fn) && ($f = fopen($fn, "rb"))) {
         $ver = 0 + trim(fgets($f, 10));
         fclose($f);
      }
      return($ver);
   }

   function SETVER($id, $version) {
      $fn = $this->FN($id);
      if ($f = fopen($fn, "wb")) {
         fwrite($f, "$version", 10);
         fclose($f);
      }
      else {
         echo "\nERROR in ewiki/db/dzf2: could not write version cache file for '$id'\n";
      }
   }

   // reads page list cache file
   function ALL($rewrite=0) {
      $CACHE = $this->dir."/CACHE";

      #-- generate cache
      if (!file_exists($CACHE) || $rewrite) {
         $r = $this->ALL_WALK();
         if ($f = fopen($fn, "wb")) {
            flock($f, LOCK_EX);
            fwrite($f, "00000027_ewiki_DZF2_database_CACHE_FILE (do not edit!)\n"
                  . implode("\n", $r) . "\n");
            flock($f, LOCK_UN);
         }
      }
      #-- read
      elseif ($f = fopen($CACHE, "r")) {
         flock($f, LOCK_SH);
         $r = explode("\n", fread($f, 1<<21));
         flock($f, LOCK_UN);
         unset($r[0]);   // header
         array_pop($r);
      }
      $f and fclose($f);

      return($r);
   }

   // adds one entry to the cache file
   function CACHE_ADD($id, $version) {
      $CACHE = $this->dir."/CACHE";
      if (($version >= 1) && ($f = fopen($CACHE, "ab"))) {
         flock($f, LOCK_EX);
         fwrite($f, ewiki_lowercase($id) . "\n");
         flock($f, LOCK_UN);
         fclose($f);
      }
   }

   // scans through all dirs to detect existing pages
   function ALL_WALK() {
       $r = array();
       $main = opendir($this->dir);
       while ($sub = readdir($main)) {
          if ((strlen($sub)==1) && ($sub[0]!=".") && is_dir($this->dir."/$sub")) {
             $sub = $this->dir . "/" . $sub;
             $dh = opendir($sub);
             while ($fn = readdir($dh)) {
                if (($fn[0] != ".") && (strpos($fn, ".hits") != strlen($fn)-5)) {
                   $fs = filesize($sub ."/". $fn);
                   if ($fs && ($fs < 10)) {
                      $r[] = rawurldecode($fn);
             }  }  }
          }
       }
       return($r);
    }


    function HIT($id, $add=+1)
    {
       if (!DZF2_HIT_COUNTING) {
          return;
       }
       $dbfile = $this->FN($id) . ".hits";
       
       #-- open, read
       if ($fr = @fopen($dbfile, "r")) {
          flock($fr, LOCK_SH);
          $r = trim(fgets($fr, 10));
       }
       else {
          $r = 0;
       }
       #-- update
       if ($add) {
          if ($fr) {
             flock($fr, LOCK_EX);
          }
          $r += $add;
          $fw = fopen($dbfile, "w");
          fwrite($fw, "$r");
          fclose($fw);
       }
       #-- close, return value
       if ($fr) {
          flock($fr, LOCK_UN);
          fclose($fr);
       }
       return($r);
    }


}  // end of class


?>