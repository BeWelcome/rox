<?php
/*
   Minimal raw RPC query support over HTTP. All variables are simply
   serialized() and then transmitted, minimal access permission checks
   are done (there is a $phprpc_methods like with the xmlrpc lib).
*/

define("PHPRPC_VERSION", 1);
define("PHPRPC_TYPE", "application/vnd.php.serialized");
@$ewiki_config["ua"] .= " phprpc/1";


#-- do call
function phprpc($url, $func, $args) {
   global $ewiki_config, $phprpc_error;

   #-- prepare
   $args = array(
      "methodName" => $func,
      "params" => $args,
   );
   $args = serialize($args);
   $args = gzdeflate($args);
   $len_args = strlen($args);

   #-- prepare HTTP request
   $c = parse_url($url);
   extract($c);
   ($port) || ($port=80);
   ($query) && ($path .= "?$query");
   $n = "\015\012";
   $req = "POST $path HTTP/1.0$n"
        . "Host: $host$n"
        . ($user ? "Authorization: Basic ".base64_encode("$user:$pass").$n : "")
        . "User-Agent: $ewiki_config[ua]$n"
        . "Accept-Encoding: deflate$n"
        . "Connection: close$n"
        . "Accept: ".PHPRPC_TYPE."$n"
        . "Content-Type: ".PHPRPC_TYPE."; version=".PHP_VERSION."$n"
        . "$n";

   #-- open connection
   if ($f = fsockopen($host, $port, $io_err, $io_err_s, 20)) {
      socket_set_blocking($f, true);
      socket_set_timeout($f, 17, 555);

      #-- send
      fwrite($f, $req);
      fwrite($f, "Content-Length: $len_args$n");
      fwrite($f, "$n");
      fwrite($f, $args);  $args = "";  //freemem()
      fwrite($f, "$n");

      #-- read
      $result = "";
      while (!feof($f)) {
         $result .= fread($f, 1<<21);  // max 2MB (incl. headers)
      }

      #-- strip headers
      while ($p = strpos($result, "\n")) {
         $line = trim(substr($result, 0, $p));
         $result = substr($result, $p + 1);
         if (!strlen($line)) {
            break;
         }
         $h[strtolower(strtok($line, ":"))] = trim(strtok("\000"));
      }
      fclose($f);
#print_r($h);
#print_r($result);

      #-- decode
      if (strtolower(trim(strtok($h["content-type"], ";"))) == PHPRPC_TYPE) {
         if ($h["content-encoding"] == "gzip") {
            $result = gzinflate(substr($result, 10, strlen($result)-18));
         }
         if ($h["content-encoding"] == "deflate") {
            $result = gzinflate($result);
         }
         $result = unserialize($result);

         #-- ok
         if ($result) {
            return($result);
         }
      }//decode

   } else { $phprpc_error = "no socket/connection"; } //socket
}



#-- handle calls
function phprpc_server($allowed="*") {

   global $phprpc_methods, $ewiki_config, $HTTP_RAW_POST_DATA;
   if ($phprpc_methods) {
      $allowed = $phprpc_methods;
   }

   if (($_SERVER["REQUEST_METHOD"] == "POST")
   and (strtolower(trim(strtok($_SERVER["CONTENT_TYPE"], ";"))) == PHPRPC_TYPE))
   {
       #-- get raw data
       if ($f = fopen("php://input", "rb")) {
          $HTTP_RAW_POST_DATA = fread($f, 1<<21);  // 2MB max (packed)
          fclose($f);
       }
       $call = unserialize(gzinflate($HTTP_RAW_POST_DATA));

       #-- make function call
       if (is_array($call)) {

          #-- params
          ($method = $call["method"]) || ($method = $call["func"]) || ($method = $call["function"]) || ($method = $call["methodName"]);
          ($args = $call["args"]) || ($args = $call["params"]);

          #-- plain function or static method call
          if (strpos($method, ":") || strpos($method, ".")) {
             $class = strtok($method, ":.");
             $method = trim(strtok(" "), ":");
          }
          if ($class) {
             $method = array($class, $method);
          }
          
          #-- exec, if
          if (($allowed=="*") || in_array(strtolower($method), $allowed)
          || in_array(strtolower($class), $allowed)
          || ($method = $allowed[strtolower($method)]))
          {
             $r = call_user_func_array($method, $args);
          }
          else {
             header("Status: 400 Forbidden Method"); exit;
          }
       }
       else {
          header("Status: 500 Could Not Unserialize"); exit;
       }

       #-- return result
       if ($r) {
          header("X-Server: $ewiki_config[ua]");
          header("Content-Type: ".PHPRPC_TYPE."; version=".PHP_VERSION);
          header("Cache-Control: no-cache, private");
          $r = serialize($r);
          $r = gzdeflate($r);
          header("Content-Encoding: deflate");
          header("Content-Length: ".strlen($r));
          print($r);
          exit;
       }

       #-- error
       header("Status: 500 Didn't Work");
       exit;
   }
   else {
      header("X-PHP-RPC-Error: Wrong request method $_SERVER[REQUEST_METHOD] and/or type $_SERVER[CONTENT_TYPE]");
   }
}

?>