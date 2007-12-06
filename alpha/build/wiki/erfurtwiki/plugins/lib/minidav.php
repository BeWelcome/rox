<?php
/*
   The MiniDav class is a complete rewrite of PEAR::WebDAV_Server and
   strives to be easier to use. There are still enough similarities,
   but this version removes many features and comes without PHP license
   preamble (this is Public Domain).
    + adds Content-Encoding support for XML command in/out and GET,PUT
    + more senseful xml namespace handling (->xmlns[] keeps a prefix list)
    + PROPFIND uses simple property lists, with path and status inside
    + LOCK, PROPATCH, MOVE, COPY, DELETE are unimplemented as of yet
    + all M$-DAV compatibility workarounds have been disabled
    + the GET() interface will NOT work with file handles and big files
    
   This class automatically adds and removes the base URL from incoming
   and sent pathnames. It also translates XML namespace qualifiers, if
   they get registered correctly beforehand. Most request parameters are
   available as object properties AND as method parameters.

   File lists for PROPFIND and PROPPATCH simply contain a "path" (again
   without the base URL) and multiple DAV: properties (without any xmlns
   monikers). "status" is of course optional, and custom (from other XML
   namespaces) entries may be there. While it is possible to treat some
   of them specially, most should be simple name-value pairs.
   
   Simply derive a class from "MiniDav" and implement at least GET and
   PROPFIND as described by the out-commented examples herein. Then make
   an object instance "$dav = new YourDavClass();" and call the main
   "$dav->ServerRequest();" method. Place an exit; after that into your
   interface script.
   
   This class does not handle authentication (one of the many parts of
   the WebDAV spec everybody ignores). Implement an ->auth() method,
   which is already called for all RW-methods (PUT, DELELTE, MKCOL).
*/

define("MINIDAV_IGNORE_MIME", 0);   // there are a few bogus clients


#-- WebDAV base class
class MiniDav {

   #-- request
   var $path = "";
   var $depth = 9;
   var $dest = "";
   var $version = NULL;   // no SVN proto support
   var $props = NULL;

   #-- both must be lowercase here
   var $int_charset = "iso-8859-1";
   var $ext_charset = "iso-8859-1";

   #-- namespace mapping (we don't want to deal with ugly URIs throughout the code)
   var $xmlns = array();  // gets fed directly into xml parsers
   
   #-- pre-defined values (constants)
   var $base_url = "";    // will get prefixed to PROPFIND response URLs
                          // and stripped from incoming path names
   
   #-- special handling
   var $prop_as_tag = array(
      "resourcetype" => 1,
   );
   var $prop_as_date = array(
      "creationdate" => "%G-%m-%dT%TZ",
      "getlastmodified" => "%a, %d %b %G %T %Z",
   );
   

   #-- constructor
   function MiniDav() {

      #-- you have to set all desired namespace prefixes beforehand
        //@FIX: list not yet feeded into XML parser
      $this->xmlns[""] = "DAV:";   // <default xmlns=...>
      $this->xmlns["wiki1"] = "http://purl.org/rss/1.0/modules/wiki/";
      #<eee># $this->xmlns["ms-time-compat"] = "urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/";
   }


   #-- main function
   function ServeRequest($path="") {

      #-- pathname
      if (!$path) {
         $path = $_SERVER["PATH_INFO"];
      }
      $this->path = $path;
      
      #-- base url
      if (!$this->base_url) {
         $port = $_SERVER["SERVER_PORT"];
         $s = ($_SERVER["HTTPS"] && $_SERVER["SSL_PROTOCOL"] || ($port == 443) ? "s" : "");
         $port = ($port != ($s?443:80) ? ":$port" : "");
         $this->base_url = "http" . $s . "://" . $_SERVER["SERVER_NAME"] . $port . $_SERVER["SCRIPT_NAME"];
      }

      #-- depth
      if (isset($_SERVER["HTTP_DEPTH"])) {
         $d = trim(strtolower($_SERVER["HTTP_DEPTH"]));
         if ($d == "infinity") {
            $d = 16;
         }
         $this->depth = (int)$d;
      }
      
      #-- destination path
      if ($this->dest = trim($_SERVER["HTTP_DESTINATION"])) {
         if (!strncmp($this->dest, $this->base_url, strlen($this->base_url))) {
            $this->dest = substr($this->dest, strlen($this->base_url));
         }
         else {
            $this->dest = "";
         }
      }
      
      #-- overwrite?
      $this->overwrite = (($_SERVER["HTTP_OVERWRITE"] != "F") ? 1 : 0);

      #-- ??? more params
      // ...


# $this->request_debug();
      #-- call subroutine
      $method = strtoupper($_SERVER["REQUEST_METHOD"]);
      $handler = $method."_request";
      if (method_exists($this, $handler) and method_exists($this, $method)) {
         call_user_method("$handler", $this);
      }
      else {
         $this->http_error("405 Unimplemented Request Method");  // or 501 ?
         $this->OPTIONS_request();
      }

      #-- stop here
      // exit;
      # (this is handled by the caller typically)
   }



   #------------------------------------------------------- request methods ---



                               /////// GET ///////

   #-- retrieve a single document
   function GET_request() {
      $ok = $this->GET( $this->path );
      $this->http_error($ok);
   }
   
   /*
   function GET($path) {
      - just call the ->GET_response() handler with some file $data
   }
   */

   #-- finish
   function GET_response($data, $mtime=0, $ctype="") {

      #-- fix params
      if (!$mtime) {
         $mtime = time();
      }
      if (!$ctype) {
         $ctype = "application/octet-stream";
      }
      if (is_resource($data)) {
         $data = fread($data, 1<<22);
      }

      #-- send
      header("Content-Type: $ctype");
      header("Last-Modified: ".gmstrftime($this->prop_as_date["getlastmodified"], $mtime));
      $this->cut_content($data);
      $this->content_encode($data);
      print($data);

      #-- done
      return("200 OK");
   }



                               ////// HEAD ///////

   #-- we handle this by calling ->GET()
   function HEAD_request() {
      $this->HEAD( $this->path );
   }

   #-- HEAD() gets automatically emulated
   function HEAD($path) {
      ob_start();
      $this->GET();
      ob_end_clean();
   }



                               /////// PUT ///////
   
   #-- store resources under given filename
   function PUT_request() {
      $this->auth();

      #-- prepare params
      list($content_type, $charset) = $this->get_ctype();
      if (!$content_type) {
         $this->http_error("415 Use a MIME compliant client!");
      }
      else {
         $ok = $this->PUT( $this->path, $content_type, $charset );
         $this->http_error($ok);
      }
   }

   /*
   function PUT($path, $ctype, $charset) {
      - simply calls $this->get_body() to get the decompressed
        input stream
      - checks $overwrite and $ctype, $charset
      - complains at will
   }
   */



                               ///// PROPFIND /////

   #-- sort of directory listing
   function PROPFIND_request() {

      #-- request type
      if (($ctype = $this->get_ctype()) and ($this->is_xml($ctype[0]))
      and ($body = $this->get_body()))
      {
         $p = & new MiniDav_PropFind($body, $ctype[1], $this->int_charset, $this->xmlns);
         $p->parse();
#print_r($p);
         if (isset($p->prop)) {
            $this->props = array_keys($p->prop);
         }
         elseif (isset($p->allprop)) {
            $this->props = 1;
         }
         elseif (isset($p->propname)) {
            $this->props = 0;
         }
         else {
            $this->http_error("400 Couldn't determine <propfind> request type");
            exit;
         }
      }
      else {
         $this->props = 1;   // ALL
#print_r($_SERVER);
#echo "NON($ctype[0],$body)";
      }
#print_r($this);

      #-- get
      $files = $this->PROPFIND($this->path, $this->props);
      
      #-- send transformed list
      $this->PROPFIND_response($files);
   }

   /*
   function PROPFIND($path, $wanted_props) {
      - searches resources for given $path up to ->depth subdirs
      - generates a result list in a $files[] array with $wanted_props:
        $files[] = array(
                     "path"=>"/fn/...",    // ALWAYS!, without URL prefix
                     "status"=>"200 OK",   // optional
                     "getcontenttype"=>"...",  // in DAV: default namespace
                     "myXmlNs:p1"=>"...",      // other XML namespace
                  );
      - $wanted_props is either 1 or 0 or an array (1 = ALL fields, 0 = only NAMES
        of fields, or ARRAY = the respective list of requested property field names)
      - send the collected file info list to ->PROPFIND_response() finally
   }
   */

   #-- send back
   function PROPFIND_response($files) {

      #-- start
      header("Content-Type: application/xml");
      echo "<?xml version=\"1.0\" encoding=\"{$this->int_charset}\" ?>\n";
      echo "<multistatus" . $this->xmlns_out() . ">\n";

      #-- required fields
      $add_req = array();
      if (is_array($this->props)) {
         foreach ($this->props as $id) {
            $add_req[$id] = false;  // become <empty/> nodes
         }
      }
#print_r($add_req);
#print_r($this);

      #-- files
      foreach ($files as $row) {
      
         #-- transform/add fields
         $href = $this->xmlentities($this->base_url . $row["path"]);
         if (!$href) { continue; }
         unset($row["path"]);
         $status = $row["status"] ? $row["status"] : "HTTP/1.0 200 OK";
         unset($row["status"]);

         #-- add _required_ fields (but empty then)
         if ($add_req) {
            $row = $row + $add_req;
         }
         
         #-- throw entry + properties
         echo "  <response>\n";
         echo "    <href>$href</href>\n";
         echo "    <propstat>\n";
         echo "      <prop>\n";

         #-- output fields
         foreach ($row as $id=>$value) {

            #-- names only?
            if (!$this->props || ($value===false)) {
               echo "\t<$id/>\n";
            }
            else {
               #-- skip filtered
               if (is_array($this->props) && !in_array($id, $this->props)) {
                  continue;
               }
               #-- xml encode
               if ($this->prop_as_tag[$id]) {
                  if (!strlen($value)) {
                     $value = "<!--empty-->";
                  }
                  else {
                     $value = "<$value/>";
                  }
               }
               elseif ($sft = $this->prop_as_date[$id]) {
                  $value = gmstrftime($sft, $value);
               }
               else {
                  $value = $this->xmlentities($value);
               }
               echo "\t<$id>$value</$id>\n";
            }
         }
         echo "      </prop>\n";
         echo "      <status>$status</status>\n";
         echo "    </propstat>\n";
         echo "  </response>\n";
      }
      
      #-- fin
      echo "</multistatus>\n";
   }



                               //// PROPPATCH ////

   function PROPPATCH_request() {
   }
   
   /*
   function PROPPATCH($path, $set, $remove) {
      - $set and $remove are associate arrays, with property names
        and values ($set only) inside
      - $remove only contains false as values, and tells to unset
        the according property from all matched files
      - $path is either a file or a directory again (should honor
        $this->depth then)
      - returns a $files[] list, similar to the PROPFIND() method,
        which contains property names associated to status values:
          array( "getcontenttype" => "500 bad request", )
        (1 is allowed as status value)
   }
   */



                               ////// MKCOL //////

   #-- create a collection (sub directory)
   function MKCOL_request() {
      $this->auth();
      $r = $this->MKCOL($this->path);
      $this->http_error($r);
   }
   
   /*
   function MKCOL($dir) {
   }
   */



                               ////// COPY ///////

   #-- removes an entry
   function COPY_request() {
      $this->auth();
      $this->check_src_dest();
      $r = $this->COPY($this->path, $this->dest, $this->overwrite, $this->depth);
      $this->http_error($r);
   }
   
   /*
   function COPY($src, $dest, $overwrite, $depth) {
      - duplicates a $src file or a directory tree ($depth) to the
        give $dest-ination
      - if $overwrite is 1, then existing files should be ->DELETEd
        automatically (simply overwrite)
      - return success
   }
   */
   
   #-- simple pre-conditions for COPY and MOVE
   function check_src_dest() {
      if (!$this->dest) {
         $this->http_error("400 No valid Destination: given");
         exit;
      }
      elseif (trim($this->dest, "/") == trim($this->path, "/")) {
         $this->http_error("409 Source and Destination are the same");
         exit;
      }
   }



                               ////// MOVE ///////

   #-- removes an entry
   function MOVE_request() {
      $this->auth();
      $this->check_src_dest();
      $r = $this->MOVE($this->path, $this->dest, $this->overwrite, $this->depth);
      $this->http_error($r);
   }
   
   /*
   function MOVE($src, $dest, $overwrite, $depth) {
      - works like COPY, but that all source files must be deleted
        after a successful move
   }
   */



                               ///// DELETE //////

   #-- removes an entry
   function DELETE_request() {
      $this->auth();
      $this->DELETE($this->path);
   }
   
   /*
   function DELETE($path) {
      - purge the given file if it wants to
      - watch out for directories and $this->depth
   }
   */



                               ///// OPTIONS /////

   #-- yields some informational headers
   function OPTIONS_request() {
      header("Allow: " . implode(", ", $this->get_options()));
      header("DAV: 1");  // version 1 means without locking support
   }

   function OPTIONS() {
      // nothing to do
   }
   
   #-- list of defined request method handler pairs
   function get_options() {
      $class = get_class($this);
      $r = array();
      foreach (get_class_methods($class) as $fn) {
         #-- for every "METHOD()", there must be a "METHOD_request()"
         if (!strpos($fn, "_") && method_exists($this, "{$fn}_request")) {
            $r[] = strtoupper($fn);
         }
      }

      $r[] = "TRACE";   // Apache must handle this
      return($r);
   }



                               ///////////////////


                               ////// LOCK ///////


                               ///// UNLOCK //////



                               ///////////////////




   #------------------------------------------------------ utility code ---


   #-- is called for all writing methods
   function auth() {
      // Makes it easy to bring up authentication.
      // If you want to "protect" the read-only WebDAV method as
      // well, then just wrap this into your interface script.
      
      # include("ewiki/plugins/../fragments/funcs/auth.php");
   }


   #-- encode string values
   function xmlentities($s) {
      return xmlentities($s);
   }


   #-- serialize all valid $this->xmlns[]
   function xmlns_out() {
      $s = "";
      foreach ($this->xmlns as $prefix=>$uri) {
         if (strpos($uri, ":")) {
            $s .= " xmlns" . ($prefix ? ":$prefix" : "") ."=\"" . $uri . "\"";
         }
      }
      return($s);
   }




   #------------------------------------------------------- http in/out ---


   #-- compress page content (prior sending)
   function content_encode(&$body) {
      $ae = strtolower($_SERVER["ACCEPT_ENCODING"]);
      $alg = array("gzip"=>"gzencode", "deflate"=>"gzdeflate", "compress"=>"gzcompress", "x-bzip2"=>"bzcompress");
      if ($ae) {
         foreach (explode(",", $ae) as $ae) {
            $ae = trim(strtok($ae, ";"));
            if ($pf = $alg[$ae]) {
               $body = $pf($body);
               header("Content-Encoding: $ae");
               break;
            }
         }
      }
//      unset($_SERVER["ACCEPT_ENCODING"]); //@HACK: prevent accidential double encoding - ewiki plugins could do this automatically
   }


   #-- or decompress received body
   function content_decode(&$body) {
      $de = strtolower(trim($_SERVER["HTTP_CONTENT_ENCODING"]));
      if (!strlen($de)) { /* nop */ }
      elseif ($de == "gzip") { $body = function_exists("gzdecode") ? gzdecode($body) : gzinflate(substr($body, 10, strlen($body) - 18)); }
      elseif ($de == "deflate") { $body = gzinflate($body); }
      elseif ($de == "compress") { $body = gzuncompress($body); }
      elseif ($de == "x-bzip2") { $body = bzuncompress($body); }
//      unset($_SERVER["HTTP_CONTENT_ENCODING"]); //@HACK: prevent accidential double decoding, wrong here
   }


   #-- convinience function for sending HTTP status responses
   function http_error($w=false, $def_success="200 OkiDoki") {
      if ($w == false) {
         $w = "500 Internal Error";
      }
      elseif (($w === true) || ($w === 1)) {
         $w = $def_success;
      }
      if (!headers_sent() && !isset($this->no_status)) {
         if (ini_get("cgi.rfc2616_headers")) {
            header("HTTP/1.1 $w");
         }
         header("Status: $w");   // always ok
      }
   }


   #-- check content type and charset
   function get_ctype() {
      $ct = trim(strtolower(strtok($_SERVER["CONTENT_TYPE"], ";,(")));
      $rest = strtok(",(");
      if (preg_match("#charset=[\"\']?([-\d\w]+)#", $rest, $uu)) {
         $charset = $uu[1];
      }
      elseif ($ct == "text/xml") {
         $charset = "iso-8859-1";
      }
      return(array($ct, $charset));
   }
   

   #-- check content-type for being */*xml*
   function is_xml($ct) {
      if (preg_match("#(^x\.?ml/...|...[/+]xml$)#", $ct) || MINIDAV_IGNORE_MIME) {
         return(true);
      }
   }

   
   #-- get request body, decoded
   function get_body() {

      #-- fetch
      $f = fopen("php://input", "rb");
      $body = fread($f, 1<<22);
      fclose($f);

      #-- uncompress
      $this->content_decode($body);
     
      return($body);
   }


   #-- partial responses (only contiguous ranges)
   function cut_content(&$data) {

      if (($h = $_SERVER["HTTP_RANGE"])
      and preg_match("/^bytes=(\d*)-(\d+)$/", trim($h), $uu))
      { 
         list($uu, $start, $end) = $uu;

         #-- correct positions
         $len = strlen($data);
         if (!strlen($start)) {
            if ($end > $len) {
               $start = 0;
            }
            else {
               $start = $len - $end;
            }
            $end = $len - 1;
         }
         if ($start > $end) {
            $this->http_error("416 Unsatisfiable Range:");
            return;
         }

         #-- cut
         $data = substr($data, $start, $end - $start + 1);
         
         #-- send headers
         header("Content-Range: bytes $start-$end/$len");
         $this->http_error("206 Partial Content");
         $this->no_status = 1;
      }
   }


   #---------------------------------------------------------- old code ---

   /*
   #-- output raw page data
   function out_content(&$data) {
      $c = & $data["content"];
      $this->content_encode($c);
      header("Content-Length: ".strlen($c));
      print $c;
   }
   */


   #-- debugging
   function request_debug() {
      ob_start();
      print_r($_SERVER);
      echo $this->get_body();
      print_r($this);
      $d = ob_get_contents();
      ob_end_clean();
      file_put_contents("/tmp/minidav.".time().".".rand(0, 99), $d);
   }



}// end of class




#------------------------------------------------------------------ xml ---


#-- <propfind> request bodies
class MiniDav_PropFind extends easy_xml {

   function MiniDav_PropFind($xml, $ctype="", $uu=NULL, $addns=array()) {
      parent::easy_xml($xml, $ctype);
      $this->xmlns2["dav"] = "";
      $this->xmlns += array_flip($addns);
   }
   
   function start($xp, $tag, $attr) {
      parent::start($xp, $tag, $attr);
      if ($this->parent) {
         $this->{$this->parent}[$tag] = true;
      }
   }
}



?>