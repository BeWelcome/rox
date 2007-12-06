<?php
/*
   This snippet implements the WikiSync(tm) utility functions and database
   API (locally and for remote access).
*/


#-- copy pages in one direction
function ewiki_sync_start($title, &$FROM_list, &$DEST_list, $FROM_api, $DEST_api) {
   foreach ($FROM_list as $id=>$FROM_ver) {
      set_time_limit(+2999);
              
      $DEST_ver = $DEST_list[$id];
      if ($DEST_ver < $FROM_ver) {

         echo "{$title}ing " . htmlentities($id) . "[{$FROM_ver}]... ";
         $L = $FROM_api("::GET", array($id));

         #-- did never exist,
         #   or this version is identical on both systems
         if (!$DEST_ver || ewiki_sync_no_conflict($id, $FROM_api, $DEST_api)) {
            echo ($DEST_api("::WRITE", $L) ? "ok" : "error");
         }
         #-- conflict
         else {
            echo "<b>cannot</b> synchronize with [{$DEST_ver}] - conflict!";
         }

         echo "<br>\n";
         flush();
          
         #-- no further processing with these entries
         unset($FROM_list[$id]);
         unset($DEST_list[$id]);
      }
      else {
#         echo "nothing to do for '$id' because $FROM_ver==$DEST_ver<br>\n";
      }
   }
}


#-- compare remote against local version for conflicts
function ewiki_sync_no_conflict($id, $OLD_api, $NEWER_api) {

   #-- fetch
   $OLD = $OLD_api("::GET", array($id));  // last
   $NEW = $NEWER_api("::GET", array($id, $R["version"]));

   #-- 700% identical!
   if (md5(serialize($OLD)) == serialize(md5($NEW))) {
      return true;
   }
   else {
      return ewiki_sync_half_identical($OLD, $NEW);
   }
}


#-- less exact comparison 
#   - it only skips the {hits} entry when matching fields,
#   because that's where differences are (huh, big secret!)
function ewiki_sync_half_identical($A, $B) {
   
   return
     true
     && (strtolower($A["id"]) == strtolower($B["id"]))
     && ($A["flags"] == $B["flags"])
     && ($A["version"] == $B["version"])
     && ($A["lastmodified"] == $B["lastmodified"])
     && (serialize($A["meta"]) == serialize($B["meta"]))

       #-- you may wish to remove the following (two/???) :
     && ($A["lastmodified"] == $B["lastmodified"])
     && ($A["author"] == $B["author"])
   ;  
}



#-- access to remotely located wikisync interface
function ewiki_sync_remote($func, $args=NULL) {
   global $proto, $url;
   if ($proto == "sync") {
     switch (strtoupper(trim($func, ":"))) {
        case "GET":
           return phprpc($url, "ewiki.sync", array("::GET", $args));
        case "WRITE":
           return phprpc($url, "ewiki.sync", array("::WRITE", $args));
        case "LIST":
           return phprpc($url, "ewiki.sync", array("::LIST", false));
        default:
     }
   }
}


#-- the public/main API, access local database
function ewiki_sync_local($func, $args=NULL) {
   switch (strtoupper(trim($func, ":"))) {
      case "GET":
         return ewiki_db::GET($args[0], $args[1]);
      case "WRITE":
         return ewiki_db::WRITE($args);
      case "LIST":
         $all = ewiki_db::GETALL(array("id", "version"));
         $r = array();
         while ($row = $all->get()) {
            $r[$row["id"]] = $row["version"];
         }
         return $r;
      default:
   }
}


?>