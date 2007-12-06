<?php
/*
   This snippet implements HTTP queries, and allows for most request
   methods, content types and encodings. It is useful for contacting
   scripts made to serve HTML forms.
    - does neither depend upon wget or curl, nor any other extension
    - you can add ->$params (form variables) on the fly, it's a hash
    - if the initial URL contains a query string, the vars will be
      extracted first
    - set the ->$enc very carefully, because many CGI apps and HTTP
      servers can't deal with it (else "gzip" and "deflate" are nice)
    - there are abbreviations for the content ->$type values (namely
      "form" , "url" and "php")
    - user:password@ pairs may be included in the initially given URL
    - headers always get normalized to "Studly-Caps"
    - won't support keep-alive connections
    - for PUT and other methods, the ->$params var may just hold the
      request body
    - files can be added to the ->params array as hash with specially
      named fields: "content"/"data", and "filename"/"name" , "type"
    - you can add authentication information using the standard notation
      "http://user:passw@www.example.com/..." for ->$url and ->$proxy

   A response object will have a ->$content field, ->$headers[] and
   ->len, ->type attributes as well. You could also ->decode() the
   body, if it is app/vnd.php.serialized or app/x-www-form-urlencoded.
   
   Public Domain (use freely, transform into any other license, like
   LGPL, BSD, MPL, ...; but if you change this into GPL please be so
   kind and leave your users a hint where to find the free version).
*/


#-- request objects
class http_request {

   var $method = "GET";
   var $proto = "HTTP/1.1";
   var $url = "";
   var $params = array();   // URL/form post vars, or single request body str
   var $headers = array();
   var $cookies = array();
   var $type = "url";       // content-type, abbrv. for x-www-form-...
   var $enc = false;        // "gzip" or "deflate"
   var $error="", $io_err=0, $io_err_s="";
   var $active_client = 1;  // enables redirect-following
   var $redirects = 3;
   var $proxy = false;      // set to "http://host:NN/"
   var $timeout = 15;


   #-- constructor
   function http_request($method="GET", $url="", $params=NULL) {
      $this->headers["User-Agent"] = "http_query/17.2 {$GLOBALS[ewiki_config][ua]}";
      $this->headers["Accept"] = "text/html, application/xml;q=0.9, text/xml;q=0.7, xml/*;q=0.6, text/plain;q=0.5, text/*;q=0.1, image/png;q=0.8, image/*;q=0.4, */*+xml;q=0.3; application/x-msword;q=0.001, */*;q=0.075";
      $this->headers["Accept-Language"] = "en, eo, es;q=0.2, fr;q=0.1, nl;q=0.1, de;q=0.1";
      $this->headers["Accept-Charset"] = "iso-8859-1, utf-8";
      $this->headers["Accept-Feature"] = "textonly, tables, !tcpa, !javascript, !activex, !graphic";
      $this->headers["Accept-Encoding"] = "deflate, gzip, compress, x-gzip, x-bzip2";
      //$this->headers["Referer"] = '$google';
      $this->headers["TE"] = "identity, chunked, binary, base64";
      $this->headers["Connection"] = "close";
      //$this->headers["Content-Type"] = & $this->type;
      if (isset($params)) {
         $this->params = $params;
      }
      if (strpos($method, "://")) {
         $url = $method;  # glue for incompat PEAR::Http_Request
         $method = "GET";
      }
      $this->method($method);
      $this->setURL($url);
   }


   #-- sets request method
   function method($str = "GET") {
      $this->method = $str;
   }

   #-- special headers
   function setcookie($str="name=value", $add="") {
      $this->cookies[strtok($str,"=")] = strtok("\000").$add;
   }


   #-- deciphers URL into server+path and query string
   function setURL($url) {
      if ($this->method == "GET") {
         $this->url = strtok($url, "?");
         if ($uu = strtok("\000")) {
            $this->setQueryString($uu);
         }
      }
      else {
         $this->url = $url;
      }
   }
   
   
   #-- decodes a query strings vars into the $params hash
   function setQueryString($qs) {
      $qs = ltrim($qs, "?");
      parse_str($qs, $this->params);
   }


   #-- returns params as querystring for GET requests
   function getQueryString() {
      $qs = "";
      if (function_exists("http_build_query")) {
         $qs = http_build_query($this->params);
      }
      else {
         foreach ($this->params as $n=>$v) {
            $qs .= "&" . urlencode($n) . "=" . urlencode($v);
         }
         $qs = substr($qs, 1);
      }
      return($qs);
   }


   #-- transforms $params into request body
   function pack(&$path) {
      $m = strtoupper($this->method);

      #-- GET, HEAD
      if (($m == "GET") || ($m == "HEAD")) {
         $BODY = "";
         $path .= (strpos($path, "?") ? "&" : "?") . $this->getQueryString();
      }

      #-- POST
      elseif (($m == "POST") && is_array($this->params)) {

         #-- known encoding types
         $type = $this->type($this->type, 0);
         if ($type == "url") {
            $BODY = $this->getQueryString($prep="");
         }
         elseif ($type == "php") {
            $BODY = serialize($this->params);
         }
         elseif ($type == "form") {
            // boundary doesn't need checking, unique enough
            $bnd = "snip-".dechex(time())."-".md5(serialize($this->params))
                 . "-".dechex(rand())."-snap";
            $BODY = "";
            foreach ($this->params as $i=>$v) {
               $ct = "text/plain";
               $inj = "";
               if (is_array($v)) {
                  ($ct = $v["ct"].$v["type"].$v["content-type"]) || ($ct = "application/octet-stream");
                  $inj = ' filename="' . urlencode($v["name"].$v["file"].$v["filename"]) . '"';
                  $v = $v["data"].$v["content"].$v["body"];
               }
               $BODY .= "--$bnd\015\012"
                     . "Content-Disposition: form-data; name=\"".urlencode($i)."\"$inj\015\012"
                     . "Content-Type: $ct\015\012"
                     . "Content-Length: " . strlen($v) . "\015\012"
                     . "\015\012$v\015\012";
            }
            $BODY .= "--$bnd--\015\012";
            $ct = $this->type("form") . "; boundary=$bnd";
         }
         #-- ignore
         else {
            $this->error = "unsupported POST encoding";
          // return(false);
            $BODY = & $this->params;
         }

         $this->headers["Content-Type"] = isset($ct) ? $ct : $this->type($type, 1);
      }

      #-- PUT, POST, PUSH, P*
      elseif ($m[0] == "P") {
         $BODY = & $this->$params;
      }

      #-- ERROR (but don't complain)
      else {
         $this->error = "unsupported request method '{$this->method}'";
       //  return(false);
         $BODY = & $this->params;
      }

      return($BODY);
   }


   #-- converts content-type strings from/to shortened nick
   function type($str, $long=1) {
      $trans = array(
         "form" => "multipart/form-data",
         "url" => "application/x-www-form-urlencoded",
         "php" => "application/vnd.php.serialized",
      );
      $trans["multi"] = &$trans["form"];
      if ($long) {
         $new = $trans[$str];
      }
      else {
         $new = array_search($str, $trans);
      }
      return( $new ? $new : $str );
   }


   #-- initiate the configured HTTP request ------------------------------
   function go($force=0, $asis=0) {

      #-- prepare parts
      $url = $this->prepare_url();
      if (!$url && !$force) { return; }
      $BODY = $this->body($url);
      if (($BODY===false) && !$force) { return; }
      $HEAD = $this->head($url);

      #-- open socket
      if (!$this->connect($url)) {
         return;
      }

      #-- send request data
      fwrite($this->socket, $HEAD);
      fwrite($this->socket, $BODY);
      $HEAD = false;
      $BODY = false;

      #-- read response, end connection
      while (!feof($this->socket) && (strlen($DATA) <= 1<<22)) {
         $DATA .= fread($this->socket, 32<<10);
      }
      fclose($this->socket);
      unset($this->socket);

      #-- for raw http pings
      if ($asis) { 
         return($DATA);
      }

      #-- decode response
      $r = new http_response();
      $r->from($DATA);        // should auto-unset $DATA

      #-- handle redirects
      if ($this->active_client) {
         $this->auto_actions($r);
      }

      #-- fin      
      return($r);
   }

   #-- alias
   function start($a=0, $b=0) { 
      return $this->go($a, $b);
   }
   
   
   #-- creates socket connection
   function connect(&$url) {
      if ((isset($this->socket) and !feof($this->socket))
      or ($this->socket = fsockopen($url["host"], $url["port"], $this->io_err, $this->io_err_s, $this->timeout))) {
         socket_set_blocking($this->socket, true);
         socket_set_timeout($this->socket, $this->timeout, 555);
         return(true);
      }
      else {
         $this->error = "no socket/connection";
         return(false);
      }
   }


   #-- separate URL into pieces, prepare special headers
   function prepare_url() {
      $this->setURL($this->url);
      if (!$this->proxy) {
         $url = parse_url($this->url);
         if (strtolower($url["scheme"]) != "http") {
            $this->error = "unsupported protocol/scheme";
            return(false);
         }
         if (!$url["host"]) { return; }
         if (!$url["port"]) { $url["port"] = 80; }
         if (!$url["path"]) { $url["path"] = "/"; }
         if ($url["query"]) { $url["path"] .= "?" . $url["query"]; }
         $proxy = "";
      }
      else {
         $url = parse_url($this->proxy);
         $url["path"] = $this->url;
         $proxy = "Proxy-";
         $this->headers["Proxy-Connection"] = $this->headers["Connection"];
      }

      #-- inj auth headers
      if ($url["user"] || $url["pass"]) {
         $this->headers[$proxy."Authorization"] = "Basic " . base64_encode("$url[user]:$url[pass]");
      }
      
      return($url);
   }


   #-- generates request body (if any), must be called before ->head()
   function body(&$url) {

      #-- encoding of variable $params as request body (according to reqmethod)
      $BODY = $this->pack($url["path"]);
      if ($BODY === false) {
         return false;
      }
      elseif ($len = strlen($BODY)) {
         $this->headers["Content-Length"] = $len;
      }
      $enc_funcs = array("gzip"=>"gzencode", "deflate"=>"gzinflate", "bzip2"=>"bzcompress", "x-bzip2"=>"bzcompress", "compress"=>"gzcompress");
      if ((strlen($BODY) >= 1024) && ($f = $enc_funcs[$this->enc]) && function_exists($f)) {
         $BODY = $f($BODY);
         $this->headers["Content-Encoding"] = $this->enc;
         $this->headers["Content-Length"] = strlen($BODY);
      }
      return($BODY);
   }


   #-- generates request head part
   function head(&$url) {
   
      #-- inject cookie header (if any)
      if ($this->cookies) {
         $c = "";
         foreach ($this->cookies as $i=>$v) {
            $c .= "; " . urlencode($i) . "=" . urlencode($v);
         }
         $this->headers["Cookie"] = substr($c, 2);
         $this->headers["Cookie2"] = '$Version="1"';
      }
      
      #-- request head
      $CRLF = "\015\012";
      $HEAD  = "{$this->method} {$url[path]} {$this->proto}$CRLF";
      $HEAD .= "Host: {$url[host]}$CRLF";
      foreach ($this->headers as $h=>$v) {
         $HEAD .= trim($h) . ": " . strtr(trim($v), "\n", " ") . $CRLF;
      }
      $HEAD .= $CRLF;
      return($HEAD);
   }

   #-- perform some things automatically (redirects)
   function auto_actions(&$r) {

      #-- behaviour table
      static $bhv = array(
         "failure" => "204,300,304,305,306",
         "clean_::POST" => "300,301,302,303,307",
         "clean_::PUT" => "300,301,302,303,307",
         "clean_::GET" => "300",  // $params:=undef
         "GET_::POST" => "303",
         "GET_::PUT" => "303",    // downgrade $method:=GET
      );
   
      #-- failure
      if (strstr($this->behaviour_table["failure"], $r->status)) {
         return;
      }

      #-- HTTP redirects
      if (($pri_url=$r->headers["Location"]) || ($pri_url=$r->headers["Uri"])) {

         if ((($this->redirects--) >= 0) && ($r->status >= 300) && ($r->status < 400)) {
            $m = strtoupper($this->method);
            if (strstr($this->behaviour_table["clean_::$m"], $r->status)) {
               unset($this->params);
            }
            if (strstr($this->behaviour_table["GET_::$m"], $r->status)) {
               $this->method("GET");
            }
            $this->setURL($pri_url);
            $this->go();
         }
      }
   }
   
   #-- aliases for compatiblity to PEAR::HTTP_Request
   function sendRequest() {
      return $this->go();
   }
   function setBasicAuth($user, $pw) {
      $this->url = preg_replace("#//(.+?@)?#", "//$user@$pw", $this->url);
   }
   function setMethod($m) {
      $this->method($m);
   }
   function setProxy($host, $port=8080, $user="", $pw="") {
      $auth = ($pw ? "$user:$pw@" : ($user ? "$user@" : ""));
      $this->proxy = "http://$auth$server:$port";
   }
   function addHeader($h, $v) {
      $this->headers[$h] = $v;
   }
   function getResponseStatus() {
      $this->headers[$h] = $v;
   }
}
class http_query extends http_request {
   /* this is just an alias */
}




#-- every query result will be encoded in such an object --------------------
class http_response {

   var $status = 520;
   var $status_str = "";
   var $headers_str = "";
   var $headers = array();
   var $len = 0;
   var $type = "message/x-raw";
   var $content = "";
   
   
   function http_response() {
   }
   

   #-- fill object from given HTTP response BLOB   
   function from(&$SRC) {
      $this->breakHeaders($SRC);  // split data into body + headers
      $SRC = false;
      $this->decodeHeaders();     // normalize header names
      $this->headerMeta();
      $this->decodeTransferEncodings();    // chunked
      $this->decodeContentEncodings();     // gzip, deflate
      $this->len = strlen($this->content);
   }


   #-- separates headers block from response body part
   function breakHeaders(&$DATA) {
      $l = strpos($DATA, "\012\015\012"); $skip = 3;
      $r = strpos($DATA, "\012\012");
      if ($r && ($r<$l)) { $l = $r; $skip = 2; }
      if (!$l) { $l = strlen($DATA); }
      $this->headers_str = rtrim(substr($DATA, 0, $l), "\015");
      $this->content = substr($DATA, $l + $skip);
      $this->body = & $this->content;
      $this->data = & $this->content;  // aliases
      $this->ct = & $this->type;
   }


   #-- splits up the $headers_str into an array and normalizes header names
   function decodeHeaders() {

      #-- normalize linebreaks
      $str = & $this->headers_str;
//      $str = str_replace("\n ", " ", $str);
      $str = str_replace("\r", "", $str);
      
      #-- strip headline
      $nl = strpos($str, "\n") + 1;
      $this->proto = strtok(substr($str, 0, $nl), " ");
      $this->status = (int) strtok(" ");
      $this->status_str = strtok("\000\r\n");
      if ($this->status == 100) {
         $this->full_duplex = 1;
      }

      #-- go through lines, split name:value pairs
      foreach (explode("\n", substr($str, $nl)) as $line) {

         $i = trim(strtok($line, ":"));
         $v = trim(strtok("\000"));

         #-- normalize name look&feel
         $i = strtr(ucwords(strtolower(strtr($i, "-", " "))), " ", "-");

         #-- add to, if key exists
         if (!empty($this->headers[$i])) {
            $this->headers[$i] .= ", ".$v;
         }
         else {
            $this->headers[$i] = $v;
         }

      }
   }


   #-- extract interesting values
   function headerMeta() {
      $this->len = strlen($this->content);
      $this->type = trim(strtok(strtolower($this->headers["Content-Type"]), ";"));
   }
   

   #-- strip any content transformation
   function decodeTransferEncodings() {
      $enc = trim(strtok(strtolower($this->headers["Transfer-Encoding"]), ",;"));
      if ($enc) {
         switch ($enc) {
            case "chunked":
               $this->decodeChunkedEncoding();
               break;
            case "base64":
               $this->content = base64_decode($this->content);
               $this->len = strlen($this->content);
               break;
            case "identity": case "binary":
            case "7bit": case "8bit":
               break;
            default:
               trigger_error("http_response::decodeTransferEncodings: unkown TE of '$enc'\n", E_WARNING);
         }
      }
   }


   #-- scripts on HTTP/1.1 servers may send fragmented response
   function decodeChunkedEncoding() {

      $data = "";	# decoded data
      $p = 0;		# current string position

      while ($p < strlen($this->content)) {

         #-- read len token
         $n = strtok(substr($this->content, $p, 20), "\n");
         $p += strlen($n)+1;

         #-- make integer
         $n = 0 + (int) (trim($n));
         if (!$n) {
            break;
         }

         #-- read data
         $data .= substr($this->content, $p, $n);
         $p += $n;
      }

      $this->content = $data;
      unset($data);
      $this->len = strlen($this->content);
   }


   #-- uncompress response body
   function decodeContentEncodings() {
      $enc = trim(strtok(strtolower($this->headers["Content-Encoding"]), ";,"));
      $dat = &$this->content;
      if ($enc == "deflate") {
         $dat = gzinflate($dat);
      }
      elseif (($enc == "gzip") || ($enc == "x-gzip")) {
         if (function_exists("gzdecode")) {
            $dat = gzdecode($dat);
         }
         else {
            $dat = gzinflate(substr($dat, 10, strlen($dat)-18));
         }
      }
      elseif ($enc == "compress") {
         $dat = gzuncompress($dat);
      }
      elseif (($enc == "x-bzip2") || ($enc == "bzip2")) {
         if (function_exists("bzdecompress")) {
            $dat = bzdecompress($dat);
         }
         else trigger_error("http_response::decodeContentEncoding: bzip2 decoding isn't supported with this PHP interpreter version", E_WARNING);
      }
      $this->len = strlen($this->content);
   }


   #-- can handle special content-types (multipart, serialized, form-data)
   function decode() {
      $t = http_request::type($this->type, 0);
      if ($t == "php") {
         return(unserialize($this->content));
      }
      elseif ($t == "url") {
         parse_str($this->content, $r);
         return($r);
      }
      elseif ($t == "form") {
         // oh, not yet exactly
      }
   }

   #-- aliases for compatiblity to PEAR::HTTP_Request
   function getResponseBody() {
      return $this->content;
   }
   function getResponseStatus() {
      return $this->status;
   }
   function getResponseCode() {
      return $this->status;
   }
   function getResponseHeader($i=NULL) {
      if (!isset($i)) {
         return $this->headers;
      }
      $i = strtolower($i);
      foreach ($this->headers as $h=>$v) {
         if (strtolower($h)==$i) {
            return $v;
         }
      }
   }
}



?>