<?php define("XMLRPC_VERSION", "0.3.10");
#
#  Supports XML-RPC (text/xml) and XML+RPC (application/rpc+xml) compressed,
#  and can be used as client or server interface. Works without XMLRPC and
#  XML extensions, but utilizes them for optimal speed whenever available.
#
#   XXXX   XXXX MMM     MMM LLL              RRRRRRR   PPPPPPP    CCCCCCC
#    XXXX XXXX  MMMM   MMMM LLL      +++     RRRRRRRR  PPPPPPPP  CCCCCCCCC
#     XXXXXXX   MMMMM MMMMM LLL      +++     RRR   RRR PPP   PPP CCC    CCC
#      XXXXX    MMMMMMMMMMM LLL  +++++++++++ RRR   RRR PPP   PPP CCC
#       XXX     MMM MMM MMM LLL  +++++++++++ RRRRRRRR  PPPPPPPP  CCC
#      XXXXX    MMM  M  MMM LLL      +++     RRRRRRR   PPPPPPP   CCC
#     XXXXXXX   MMM     MMM LLL      +++     RRR  RRR  PPP       CCC    CCC
#    XXXX XXXX  MMM     MMM LLLLLLL          RRR   RRR PPP       CCCCCCCCC
#   XXXX   XXXX MMM     MMM LLLLLLL          RRR   RRR PPP        CCCCCCC
#
#  This is Public Domain. (c) 2004 WhoEver wants to. [milky*erphesfurt·de]


#-- config
define("XMLRPC_PLUS", 0);        # use XML+RPC per default
define("XMLRPC_AUTO_TYPES", 0);  # detect base64+datetime strings and automatically generate the according xmlrpc object representations then
define("XMLRPC_AUTO_UTF8", 1);   # de/convert anything from and to UTF-8 automatically - if yourscripts use Latin1 natively, but the RPC server expects/sends UTF-8
define("XMLRPC_CHARSET", "utf-8");  # used in responses and requests
define("XMLRPC_AUTODISCOVERY", 0);  # "connections" automatically create methods
define("XMLRPC_FAST", 1);        # use PHPs XML-RPC extension where possible
define("XMLRPC_OO", 1);          # return XML-RPC/HTTP errors as objects
define("XMLRPC_DEBUG", 0);       # output error hints, write /tmp dumps - set this to 1, 2 or 3

#-- _server() settings
define("XMLRPC_LOG", "/tmp/xmlrpc.".@$_SERVER["SERVER_NAME"].".log");

#-- general data
#  (don't change the following, most are auto-configured values)
define("XMLRPC_UA", "xml+rpc/".XMLRPC_VERSION." (PHP/".PHP_VERSION."; ".PHP_OS.")");
define("XMLRPC_MIME_NEW", "application/rpc+xml");
define("XMLRPC_MIME_OLD", "text/xml");
define("XMLRPC_MIME", XMLRPC_MIME_OLD);
define("XMLRPC_ACCEPT", XMLRPC_MIME_NEW.", ".XMLRPC_MIME_OLD."; q=0.5");
define("XMLRPC_EPI", function_exists("xmlrpc_decode_request"));

#-- init
error_reporting(0);
if (isset($_SERVER["HTTP_CONTENT_TYPE"]) && empty($_SERVER["CONTENT_TYPE"])) {
   $_SERVER["CONTENT_TYPE"] = $_SERVER["HTTP_CONTENT_TYPE"];   // older CGI implementations
}




############################################################################
#                                                                          #
#  client part                                                             #
#                                                                          #
############################################################################


#-- Issue a request, call can take any number of arguments.
#     $result = xmlrpc("http://example.com/RPC2/", "method1", $arg1 ...);
#     $result = xmlrpc("xml+rpc://here.org/RPC3/", "ns.function", ...);
#   Results automatically have <datetime> values converted into Unix
#   timestamps and <base64> unpacked into strings.
#
function xmlrpc($server, $method=NULL /*, ... */) {
   if ($method) {
      $params = func_get_args();
      shift($params); shift($params);
      return
        xmlrpc_request($server, $method, $params);
   }
   else {
      return
        new xmlrpc_connection($server);
   }
}



#--  Generate and send request, decode response.
function xmlrpc_request($url, $method, $params=array(), $plus=XMLRPC_PLUS, $gzip=0) {
   global $xmlrpc_response_headers, $xmlrpc_error;
   
   #-- init whole lib for request (we are not-OO here)
   $xmlrpc_error = false;
   $xmlrpc_response_headers = array();
   
   #-- encapsulate req, transmit it
   $socket = xmlrpc_request_send($url, $method, $params, $plus, $gzip);
   if (!$socket) {
      return xmlrpc_error(-32768, "no connection", 0, "GLOBALVARS");
   }

   #-- wait for, read response
   $response = "";
   while (!feof($socket) && (strlen($DATA) <= 768<<10)) {
      $response .= fread($socket, 4<<10);
   }
   fclose($socket);
   if (XMLRPC_DEBUG >= 3) {
      echo "<code>$response</code>";
   }

   #-- decode answer and give results
   return xmlrpc_response_decode($response);
}


#-- an alias
function xmlrpc_call($url, $method, $params=array(), $plus=XMLRPC_PLUS, $gzip=0) {
   return xmlrpc_request($url, $method, $params, $plus, $gzip);
}



#-- marshall request parameters into array, hash, xml string
function xmlrpc_request_send($url, $method, &$params, $plus, $gzip, $blocking=true) {

   #-- get connection data
   $c = parse_url($url);
   ($host = $c["host"]);
   ($port = @$c["port"]) or ($port = 80);
   ($path = $c["path"]) or ($path = "/");
   if (strpos($c["scheme"], "+")) {
      $plus++;
   }
   if (strpos($c["scheme"], "gzip")) {
      $gzip++;
   }
   if (!$host) { return(NULL); }
   $inj = "";
   if ($str = $c["user"]) {
      if ($c["pass"]) { $str .= ":" . $c["pass"]; }
      $inj = "Authorization: Basic " . base64_encode($str) . "\n";
   }
   
   #-- mk request HTTP+XML block from params
   $request = xmlrpc_request_marshall($method, $params);
   $request = xmlrpc_request_http($request, $path, $host, $plus, $gzip, $inj);

   #-- connect, send request
   if ($socket = fsockopen($host, $port, $io_err, $io_err_s, 30)) {
      socket_set_blocking($socket, $blocking);
      socket_set_timeout($socket, 17, 555);
   }
   else {
      echo "Could not connect to '<b>$host</b>:$port$path' - error $io_err: $io_err_s.<br>\n";
      return(NULL);
   }
   fputs($socket, $request);

   #-- done here
   return($socket);
}


#-- marshall function call into XML+HTTP string
function xmlrpc_request_marshall($method, &$params) {

   #-- use xmlrpc-epi
   if (XMLRPC_FAST && XMLRPC_EPI) {
      $query = xmlrpc_encode_request($method, $params);
      return($query);
   }

   #-- build query
   $query = array(
      "methodCall" => array(
         "methodName" => array( ",0"=>$method ),
         "params" => array()
      )
   );
   foreach ($params as $i=>$p) {
      $query["methodCall"]["params"]["param,$i"] = xmlrpc_compact_value($p);
   }
   $query = array2xml($query, 1, 'encoding="'.XMLRPC_CHARSET.'" ');

   #-- encode?
   if (XMLRPC_AUTO_UTF8) {
      $query = utf8_encode($query);
   }
   
   return($query);   
}


#-- enclose body into HTTP request string
function xmlrpc_request_http(&$query, $path, $host, $plus, $gzip, $inj_header="") {

   #-- build request
   $n = "\015\012";
   $request = "POST $path HTTP/1.0$n"
            . "Host: $host$n"
            . ($inj_header ? str_replace("\n", $n, $inj_header) : "")
            . "User-Agent: " . XMLRPC_UA . "$n"
            . "Accept: ".XMLRPC_ACCEPT."$n"
            . (!XMLRPC_DEBUG ? "Accept-Encoding: deflate$n" : "")
            . "Content-Type: ".($plus ? XMLRPC_MIME_NEW : XMLRPC_MIME_OLD)
                              ."; charset=".XMLRPC_CHARSET."$n";

   #-- compress?
   if ($gzip) {
      $query = gzdeflate($query);
      $request .= "Content-Encoding: deflate$n";
   }
   $request .= "Content-Length: " . strlen($query) . "$n" . "$n";
   $request .= $query . "$n";

   return($request);
}


#-- unpack response from HTTP and XML representation
function xmlrpc_response_decode(&$response) {
   global $xmlrpc_response_headers;

   #-- split into headers and content
   $l1 = strpos($response, "\n\n");
   $l2 = strpos($response, "\n\r\n");
   if ($l2 && (!$l1 || ($l2<$l1))) {
      $head = substr($response, 0, $l2);
      $response = substr($response, $l2+3);
   }
   else {
      $head = substr($response, 0, $l1);
      $response = substr($response, $l2+2);
   }

   #-- decode headers, decompress body
   foreach (explode("\n", $head) as $line) {
      $xmlrpc_response_headers[strtolower(trim(strtok($line, ":")))] = trim(strtok("\000"));
   }
   if ($enc = trim(@$xmlrpc_response_headers["content-encoding"])) {
      if (($enc == "gzip") || ($enc == "x-gzip")) {
         $response = gzinflate(substr($response, 10, strlen($response)-18));
      }
      elseif (($enc == "compress") || ($enc == "x-compress")) {
         $response = gzuncompress($response);
      }
      elseif (($enc == "deflate") || ($enc == "x-deflate")) {
         $response = gzinflate($response);
      }
   }

   $r = xmlrpc_response_unmarshall($response);
   if (XMLRPC_DEBUG) {var_dump($r);}
   return($r);
}


#-- decode XML-RPC from string into array and extract its actual meaning
function xmlrpc_response_unmarshall(&$response) {
   global $xmlrpc_response_headers;

   #-- strip encoding
   if (XMLRPC_AUTO_UTF8) {
      xmlrpc_decode_utf8xml($response, @$xmlrpc_response_headers["content-type"].@$xmlrpc_response_headers["content-charset"]);
   }

   if (XMLRPC_DEBUG >= 4) { fwrite(fopen("/tmp/xmlrpc:resp_in_xml","w"), $response); }
   
   #-- use xmlrpc-epi
   if (XMLRPC_FAST && XMLRPC_EPI) {
      $r = xmlrpc_decode_request($response, $uu);
      xmlrpc_epi_decode_xtypes($r);
      if (is_array($r) && (count($r)==2) && isset($r["faultCode"]) && isset($r["faultString"])) {
         return xmlrpc_error($r["faultCode"], $r["faultString"], 1, "GLOBALVARS");
      }
      else {
         return($r);
      }
   }


   #-- unmarshall XML
   $response = xml2array($response);

   #-- fetch content (one returned element)
   if ($r = @$response["methodResponse,0"]["params,0"]["param,0"]["value,0"]) {
      $r = xmlrpc_decode_value($r);
      return($r);
   }
  
   #-- error cases
   #  (we should rather return an error object here)
   if (($r = @$response["methodResponse,0"]["fault,0"]["value,0"]) && ($r = xmlrpc_decode_value($r))) { 
      return xmlrpc_error($r["faultCode"], $r["faultString"], 1, "GLOBALVARS");
   }
   else {
      return xmlrpc_error(-32600, "xml+rpc: invalid response", 0, "GLBLVRS");
   }
   return(NULL);
}



#-- Establish a virtual XML+RPC or XML-RPC server connection (a pseudo
#   handshake is used to determine supported protocol / extensions).
class xmlrpc_connection {

   #-- init
   function xmlrpc_connection($url, $autodiscovery=0) {
      global $xmlrpc_response_headers;
      $this->server = $url;
      $this->plus = 0;
      $this->gzip = 0;

      #-- handshake to check supported protocol
      $funcs = $this->call("system.getVersion");
      $this->plus = (strpos($xmlrpc_response_headers["accept"], XMLRPC_MIME_NEW) !== false);
      $this->gzip = (strpos($xmlrpc_response_headers["accept_encoding"], "deflate") !== false);
      
      #-- auto-discovery, create 'method' names
      if ($funcs && (XMLRPC_AUTODISCOVERY || $autodiscovery)) {
         foreach ($funcs as $fn) {
            $short = $fn;
            if ($l = strpos($fn, ".")) {
               $short = substr($fn, $l + 1);
               if (substr($fn, 0, $l) == "system") { continue; }
            }
            $this->short = create_function("", "return xmlrpc_request('{$this->server}','$fn',func_get_args(),{$this->plus},{$this->gzip});");
         }
      }
   }
   
   #-- generical call (needs func name)
   function call($method /*, ... */) {
      $params = func_get_args();
      shift($params);
      $r = xmlrpc_request($this->serverm, $method, $params, $this->plus, $this->gzip);
      return($r);
   }
}

#-- an alias
class xmlrpc extends xmlrpc_connection {
}




############################################################################
#                                                                          #
#  server implementation                                                   #
#                                                                          #
############################################################################


#-- Check request and execute function if registered in $xmlrpc_methods[]
#   array.
function xmlrpc_server() {

   global $xmlrpc_methods;

   #-- server is active
   define("XMLRPC_SERVER", getmypid());
   if (XMLRPC_DEBUG) { error_reporting(E_ALL^E_NOTICE); }
   ob_start();

   #-- standard reply headers
   header("Accept: ".XMLRPC_MIME_NEW.", ".XMLRPC_MIME_OLD."; q=0.5");
   header("Accept-Encoding: deflate");
   header("X-Server: " . XMLRPC_UA);
   header("Connection: close");
   header("Cache-Control: private");

   #-- fixes for PHP/Apache
   if (function_exists("getallheaders")) {
      foreach (getallheaders() as $i=>$v) {
         $_SERVER[strtoupper(strtr("HTTP_$i", "-", "_"))] = $v;
      }
   }

   #-- check and get call
   $allowed = array(
      "REQUEST_METHOD" => array("POST", "PUT", "CALL"),
      "CONTENT_TYPE" => array(XMLRPC_MIME_NEW, XMLRPC_MIME_OLD),
   );
   foreach ($allowed as $WHAT=>$WHICH) {
      if (!in_array(trim(strtok($WRONG=$_SERVER[$WHAT], ";,(")), $WHICH)) {
         header("Status: 400 Go Away, Stupid!");
         if (!$WRONG) {
            $WRONG = "undefined";
         }
         die("<h2>Error</h2>Your request was bogus, <b>$WHAT</b> must be <i>"
             . implode("</i> or <i>", $WHICH) . "</i>, but yours was '<tt>$WRONG</tt>'.\n");
      }
   }
   if (!($xml_request = xmlrpc_fetch_post_chunk())) {
      header("Status: 500 How Sad");
      die("<h2>Error</h2>Could not fetch POST data.\n");
   }

   #-- decipher incoming XML request string
   $method = "";
   if (XMLRPC_FAST && XMLRPC_EPI) {
      $params = xmlrpc_decode_request($xml_request, $method);
      xmlrpc_epi_decode_xtypes($params);
   }
   else {
      $params = xmlrpc_request_unmarshall($xml_request, $method);
   }

   
   #-- add the few system.methods()
   //if (empty($xmlrpc_methods)) {
   //   $xmlrpc_methods = get_defined_functions();
   //}
   $xmlrpc_methods["system"] = "xmlrpc_system_methods";   # a class

   #-- call
   $result = xmlrpc_exec_method($method, $params);

   #-- send back result
   if (isset($result)) {
      if (isset($result)) {
         $resp["methodResponse"]["params"]["param"] = xmlrpc_compact_value($result);
      }
      else {
         $resp["methodResponse"]["params"] = array();
      }

      xmlrpc_send_response($resp);
   }
   else {
      $result = xmlrpc_error(0, "No Result");
      xmlrpc_send_response($result);
   }
}



#-- decode <methodCall> XML string into understandable chunks,
#   gives $params as return value and $method name via pass-by-ref
function xmlrpc_request_unmarshall(&$xml_request, &$method) {

   #-- mangle charset
   if (XMLRPC_AUTO_UTF8) {
      xmlrpc_decode_utf8xml($xml_request, $_SERVER["CONTENT_TYPE"].$_SERVER["HTTP_CONTENT_CHARSET"]);
   }

   #-- decode XML string into PHP arrays
   $call = xml2array($xml_request, 1);
   $xml_request = NULL;

   $call = $call["methodCall,0"];
   if (!$call) {
      xmlrpc_send_response(xmlrpc_error(-32600, "Bad Request, <methodCall> missing"));
   }
   $method = $call["methodName,0"][",0"];
   if (!$method) {
      xmlrpc_send_response(xmlrpc_error(-32600, "Bad Request, <methodName> missing"));
   } 

   $params = array();
   foreach ($call["params,1"] as $uu => $param) {
      $params[] = xmlrpc_decode_value($param["value,0"]);
   }

   return($params);
}



#-- Call the requested method (using the XML-method to PHP-function mapping
#   table and hints).
function xmlrpc_exec_method($method, $params) {

   global $xmlrpc_methods;
   if (XMLRPC_DEBUG >= 2) { error_reporting(E_ALL^E_NOTICE); }

   #-- check if allowed call
   $rf = strtr($method, ".", "_");
   $cl = strtok($method, ".");
   if (!$xmlrpc_methods[$method] && !$xmlrpc_methods[$cl]
      && !in_array($method, $xmlrpc_methods)
      && !in_array($rf, $xmlrpc_methods) && !in_array($cl, $xmlrpc_methods) )
   {
      xmlrpc_send_response(xmlrpc_error(-32601));
   }

   #-- real function call
   if ($php_func_name = $xmlrpc_methods[$method]) {
      $rf = $method = $php_func_name;
   }
   if (function_exists($rf)) {
      $result = call_user_func_array($rf, $params);
      if (XMLRPC_DEBUG >= 4) { fwrite(fopen("/tmp/xmlrpc:func_call_res","w"),serialize(array($rf,$result,$params))); }
      return($result);
   }
   #-- PHP object method calls
   else {
      $class = strtok($method, ".");
      $method = strtok("\000");
      if ($uu = $xmlrpc_methods[$class]) {
         $class = $uu;
      }
      if ($class && class_exists($class) && $method) {
         $obj = new $class;
         if (method_exists($obj, $method)) {
            $result = call_user_method_array($method, $obj, $params);  //<DEPRECATED>
            return($result);
         }
      }
   }

   #-- else error
   xmlrpc_send_response(xmlrpc_error(-32601));
}



#-- Get POST data from PHP (if it gives it to us).
function xmlrpc_fetch_post_chunk() {
   global $HTTP_RAW_POST_DATA;

   $data = false;
   if ($f = fopen("php://input", "rb")) {
      $data = fread($f, 0x0100000);
      fclose($f);
   }
   if (empty($data)) {
      ini_set("always_populate_raw_post_data", "true");  // well, maybe(!?)
      $data = $HTTP_RAW_POST_DATA;
      $HTTP_RAW_POST_DATA = "";
   }
   $enc = trim(strtolower($_SERVER["HTTP_CONTENT_ENCODING"]));
   $funcs = array("deflate"=>"gzinflate", "gzip"=>"gzdecode", "compress"=>"gzuncompress", "x-gzip"=>"gzdecode", "x-bzip2"=>"bzuncompress");
   if ($enc && ($pf = $funcs[$enc]) && function_exists($pf)) {
      $data = $pf($data);
   }
   return($data);
}


#-- converts UTF-8 documents into Latin-1 ones
function xmlrpc_decode_utf8xml(&$xml, $ct) {
   if (strpos(strtolower($ct), "utf-8") or preg_match('/<\?xml[^>]+encoding=["\']utf-8/i', $xml)) {
      $xml = utf8_decode($xml);
      $xml = preg_replace('/(<\?xml[^>]+encoding=["\'])utf-8(["\'])/i', '$1iso-8859-1$2', $xml, 1);
   }
}



#-- Creates an error object.
function xmlrpc_error($no=-32500, $str="", $type=1, $into_vars=0) {
   global $xmlrpc_error, $xmlrpc_errorcode;
   $errors = array(
           0 => "No Result",
      -32300 => "Transport error",
      -32400 => "Internal Server Error",
      -32500 => "Application error",
      -32600 => "Invalid message format / Bad request",
      -32601 => "Method does not exist",
      -32602 => "Parameter type mismatch",
      -32603 => "Internal XML-RPC error",
      -32604 => "Too many parameters",
      -32700 => "Not well-formed XML",
      -32701 => "Unsupported encoding - only ISO-8859-1 and UTF-8 capable",
      -32702 => "Invalid characters, encoding mismatch",
   );
   #-- build response xml/array
   if (!($str) && !($str = $errors[$no])) {
      $str = "Unknown Error";
   }
   if ($into_vars && !XMLRPC_OO) {
      $xmlrpc_error = $str;
      $xmlrpc_errorcode = $no;
      return(NULL);
   }
   else {
      return new xmlrpc_error($no, $str, $type);
   }
}


#-- error object
class xmlrpc_error {

   var $type = 1;   // else an HTTP error
   var $no;
   var $str;
   
   function xmlrpc_error($no, $str, $type=1) {
      $this->type = $type;
      $this->no = $no;
      $this->str = $str;
   }
   
   function send() {
      $error = xmlrpc_compact_value(array(
         "faultCode" => $no,
         "faultString" => $str,
      ));
      $resp = array(
         "methodResponse" => array(
            "fault" => $error
         )
      );
      xmlrpc_send_response($resp);
   }
}


#-- Sends a response.
function xmlrpc_send_response($r) {

   #-- error objects send itself (by calling _send_response() again ;-)
   if (is_object($r)) {
      $r->send();
   }

   #-- answer XML-RPC and XML+RPC requests
   $ct = trim(strtok(strtolower($_SERVER["CONTENT_TYPE"]), ";,("));  // from original request
   $cs = XMLRPC_CHARSET;
   header("Content-Type: $ct; charset=\"$cs\"");
   
   #-- make XML document from it
   if (is_array($r)) {
      $r = array2xml($r, 1, 'encoding="'.$cs.'" ');
   }

   #-- compress answer?
   if (!headers_sent()) {
      $enc = trim(strtolower($_SERVER["HTTP_ACCEPT_ENCODING"]));
      $funcs = array("deflate"=>"gzdeflate", "gzip"=>"gzencode", "compress"=>"gzcompress", "x-gzip"=>"gzencode", "x-bzip2"=>"bzcompress");
      if ($enc && ($pf = $funcs[$enc]) && function_exists($pf)) {
         header("Content-Encoding: $enc");
         $r = $pf($r);
      }
   }

   #-- send
   if (ob_get_level()) {
      #-- this prevents that PHP errors appear as garbage in our response
      $add .= "<!--\n" . ob_get_contents() . "\n-->";
      ob_end_clean();
   }
   header("Content-Length: " . strlen($r));
   print $r . $add;
   die;
}



#-- Provides "system.*" method namespace.
class xmlrpc_system_methods {

   function listMethods() {
      global $xmlrpc_methods;
      $r = array();
      foreach ($xmlrpc_methods as $i=>$i2) {
         $real = is_int($i) ? $i2 : $i;
         if (class_exists($real) && ($i2=$real) || class_exists($i2)) {
            foreach (get_class_methods($i2) as $add) {
               $r[] = $real.".".$add;
            }
         }
         else {
            $r[] = $real;
         }
      }
      return($r);
   }

   function time() {
      return new xmlrpc_datetime(time());
   }
}


############################################################################
#                                                                          #
#  misc functions                                                          #
#                                                                          #
############################################################################


function xmlrpc_log($message) {
}

function xmlrpc_die($error="", $str="") {
}



############################################################################
#                                                                          #
#  data representation mangling                                            #
#                                                                          #
############################################################################


#-- Makes compact-array2xml datavar from a PHP variable.
function xmlrpc_compact_value($var, $n=0) {

   #-- create compact-array2xml tree
   $root = array(
      "value,$n" => array(),
   );
   $r = &$root["value,$n"];

   #-- detect PHP values to be complex types in XML-RPC
   if (XMLRPC_AUTO_TYPES && is_string($var)) {
      if ((strlen($var) >= 64) && preg_match('/^[\w]+=*$/', $var)) {
         $var = new xmlrpc_base64($var);
      }
      elseif ((strlen($var)==17) && ($var[8]=="T") && preg_match('/^\d{8}T\d\d:\d\d:\d\d$/', $var)) {
         $var = new xmlrpc_datetime($var);
      }
   }

   #-- complex types
   if (is_object($var)) {
      $r = $var->out();
   }
   #-- arrays and hashes(structs)
   elseif (is_array($var)) {
      if (isset($var[0]) || empty($var)) {
         $r = array("array,$n" => array("data,0" => array()));
         $r = &$r["array,$n"]["data,0"];
         foreach ($var as $n=>$val) {
            $r = array_merge($r, xmlrpc_compact_value($val, $n));
         }
      }
      else {
         $r = array("struct,$n"=>array());
         $r = &$r["struct,$n"];
         $n = 0;
         foreach ($var as $i=>$val) {
            $r["member,$n"] = array_merge(array(
               "name,0" => array(",0" => "$i"),
            ), xmlrpc_compact_value($val, 1));
            $n++;
         }
      }
   }
   #-- simple types
   elseif (is_bool($var)) {
      $r = array(
         "boolean,$n" => array(",0" => ($var?1:0)),
      );
   }
   elseif (is_int($var)) {
      $r = array(
         "int,$n" => array(",0" => $var),
      );
   }
   elseif (is_float($var)) {
      $r = array(
         "double,$n" => array(",0" => $var),
      );
   }
   elseif (is_string($var)) {
      $r = array(
         "string,$n" => array(",0" => $var),
      );
   }
   return($root);
}


#-- Makes a PHP array from a compact-xml2array representation. $value must
#   always be the xml2array elements _below_ the ["value,0"] or ["data,0"]
#   or ["member,N"] entry.
function xmlrpc_decode_value($value) {
   $val = NULL;
   foreach ($value as $i=>$d) {

      #-- use single (text) content xml2array entry as actual $d var
      if (is_array($d) && isset($d[",0"])) {
         $d = $d[",0"];
      }

      #-- convert into PHP var based on type
      $type = strtok($i, ",");
      switch ($type) {

         case "array":
            $val = array();
            foreach ($d["data,0"] as $i=>$value) {
               $val[] = xmlrpc_decode_value($value);
            }
            break;

         case "struct":
            $val = array();
            foreach ($d as $uu=>$d2) {
               if (($in=$d2["name,0"][",0"]) && ($pos2=1) || ($in=$d2["name,1"][",0"]) && ($pos2=0)) {
                  $val[$in] = xmlrpc_decode_value($d2["value,$pos2"]);
               }
            }
            break;

         case "":    # handles also '<value>s</value>' instead
         case "0":   # of '<value><string>s</string></value>'
         case "string":
            $val =  is_array($d) ? "" : (string)$d;
            break;

         case "base64":
            $val = (XMLRPC_AUTO_TYPES>=2) ? base64_decode($d) : (string)$d;
            if ((XMLRPC_AUTO_UTF8 >= 2) && ($uu = utf8_decode($val))) {
               $val = $uu;
            }
            break;
            
      // case "real":  case "float":   // neither is allowed
         case "double":
            $val = (double)$d;
            break;
         case "i4":
         case "int":
            $val = (int)$d;
            break;

         case "boolean":
            $val = (boolean)$d;
            break;

         case "dateTime.iso8601":
            $val = xmlrpc_strtotime($d);
            break;

         default:
            if (defined("XMLRPC_SERVER")) {
               xmlrpc_send_response(xmlrpc_error(-32600, "Unknown data type '$type'"));
            }
            else {
               echo $xmlrpc_error = "UNKNOWN XML-RPC DATA TYPE '$type'<br>\n";
               $xmlrpc_errorcode = -32207;
            }
#           echo "<!-- UNKNOWN TYPE $type -->\n";
#           xmlrpc_log("bad data type '$type' enountered");
      }
   }
   return($val);
}


#-- More complex XML-RPC data types need object representation to
#   distinguish them from ordinary string and integer vars.
class xmlrpc_xtype {
   var $scalar = "";
   var $xmlrpc_type = "string";
   var $tag = "string";
   function xmlrpc_type($str) {
      $this->data = $str;
   }
   function out() {
      return array($this->tag.",0" => array(",0"=>$this->scalar));
   }
}
class xmlrpc_base64 extends xmlrpc_xtype {
   function xmlrpc_base64($str) {
      $this->tag = "base64";
      $this->xmlrpc_type = "base64";
      if (XMLRPC_AUTO_UTF8 >= 2) {
         $str = utf8_encode($str);
      }
      if (!preg_match("/^[=\w\s]+$/", $str)) {
         $this->encode=1;
      }
      $this->scalar = $str;
   }
   function out() {
      if (isset($this->encode)) {
         $this->scalar = chunk_split(base64_encode($this->scalar), 74, "\n");
      }
      return xmlrpc_xtype::out();
   }
}
class xmlrpc_datetime extends xmlrpc_xtype {
   function xmlrpc_datetime($t) {
      $this->tag = "dateTime.iso8601";
      $this->xmlrpc_type = "datetime";
      if (($t > 0) && ($t[8] != "T")) {
         $this->timestamp = $t;
         $t = xmlrpc_timetostr($t);
      }
      $this->scalar = $t;
   }
}

#-- Further simplify use of the above ones.
function xmlrpc_base64($string) {
   return(new xmlrpc_base64($string));
}
function xmlrpc_datetime($timestr) {
   return(new xmlrpc_datetime($timestr));
}


#-- Deciphers ISO datetime string into UNIX timestamp.
function xmlrpc_strtotime($str) {
   $tm = explode(":", substr($str, 9));
   $t = mktime($tm[0], $tm[1], $tm[2], substr($str, 4, 2), substr($str, 6, 2), substr($str, 0, 4));
   return($t);
}
function xmlrpc_timetostr($time) {
   return(gmstrftime("%Y%m%dT%T", $time));
}


#-- helping hand for the xmlrpc-epi extension of php
function xmlrpc_epi_decode_xtypes(&$r) {
   if (is_object($r) && isset($r->xmlrpc_type)) {
      if (isset($r->timestamp)) {
         $r = $r->timestamp;
      }
      else {
         $r = $r->scalar;
      }
   }
   elseif (is_array($r)) {
      foreach ($r as $i=>$v) {
         xmlrpc_epi_decode_xtypes($r[$i]);
      }
   }
}




############################################################################
#                                                                          #
#  simplified XML parser                                                   #
#                                                                          #
############################################################################


#-- Encode the two chars & and < into htmlentities (there is nothing said
#   about the possible other entities in the XML-RPC spec).
function xml_entities($str) {
   $e = array(
      "&" => "&amp;",
      "<" => "&lt;",
//      ">" => "&gt;",
   );
   return(strtr($str, $e));
}
function xml_decode_entities($str) {
   $e = array(
      "&lt;" => "<",
      "&gt;" => ">",
      "&apos;" => "'",
      "&quot;" => '"',
      "&amp;" => "&",
   );
   if (strpos($e, "&#") !== false) {
      $e = preg_replace('/&#(\d+);/e', 'chr($1)', $e);
      $e = preg_replace('/&#x([\da-fA-F]+);/e', 'chr(hexdec("$1"))', $e);
   }
   return(strtr($str, $e));
}


#-- Can split simplified XML into a PHP array structure. The now used
#   'compact' format will yield tag sub arrays with an "*,0" index and
#   just [",0"] for text nodes.
function xml2array($xml, $compact="ALWAYS") {
   $r = array();
   if (function_exists("xml_parser_create") && (strlen($xml) >= 512)) {
      $r = xml2array_php($xml);
   }
   else {
      xml2array_parse($xml, $r, $compact);
   }
   return($r);
}


#-- Recursively builds an array of the chunks fetched via strtok() from
#   the original XML input string.
function xml2array_parse(&$string, &$r, $compact=1) {
   $n = 0;
   do {
      #-- split chunks
      $l = strpos($string, "<");
      $p = strpos($string, ">", $l);
      $text = $attr=$close = $tag = false;
      if ($l === false) {
         $text = $string;
         $string = false;
      }
      else {
         $tag = strtok(substr($string, $l+1, $p-$l-1), " ");
         if ((strncmp($tag, "![CDATA[", 8)==0) && ($p = strpos($string, "]]>", $l))) {
            $text = substr($string, $l+9, $p-$l-9);
         }
         else {
            if ($l) {
               $text = xml_decode_entities(substr($string, 0, $l));
            }
            $attr = strtok("\000");
            $close = $attr && ($attr[strlen($attr)-1]=="/");
            $string = substr($string, $p+1);
         }
      }
      #-- insert text/body content into array
      if (trim($text)) {
#         if ($compact) {
             $r[",$n"] = $text;
#         }
#         else {
#            $r[] = $text;
#         }
         $n++;
      }
      #-- recurse for tags
      if ($tag && ($tag[0] >= 'A')) {    #-- don't read <? <! </ pseudo-tags
#         if ($compact) {
             $r["$tag,$n"] = array();
             $new = &$r["$tag,$n"];
#         } else {
#            $r[] = array($tag => array());
#            $new = &$r[count($r)-1][$tag];
#         }
         if (!$close) {
            xml2array_parse($string, $new, $compact);
         }
         $n++;
      }
      #-- stop if no more tags or content
      if (empty($tag) && empty($text) || empty($string)) {
         $tag = "/";
      }
   } while ($tag[0] != "/");
}


#-- Uses the XML extension of PHP to convert an XML stream into the
#   compact array representation.
function xml2array_php(&$xml, $compact=1) {

   $p = xml_parser_create(xml_which_charset($xml));
   xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, false);
   xml_parser_set_option($p, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");

   xml_parse_into_struct($p, $xml, $struct);

   $a = array();  // will hold all tag nodes
   $tree = array(&$a);  // stack of pointers to last node of any tree level
   $in = &$a;           // pointer to last created node

   foreach ($struct as $t) {
      unset($value);
      extract($t);

      $depth = count($tree) - 1;
      $in = &$tree[$depth];
      $tag .= "," . count($in);
//echo "#$depth, TAG=\"$tag\", TYP=$type, LEV=$level, VAL=$value\n";

      switch ($type[1]) {

         #--  OpEN
         case "p":
            $in[$tag] = array();
            if ($type=="open") {
               $tree[] = &$in[$tag];
            }
            if (isset($value) && trim($value)) {
               $in[$tag][",0"] = $value;
            }
            break;

         #--  CoMPLETE
         case "o":
            $in[$tag] = array();
            if (isset($value) && trim($value)) {
               $in[$tag][",0"] = $value;
            }
            break;

         #--  ClOSE
         case "l":
            array_pop($tree);
            break;

         #--  CdATA - usually just whitespace
         case "d":
            if (isset($value) && trim($value)) {
               $in[",".count($in)] = $value;
            }
            break;
         
         default:
            // case "attribute":
            // and anything else we do not want
      }
      
   }
   
   return($a);
}



function xml_which_charset(&$xml) {
   return( strpos(strtok($xml, "\n"), '-8859-1"') ? "iso-8859-1" : "utf-8" );
}



############################################################################
#                                                                          #
#  simplified XML creator                                                  #
#                                                                          #
############################################################################


#-- This is the opposite of the above xml2array, and can also work with the
#   so called $compact format.
function array2xml($r, $compact=1, $ins="") {
   $string = "<?xml version=\"1.0\" $ins?>";
   array2xml_push($string, $r, $compact);
   return($string);
}


#-- Recursively throws out the XMLified tree generated by the xml2array()
#   'parser' function.
function array2xml_push(&$string, &$r, $compact, $ind=-1) {
   $old_ind = ++$ind - 1;
   if ($old_ind < 0) { $old_ind = 0; }
   foreach ($r as $i=>$d) {
      $d = &$r[$i];
      if (is_scalar($d)) {
         $string .= xml_entities($d);
      }
      elseif (is_array($d)) {
         if ($compact) {
            $i = strtok($i, ","); 
         }
         $ls = str_repeat(" ", $ind);
         $string .= "\n$ls<$i>";
         $string .=  array2xml_push($string, $d, $compact, $ind);
         $ls = str_repeat(" ", $old_ind);
         $string .= "</$i>\n$ls";
      }
   }
}




?>