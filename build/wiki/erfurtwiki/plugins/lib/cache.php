<?php

/*
   This plugin can cache readily rendered pages either as files or in the
   ewiki database, so they will show up much faster when accessed a second
   time.

   Right now it only supports storing the fully rendered page (_CACHE_FULL).
   Storing of page plugins output (_CACHE_ALL) should be kept disabled,
   because "UpdatedPages" and so on cannot be verified to not have updated
   since the cache entry was written.
   Also you may want to disallow caching of the "links" action, because
   there is no change tracking with it.
*/

#-- how and which pages to store
define("EWIKI_CACHE_FULL", 1);   # including control links line?
define("EWIKI_CACHE_ALL", 0);    # also virtual pages?
define("EWIKI_CACHE_VTIME", 1000);  # time to keep page plugins` output, NYI

#-- where to store the pre-rendered pages (DB or files) - unset one of them:
define("EWIKI_CACHE_DIR", "./var/cache");    # preferred over db storage
define("EWIKI_CACHE_DB", "system/cache/");   # only has effect, if _DIR undefined

#-- when to store rendered pages?
$ewiki_config["cache.actions"] = array(
   "view", "info",
   "links", 
);


#-- plugin glue
if (EWIKI_CACHE_FULL) {
   $ewiki_plugins["handler"][] = "ewiki_handler_cache_full";
   $ewiki_plugins["page_final"][] = "ewiki_store_cache_full";
}
else {
   die("unsupported ewiki caching guideline setting");
}


#-- init
if (defined("EWIKI_CACHE_DIR") && !file_exists(EWIKI_CACHE_DIR)) {
   @mkdir(dirname(EWIKI_CACHE_DIR));
   mkdir(EWIKI_CACHE_DIR);
}


#-- fetch cache entry for page
function ewiki_get_cache($action, $id) {
   $row = array();
   if (defined("EWIKI_CACHE_DIR") && EWIKI_CACHE_DIR) {
      $file = EWIKI_CACHE_DIR . "/" . $action . "," . urlencode($id);
      if (file_exists($file)) {
         $f = gzopen($file, "r");
         if ($f) {
            $content = gzread($f, 1<<17);
            fclose($f);
            if ($content) {
               $row = array(
                  "id" => $id,
                  "version" => 1,
                  "flags" => EWIKI_DB_F_BINARY¦EWIKI_DB_F_TEXT|EWIKI_DB_F_HTML,
                  "created" => filectime($file),
                  "lastmodified" => filemtime($file),
                  "content" => $content,
                  "meta" => array("class"=>"cache"),
               );
            }
         }
      }
   }
   elseif (defined("EWIKI_CACHE_DB") && (EWIKI_CACHE_DB)) {
      $id = EWIKI_CACHE_DB."$action/$id";
      $row = ewiki_db::GET($id);
   }
   return($row);
}


#-- return rendered page
function ewiki_handler_cache_full($id, &$data, $action) {
   global $ewiki_config;
   if (in_array($action, $ewiki_config["cache.actions"]) && ($cache = ewiki_get_cache($action,$id))) {
      if ($cache["lastmodified"] >= $data["lastmodified"]) {
         $data = &$cache;
         ewiki_http_headers($data["content"], $id, $cache, $action);
         return($data["content"]);
      }
   }
}


#-- if we get here, we should store the rendered page
function ewiki_store_cache_full(&$o, $id, &$data, $action) {
   global $ewiki_config;
   if (in_array($action, $ewiki_config["cache.actions"]) && ($data["version"] || EWIKI_CACHE_ALL) && ($_SERVER["REQUEST_METHOD"]=="GET")) {

      #-- only store, if we got just a few QueryString parameters
      if (count($_GET) <= 2) {
         ewiki_put_cache($action, $id, $o);
      }
   }
}


#-- real save function
function ewiki_put_cache($action, $id, &$o) {
   #-- save into cache dir
   if (defined("EWIKI_CACHE_DIR") && EWIKI_CACHE_DIR) {
      $file = EWIKI_CACHE_DIR . "/" . $action . "," . urlencode($id);
      $f = gzopen($file, "w9");
      if ($f) {
         gzwrite($f, $o);
         fclose($f);
      }
   }
   #-- store in ewiki database
   elseif (defined("EWIKI_CACHE_DB") && (EWIKI_CACHE_DB)) {
      $id = EWIKI_CACHE_DB."$action/$id";
      $save = array(
         "id" => $id,
         "version" => 1,
         "flags" => EWIKI_DB_F_BINARY¦EWIKI_DB_F_TEXT|EWIKI_DB_F_HTML,
         "created" => $data["lastmodified"],
         "lastmodified" => time(),
         "content" => &$o,
         "meta" => array("class"=>"cache"),
         "author" => ewiki_author("ewiki_cache"),
      );
      ewiki_db::WRITE($save, true);
   }
}


?>