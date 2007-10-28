<?php
/*
   WebDAV provides authoring and file access features via HTTP (it is
   in fact an extension to it). This fits very well with Wiki, and a
   nice client assumed provides another useful page editing interface.
   
   This snippet is innovocated by "z.php", and itself depends upon a
   modified version of the HTTP_WebDAV_Server module (originally PEAR).
   It does not yet work together with _PROTECTED_MODE settings, that's
   why "fragments/funcs/auth.php" should be used meanwhile (as always),
   even if the WebDAV standard actually forbidds to use the Basic auth
   mechanism (most implementations ignore this stupid rule, and as it
   is AntiWiki we ignore it even harder).
   
   Because ewiki has a really flat namespace (despite of subpages, we
   don't have PageGroups or SubWikis fired up per default), and even
   pagenames with a slash inside won't be treated as directories in
   any case. That's why the implementation of our WebDAV interface is
   much shorter than for other systems.
   
   - not tested at all
   - likely to work with Wiki pages only (handling of _BINARY entries
     is undefined)
   - enriched with content-coding (compression) support, though it'll
     only work for GET and PUT (won't patch the WebDAV class), but
     todays WebDAV clients are plain stupid in this regard anyhow
   - use the "plugins/debug/xerror.php" script together with this, if
     you'd like to see error messages (for debugging)
*/


#-- config
define("EWIKI_SCRIPT_WEBDAV", EWIKI_BASE_URL . "z.php/");  // may be we should use a separate z_dav.php script or so?
define("EWIKI_PAGE_CTYPE", "text/wiki; variant=ewiki; charset=iso-8859-1");
$ewiki_config["ua"] .= " WebDav/0.0.3";
define("WEBDAV_DIR_DEPTH", 1);   // make 0 to always include files from a collection


#-- implementation
class WikiDav extends MiniDav
{


    #-- constructor
    function WikiDav() {
       parent::MiniDav();
       $this->int_charset = strtolower(EWIKI_CHARSET);  // L1 only!
       if (!function_exists("ewiki_http_header_errors") && !function_exists("ewiki_xml_comment_errors")) {
          error_reporting(0);
       }
       $this->xmlns["page"] = "urn:x-ewiki:db:page-data";
       $this->xmlns["meta"] = "urn:x-ewiki:db:meta-meta";
    }



    #-- retrieve a page
    function GET($path) {

       #-- page name, look it up in database
       $id = $this->id($path);
       $version = NULL;
       $data = ewiki_db::GET($id, $version);

       #-- found?
       if ($id && $data && $data["version"]) {

          #-- headers
          ewiki_http_headers($data["content"], $id, $data, "view", $_saveasfn=0);
          
          #-- send
          return $this->GET_response($data["content"], $data["lastmodified"], EWIKI_PAGE_CTYPE);

       }
       else {
          return "404 Not Found";
       }
    }



    #-- get meta data of page 
    function PROPFIND($path, $props) {

       #-- page name, prep
       $id = $this->id($path);
       $files = array();

       #-- list pages
       if (!strlen($id) || ($id=="/")) {

          $oldest_time = 2*UNIX_MILLENNIUM;
          $result = ewiki_db::GETALL(array());
          while ($data = $result->get(1, 0x137f)) {
             if ($this->depth >= WEBDAV_DIR_DEPTH) {
                $files[] = $this->fileinfo($data);
             }
             $oldest_time = min($oldest_time, $data["created"]);
          }

          #-- add entry for virtual root directory
          $data = array(
             "id"=>"/",
             "created"=>$oldest_time,
          );
          $files[] = $this->dirinfo($data);
       }

       #-- just the specified one
       else {
          $version = NULL;
          $data = ewiki_db::GET($id, $version);
          if ($data) {
             $files[] = $this->fileinfo($data);
          }
       }

       #-- fin
       return($files);
    }


    
    #-- rearrange page meta data fields as array for WebDAV class
    function fileinfo(&$data) {

       #-- create meta/properties array
       ($ct = $data["meta"]["Content-Type"]) or ($ct = EWIKI_PAGE_CTYPE);
       $m = array(
          "path" => "/" . $data["id"],   // yes just a slash, no _SCRIPT_WEBDAV prefix
          "resourcetype" => "",  // "" ordinary page, was "collection" for dirs
          "creationdate" => $data["created"],
          "getcontentlength" => strlen($data["content"]),
          "getlastmodified" => $data["lastmodified"],
          "getcontenttype" => $ct,
          "displayname" => ewiki_split_title($data["id"]),
          "getetag" => ewiki_etag($data),
          "getcontentlanguage" => EWIKI_DEFAULT_LANG,
          "page:author" => $data["author"],
          "page:version" => $data["version"],
          "page:hits" => $data["hits"],
          "page:log" => $data["meta"]["log"],
          "page:user-agent" => $data["meta"]["user-agent"],
       );
       
       #-- add {meta}² entries
       if ($meta = $data["meta"]["meta"]) foreach ($meta as $i=>$v) {
          $m["meta:$i"] = implode(", ", $v);
       }
       
       return($m);
    }


    #-- needed only for (virtual) root element/dir
    function dirinfo(&$data) {

       #-- create meta/properties array
       $ct = "httpd/unix-directory";
       $m = array(
          "path" => "/" . trim($data["id"], "/"),
          "resourcetype" => "collection",  // it's a dir
          "creationdate" => $data["created"],
          "getcontentlength" => 0,
//             "getlastmodified" => $data["lastmodified"],
          "getcontenttype" => $ct, 
//          "displayname" => EWIKI_NAME.":",
       );
       
       return($m);
    }



    #-- save page into database (no overwriting)
    function PUT($path, $ct, $charset) {

       #-- let's call the auth routine once more??
       $this->auth();

       #-- get old page version
       $id = $this->id($path);
       ($data = ewiki_db::GET($id))
       or ($data = ewiki_db::CREATE($id));

       #-- read from whereever
       $data["content"] = $this->get_body();

       #-- check content-type
       if (($ct != "text/wiki") && ($ct != "text/plain")) {
          return("422 Only WikiPages Accepted");
       }

       #-- save back to db
       ewiki_db::UPDATE($data);
       $data["version"] += 1;
       $ok = ewiki_db::WRITE($data);
       
       #-- handle response here
       if ($ok) {
          return("200 Written Successfully");
       }
       return("500 Couldn't Save");
    }


//    #-- returns only the HTTP headers for a given page
//    function HEAD() {
//    }   // will anyhow get emulated via GET()


    function PROPPATCH() {
    }


    #-- no real page deletion without authenticated admin/pw, and we
    #   simply clear the page, if it exists (content:="DeletedPage")
    function DELETE() {
    }


    function COPY() {
    }
    function MOVE() {
    }


//    #-- we could emulate this via 'plugins/edit/lock|warn'
//    function LOCK() {
//    }
//    function UNLOCK() {
//    }



    #-- just to be sure we call it again (already done in z.php)
    function auth() {
       parent::auth();
       include("plugins/../fragments/funcs/auth.php");
    }





    #-- decode page id from the $path arg
    function id($path) {
       $id = $path;
//       if (strpos($id, EWIKI_SCRIPT_WEBDAV) === 0) {
//          $id = substr($id, strlen(EWIKI_SCRIPT_WEBDAV));
//       }
       #-- MiniDav base class always leaves the slash in (originally came from PATH_INFO)
       if ($id[0] == "/") {
          $id = substr($id, 1);
       }
       return $id;
    }


} // end of class


?>