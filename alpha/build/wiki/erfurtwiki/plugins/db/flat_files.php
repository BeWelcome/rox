<?php

#  This is a replacement for the ewiki.php internal MySQL database access
#  interface; this one saves all WikiPages in so called "flat files", and
#  there are now two different formats you can choose from:
#    * rfc822-style (or say message/http like),
#      which leads to files you can edit with any available text editor
#    * in a compressed and faster 'binary' format,
#      which supports more functionality (hit counting)
#      enable with EWIKI_DB_FAST_FILES set to 1
#  As this plugin can read both, you are free to switch at any time.
#
#  To enable it, just include() this plugin __before__ the main/core
#  ewiki.php script using:
#
#       include("plugins/db/flat_files.php");
#
#  If you only will use the file database, you could go to the bottom of the
#  "ewiki.php" script and replace the 'ewiki_database_mysql' class with the
#  one defined herein. Then make also sure, that the initialization code knows
#  about it (there is a class name reference in $ewiki_plugins["database"]).
#
#  db_flat_files
#  -------------
#  The config option EWIKI_DBFILES_DIRECTORY must point to a directory
#  allowing write access for www-data (the user id, under which webservers
#  run usually), use 'chmod 757 dirname/' (from ftp or shell) to achieve this
#
#  db_fast_files
#  -------------
#  Some versions of PHP and zlib do not work correctly under Win32, so
#  you should disable it either in the php.ini, or via .htaccess:
#    php_option disable_functions "gzopen gzread gzwrite gzseek gzclose"
#  You need the plugins/db/fakezlib.php script for very old PHP versions.
#
#  db_fast_files` code was contributed_by("Carsten Senf <ewiki@csenf.de>");


#-- choose flat file format
define("EWIKI_DB_FAST_FILES", 0);
define("EWIKI_DBFF_ACCURATE", 0);


#-- plugin registration
$ewiki_plugins["database"][0] = "ewiki_database_files";


#-- backend
class ewiki_database_files {

   function ewiki_database_files() {
   }


   function GET($id, $version=false) {

      if (!$version && !($version = $this->LASTVER($id))) {
         return;
      }
      #-- read file      
      $dbfile = $this->FN("$id.$version");
      if ($f = @gzopen($dbfile, "rb")) {
         $dat = gzread($f, 1<<21);
         gzclose($f);
      }

      #-- decode      
      if ($dat && (substr($dat, 0, 2) == "a:")) {
         $r = unserialize($dat);
      }
      if (empty($r)) {
         $r = array();
         $p = strpos($dat, "\012\015\012");
         $p2 = strpos($dat, "\012\012");
         if ((!$p2) || ($p) && ($p < $p2)) {
            $p = $p + 3;
         }
         else {
            $p = $p2 + 2;
         }
         $r["content"] = substr($dat, $p);
         $dat = substr($dat, 0, $p);

         foreach (explode("\012", $dat) as $h) {
            if ($h = trim($h)) {
               $r[trim(strtok($h, ":"))] = str_replace(EWIKI_DBFILES_NLR, "\n", trim(strtok("\000")));
            }
         }
      }
      return($r);
   }


   function WRITE($hash, $overwrite=0) {

      #-- which file
      $dbfile = $this->FN($hash["id"].".".$hash["version"]);
      if (!$overwrite && file_exists($dbfile)) {
         return(0);
      }

      #-- write
      if (EWIKI_DB_FAST_FILES) {
         $val = serialize($hash);
         if (($f = gzopen($dbfile, "wb".EWIKI_DBFILES_GZLEVEL))) {
            gzwrite($f, $val);
            gzclose($f);
         }
         return(1);
      }
      else {
         $headers = "";
         foreach ($hash as $hn=>$hv) if ($hn != "content") {
            $headers .= $hn . ": " . str_replace("\n", EWIKI_DBFILES_NLR, $hv) . "\015\012";
         }
         if ($f = fopen($dbfile, "wb")) {
            flock($f, LOCK_EX);
            fputs($f, $headers . "\015\012" . $hash["content"]);
            flock($f, LOCK_UN);
            fclose($f);
            return(1);
         }
      }
   }


   function HIT($id) {
      if (EWIKI_DB_FAST_FILES) {
         $dbfile = $this->FN("$id.1");
         if ($f = gzopen($dbfile, "rb")) {
            $r = unserialize(gzread($ff, 1<<21));
            gzclose($f);
            if ($r) {
               $r["hits"] += 1;
               if ($f = gzopen($dbfile, "wb".EWIKI_DBFILES_GZLEVEL)) {
                  gzwrite($fp, serialize($r));
                  gzclose($fp);
      }  }  }  }
   }
   
   
   function FIND($list) {
      $existing = array_flip($this->ALLFILES());
      $r = array();
      foreach ($list as $id) {
         $dbfile = $this->FN($id, 0);
         $r[$id] = isset($existing[$dbfile]) ?1:0;
         if (EWIKI_DBFF_ACCURATE && $r[$id] && strpos($id, "://")) {
            $uu = $this->GET($id);
            if ($uu["meta"]) {
               $r[$id] = unserialize($uu["meta"]);
               $r[$id]["flags"] = $uu["flags"];
            } else {
               $r[$id] = $uu["flags"];
            } 
         }
      }
      return($r);
   }


   function GETALL($fields, $mask=0, $filter=0) {
      $r = new ewiki_dbquery_result($fields);
      foreach ($this->ALLFILES() as $id) {
         $r->entries[] = $id;
      }
      return($r);
   }

   
   function SEARCH($field, $content, $ci="i", $regex=0, $mask=0, $filter=0) {
      $r = new ewiki_dbquery_result(array($field));
      $strsearch = $ci ? "stristr" : "strpos";
      foreach ($this->ALLFILES() as $id) {
         $row = $this->GET($id);
         if ($mask && ($filter == $row["flags"] & $mask)) {
            continue;
         }
         $match = 
            !$regex && ($strsearch($row[$field], $content)!==false)
            || $regex && preg_match("\007$content\007$ci", $row[$field]);
         if ($match) {
            $r->add($row);
         }
      }
      return($r);
   }
   
   
   function DELETE($id, $version) {
      $fn = $this->FN("$id.$version");
      @unlink($fn);
   }

   function INIT() {
      if (!is_writeable(EWIKI_DBFILES_DIRECTORY) || !is_dir(EWIKI_DBFILES_DIRECTORY)) {
         mkdir(EWIKI_DBFILES_DIRECTORY)
         or die("db_flat_files: »database« directory '".EWIKI_DBFILES_DIRECTORY."' is not writeable!\n");
      }
   }



   #-- db plugin internal ---------------------------------------------- 

   function FN($id, $prepend_path=1) {
      $fn = EWIKI_DBFILES_ENCODE ? urlencode($id) : strtr($id, '/:', '\\:');
      if ($prepend_path) {
         $fn = EWIKI_DBFILES_DIRECTORY.DIRECTORY_SEPARATOR . $fn;
      }
      return($fn);
   }


   function ID($fn) {
      $id = EWIKI_DBFILES_ENCODE ? urldecode($fn) : strtr($fn, '\\:', '/:');
      return($id);
   }

    
   function LASTVER($id) {
      $find = $this->FN($id, 0);
      $find_n = strlen($find);
      $n = 0;
      if ($find_n) {
         $dh = opendir(EWIKI_DBFILES_DIRECTORY);
         while ($fn = readdir($dh)) {
            if ( (strpos($fn, $find) === 0) &&     //@FIXME: empty delimiter
                 ($dot = strrpos($fn, ".")) && ($dot == $find_n) &&
                 ($uu = substr($fn, ++$dot)) && ($uu > $n)  )
            {
               $n = $uu;
            }
      }  }
      return($n);
   }


   function ALLFILES() {
      $r = array();
      $dh = opendir(EWIKI_DBFILES_DIRECTORY);
      $n = 0;
      while ($fn = readdir($dh)) {
         if (is_file(EWIKI_DBFILES_DIRECTORY . "/" . $fn)) {
            $id = $this->ID($fn);
            if (($dot = strrpos($id, ".")) && (substr($id, $dot+1) >= 1)) {
               $file = substr($id, 0, $dot);
               $r[] = $file;
            }
            if ($n++ > 1000) {
               $n = 0;
               $r = array_unique($r);
            }
         }
      }
      closedir($dh);
      $r = array_unique($r);
      return($r);
   }
   

} // end of class ewiki_database_files

?>