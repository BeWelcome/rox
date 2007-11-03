<?php
/*
   Utility code for handling plugin .meta data files (pmd) and their
   dependencies.
*/


#-- read a single .meta file
function ewiki_pmd_read($fn) {
   $info = array();
   if ($src = file_get_contents($fn)) {
      preg_match_all("/^(\w+):[ \t]*(([^\n]*(\n )?)+)/m", $src, $uu);
      foreach ($uu[1] as $i=>$tmp) {
         $info[$uu[1][$i]] = $uu[2][$i];
      }
   }
   return($info);
}


#-- scan all .meta files or read from cache file
function ewiki_pmd($read_cached=0, $else_write=1, $warn=0, $gauge_callback="") {
   global $ewiki_pmd;
   $basedir = dirname(dirname(__FILE__));
   $metaf = "$basedir/meta.bin";

   #-- cached data
   if ($read_cached && filesize($metaf)) {
      $ewiki_pmd = unserialize((file_get_contents($metaf)));
   }
   #-- load from .meta files
   else {
      $ewiki_pmd = array();
      $files = glob("$basedir/*.meta") + glob("$basedir/*/*.meta");
      foreach ($files as $num=>$fn) {
         if ($gauge_callback) {
            $gauge_callback($num, count($files));
         }
         $id = basename($fn, ".meta");

         #-- read
         $add = ewiki_pmd_read($fn);
         if ($add) {
         
            #-- .php script
            $php = substr($fn, 0, -5) . ".php";
            if (file_exists($php) && ($l = strpos($php, "/plugins/"))) {
               $php = substr($php, 1 + $l);
               $add["fn"] = $php;
            }
            
            #-- plugin id:
            if ($add["id"]) {
               $id = "$add[id]";
            }
            
            #-- provides:
            foreach(explode(",", "$id,$add[provides],$add[delivers]") as $p) {
               if ($p = trim($p)) {
                  $ewiki_pmd[".provides"][$p][] = $id;  // only the first gets used
               }
            }

            #-- append to list 
            if ($warn && isset($ewiki_pmd[$id])) {
               echo "WARNING: a plugin with the name '$id' is already registered\n";
            }
            $ewiki_pmd[$id] = $add;
         }
      }

      #-- save
      if ($else_write && is_writeable($metaf)) {
         file_put_contents($metaf, (serialize($ewiki_pmd)));
      }
   }
   return($ewiki_pmd);  //redundant
}


#-- apply dependencies on plugin list
function ewiki_pmd_resolve_dependencies(&$list, $add_suggested=1) {
   global $ewiki_pmd;
   $error = array();
   
   #-- how to handle fields
   $fields = array(
       "depends" => +1,
       "conflicts" => -1,
       "replaces" => -1,
       "recommends" => $add_suggested,
   );

   #-- check all entries in name list
   foreach ($list as $list_i_id=>$id) {
      #-- fields
      foreach ($fields as $name=>$action) {
         if ($action && !empty($ewiki_pmd[$id][$name]))
         foreach (explode(",",$ewiki_pmd[$id][$name]) as $dep)
         {  // multiple dependencies may be given
            $dep = trim($dep);

            #-- add
            if ($action >= +1) {
               if (!in_array($dep, $list)) {
                  if (!isset($ewiki_pmd[".provides"][$dep])) {
                     // ooops
                     unset($list[$list_i_id]);
                     $error[$id] = "unmet dependency: $dep";
                  }
                  else {
                     // always adds only the very first entry
                     $list[] = $ewiki_pmd[".provides"][$dep][0];
                  }
               }
            }
            #-- remove
            elseif ($action <= -1) {
               foreach ($ewiki_pmd[".provides"][$dep] as $dep) {
                  if (in_array($dep, $list)) {
                     unset($list[array_search($dep, $list)]);
                  }
               }
            }
         }
      }

      #-- does such a plugin exist at all?
      if (!$ewiki_pmd[$id]) {
         $error[$id] = "plugin '$id' does not exist";
      }
   }

   return($error);  //ouch, if you forget that this isn't a result
}


#-- 
function ewiki_pmd_get_config_settings($list) {
}


#-- get include_once() filenames
function ewiki_pmd_get_plugin_files($list) {
   global $ewiki_pmd;
   $r = array();
   $n = 2001;
   foreach($list as $id) {
      $fn = $ewiki_pmd[$id]["fn"];
      $type = $ewiki_pmd[$id]["type"];
      ($sort = $ewiki_pmd[$id]["sort"]) or ($sort = 0);
      $n--;
      if ($fn && ($type != "R") && !in_array($fn, $r)) {
         $r["$sort.$n"] = $fn;
      }
   }
   ksort($r);
#   $r = array_values($r);
   return($r);
}


#-- plugin sorting
function ewiki_pmd_by_category() {
   global $ewiki_pmd;
   $r = array();
   foreach ($ewiki_pmd as $id=>$row) {
      if ($id[0] != ".") {
         ($cat = $row["category"]) or ($cat = "else");
         $r[$cat][$id] = $row;
      }
   }
   return($r);
}


#-- flip array by entry
function ewiki_pmd_by($field) {
   global $ewiki_pmd;
   $r = array();
   foreach ($ewiki_pmd as $id=>$row) {
      if (isset($row[$field])) {
         $r[$row[$field]] = $id;
      }
   }
   return($r);
}


#-- unwanted plugins
function ewiki_pmd_hidden($row) {
   return
     !strlen($row["type"]) || ($row["type"] == "R")
     || !strlen($row["title"])
     || ($row["priority"] == "never")
     || ($row["priority"] == "deprecated");
}



?>