<?php
/*
   This include() script adds missing PHP functions to earlier interpreter
   versions, so you can make downwards compatible scripts without having
   to stick to the least common denominator. It only defines the ones that
   are really missing - the faster native functions will be used whenever
   available.

   - many of the emulation functions are one-liners
   - a few features have been added that never made it into one of the
     official versions (CVS code and the ever-absent "gzdecode" and
     "file_put_contents" for example)
   - a few very extravagant functions (array_u?diff*_u*assoc?) and other
     extensions have been separated out into ext/
   - the advanced OO-capabilities and language syntax extensions of PHP5
     and ZE2 cannot seriously be emulated here, this script only takes care
     of procedural interfaces
   - with only this part loaded, you get "PHP 4.1 COMPATIBILITY"
   - this is PuplicDomain (no copyright, no license, no warranty) so you
     can melt it into anything, regardless of your preferred license (you
     may strip this paragraph and turn it all into GPL, BSD, GNU LGPL,
     Artistic, MPL, PHP license, M$ EULA, or whatever you like best)
   
   Get update notes via "http://freshmeat.net/projects/upgradephp" or
   google for it. Any contribution is appreciated. <milky*users·sf·net>
*/



#------------------------------------------------------------------ CVS ---
// most of this appeared in 5.0
// ...




#------------------------------------------------------------------ 6.0 ---
// following functions were never implemented in PHP


#-- inflates a string enriched with gzip headers
#   (this is the logical counterpart to gzencode(), but don't tell anyone!)
if (!function_exists("gzdecode")) {
   function gzdecode($data, $maxlen=NULL) {

      #-- decode header
      $len = strlen($data);
      if ($len < 20) {
         return;
      }
      $head = substr($data, 0, 10);
      $head = unpack("n1id/C1cm/C1flg/V1mtime/C1xfl/C1os", $head);
      list($ID, $CM, $FLG, $MTIME, $XFL, $OS) = array_values($head);
      $FTEXT = 1<<0;
      $FHCRC = 1<<1;
      $FEXTRA = 1<<2;
      $FNAME = 1<<3;
      $FCOMMENT = 1<<4;
      $head = unpack("V1crc/V1isize", substr($data, $len-8, 8));
      list($CRC32, $ISIZE) = array_values($head);

      #-- check gzip stream identifier
      if ($ID != 0x1f8b) {
         trigger_error("gzdecode: not in gzip format", E_USER_WARNING);
         return;
      }
      #-- check for deflate algorithm
      if ($CM != 8) {
         trigger_error("gzdecode: cannot decode anything but deflated streams", E_USER_WARNING);
         return;
      }

      #-- start of data, skip bonus fields
      $s = 10;
      if ($FLG & $FEXTRA) {
         $s += $XFL;
      }
      if ($FLG & $FNAME) {
         $s = strpos($data, "\000", $s) + 1;
      }
      if ($FLG & $FCOMMENT) {
         $s = strpos($data, "\000", $s) + 1;
      }
      if ($FLG & $FHCRC) {
         $s += 2;  // cannot check
      }
      
      #-- get data, uncompress
      $data = substr($data, $s, $len-$s);
      if ($maxlen) {
         $data = gzinflate($data, $maxlen);
         return($data);  // no checks(?!)
      }
      else {
         $data = gzinflate($data);
      }
      
      #-- check+fin
      $chk = crc32($data);
      if ($CRC32 != $chk) {
         trigger_error("gzdecode: checksum failed (real$chk != comp$CRC32)", E_USER_WARNING);
      }
      elseif ($ISIZE != strlen($data)) {
         trigger_error("gzdecode: stream size mismatch", E_USER_WARNING);
      }
      else {
         return($data);
      }
   }
}


#-- get all already made headers(),
#   CANNOT be emulated, because output buffering functions
#   already swallow up any sent http header
if (!function_exists("ob_get_headers")) {
   function ob_get_headers() {
      return (array)NULL;
   }
}


#-- encodes required named XML entities, like htmlentities(),
#   but does not re-encode numeric &#xxxx; character references
#   - could screw up scripts which then implement this themselves
#   - doesn't fix bogus or invalid numeric entities
if (!function_exists("xmlentities")) {
   function xmlentities($str) {
      return strtr($str, array(
        "&#"=>"&#", "&"=>"&amp;", "'"=>"&apos;",
        "<"=>"&lt;", ">"=>"&gt;", "\""=>"&quot;", 
      ));
   }
}



#------------------------------------------------------------------ 5.0 ---
# set_exception_handler - unimpl.
# restore_exception_handler - unimpl.
# debug_print_backtrace - unimpl.
# class_implements - unimplementable
# proc_terminate - unimpl?
# proc_get_status - unimpl.
# --
# proc_nice
# dns_get_record
# date_sunrise - undoc.
# date_sunset - undoc.



#-- constant: end of line
if (!defined("PHP_EOL")) {
   define("PHP_EOL", ( (DIRECTORY_SEPARATOR == "\\") ?"\015\012" :(strncmp(PHP_OS,"D",1)?"\012":"\015") )  ); #"
}


#-- case-insensitive string search function,
#   - finds position of first occourence of a string c-i
#   - parameters identical to strpos()
if (!function_exists("stripos")) {
   function stripos($haystack, $needle, $offset=NULL) {
   
      #-- simply lowercase args
      $haystack = strtolower($haystack);
      $needle = strtolower($needle);
      
      #-- search
      $pos = strpos($haystack, $needle, $offset);
      return($pos);
   }
}


#-- case-insensitive string search function
#   - but this one starts from the end of string (right to left)
#   - offset can be negative or positive
if (!function_exists("strripos")) {
   function strripos($haystack, $needle, $offset=NULL) {

      #-- lowercase incoming strings
      $haystack = strtolower($haystack);
      $needle = strtolower($needle);

      #-- [-]$offset tells to ignore a few string bytes,
      #   we simply cut a bit from the right
      if (isset($offset) && ($offset < 0)) {
         $haystack = substr($haystack, 0, strlen($haystack) - 1);
      }

      #-- let PHP do it
      $pos = strrpos($haystack, $needle);

      #-- [+]$offset => ignore left haystack bytes
      if (isset($offset) && ($offset > 0) && ($pos > $offset)) {
         $pos = false;
      }

      #-- result      
      return($pos);
   }
}


#-- case-insensitive version of str_replace
if (!function_exists("str_ireplace")) {
   function str_ireplace($search, $replace, $subject, $count=NULL) {

      #-- call ourselves recursively, if parameters are arrays/lists 
      if (is_array($search)) {
         $replace = array_values($replace);
         foreach (array_values($search) as $i=>$srch) {
            $subject = str_ireplace($srch, $replace[$i], $subject);
         }
      }
      
      #-- sluice replacement strings through the Perl-regex module
      #   (faster than doing it by hand)
      else {
         $replace = addcslashes($replace, "$\\");
         $search = "{" . preg_quote($search) . "}i";
         $subject = preg_replace($search, $replace, $subject);
      }

      #-- result
      return($subject);
   }
}


#-- performs a http HEAD request
if (!function_exists("get_headers")) {
   function get_headers($url, $parse=0) {
   
      #-- extract URL parts ($host, $port, $path, ...)
      $c = parse_url($url);
      extract($c);
      if (!isset($port)) { 
         $port = 80;
      }
      
      #-- try to open TCP connection      
      $f = fsockopen($host, $port, $errno, $errstr, $timeout=15);
      if (!$f) {
         return;
      }

      #-- send request header
      socket_set_blocking($f, true);
      fwrite($f, "HEAD $path HTTP/1.0\015\012"
               . "Host: $host\015\012"
               . "Connection: close\015\012"
               . "Accept: */*, xml/*\015\012"
               . "User-Agent: ".trim(ini_get("user_agent"))."\015\012"
               . "\015\012");

      #-- read incoming lines
      $ls = array();
      while ( !feof($f) && ($line = trim(fgets($f, 1<<16))) ) {
         
         #-- read header names to make result an hash (names in array index)
         if ($parse) {
            if ($l = strpos($line, ":")) {
               $name = substr($line, 0, $l);
               $value = trim(substr($line, $l + 1));
               #-- merge headers
               if (isset($ls[$name])) {
                  $ls[$name] .= ", $value";
               }
               else {
                  $ls[$name] = $value;
               }
            }
            #-- HTTP response status header as result[0]
            else {
               $ls[] = $line;
            }
         }
         
         #-- unparsed header list (numeric indices)
         else {
            $ls[] = $line;
         }
      }

      #-- close TCP connection and give result
      fclose($f);
      return($ls);
   }
}


#-- list of already/potentially sent HTTP responsee headers(),
#   CANNOT be implemented (except for Apache module maybe)
if (!function_exists("headers_list")) {
   function headers_list() {
      trigger_error("headers_list(): not supported by this PHP version", E_USER_WARNING);
      return (array)NULL;
   }
}


#-- write formatted string to stream/file,
#   arbitrary numer of arguments
if (!function_exists("fprintf")) {
   function fprintf(/*...*/) {
      $args = func_get_args();
      $stream = array_shift($args);
      return fwrite($stream, call_user_func_array("sprintf", $args));
   }
}


#-- write formatted string to stream, args array
if (!function_exists("vfprintf")) {
   function vfprintf($stream, $format, $args=NULL) {
      return fwrite($stream, vsprintf($format, $args));
   }
}


#-- splits a string in evenly sized chunks
#   and returns this as array
if (!function_exists("str_split")) {
   function str_split($str, $chunk=1) {
      $r = array();
      
      #-- return back as one chunk completely, if size chosen too low
      if ($chunk < 1) {
         $r[] = $str;
      }
      
      #-- add substrings to result array until subject strings end reached
      else {
         $len = strlen($str);
         for ($n=0; $n<$len; $n+=$chunk) {
            $r[] = substr($str, $n, $chunk);
         }
      }
      return($r);
   }
}


#-- constructs a QUERY_STRING (application/x-www-form-urlencoded format, non-raw)
#   from a nested array/hash with name=>value pairs
#   - only first two args are part of the original API - rest used for recursion
if (!function_exists("http_build_query")) {
   function http_build_query($data, $int_prefix="", $subarray_pfix="", $level=0) {
   
      #-- empty starting string
      $s = "";
      ($SEP = ini_get("arg_separator.output")) or ($SEP = "&");
      
      #-- traverse hash/array/list entries 
      foreach ($data as $index=>$value) {
         
         #-- add sub_prefix for subarrays (happens for recursed innovocation)
         if ($subarray_pfix) {
            if ($level) {
               $index = "[" . $index . "]";
            }
            $index =  $subarray_pfix . $index;
         }
         #-- add user-specified prefix for integer-indices
         elseif (is_int($index) && strlen($int_prefix)) {
            $index = $int_prefix . $index;
         }
         
         #-- recurse for sub-arrays
         if (is_array($value)) {
            $s .= http_build_query($value, "", $index, $level + 1);
         }
         else {   // or just literal URL parameter
            $s .= $SEP . $index . "=" . urlencode($value);
         }
      }
      
      #-- remove redundant "&" from first round (-not checked above to simplifiy loop)
      if (!$subarray_pfix) {
         $s = substr($s, strlen($SEP));
      }

      #-- return result / to previous array level and iteration
      return($s);
   }
}


#-- transform into 3to4 uuencode
#   - this is the bare encoding, not the uu file format
if (!function_exists("convert_uuencode")) {
   function convert_uuencode($data) {

      #-- init vars
      $out = "";
      $line = "";
      $len = strlen($data);
#      $data .= "\252\252\252";   // PHP and uuencode(1) use some special garbage??, looks like "\000"* and "`\n`" simply appended

      #-- canvass source string
      for ($n=0; $n<$len; ) {
      
         #-- make 24-bit integer from first three bytes
         $x = (ord($data[$n++]) << 16)
            + (ord($data[$n++]) <<  8)
            + (ord($data[$n++]) <<  0);
            
         #-- disperse that into 4 ascii characters
         $line .= chr( 32 + (($x >> 18) & 0x3f) )
                . chr( 32 + (($x >> 12) & 0x3f) )
                . chr( 32 + (($x >>  6) & 0x3f) )
                . chr( 32 + (($x >>  0) & 0x3f) );
                
         #-- cut lines, inject count prefix before each
         if (($n % 45) == 0) {
            $out .= chr(32 + 45) . "$line\n";
            $line = "";
         }
      }

      #-- throw last line, +length prefix
      if ($trail = ($len % 45)) {
         $out .= chr(32 + $trail) . "$line\n";
      }

      // uuencode(5) doesn't tell so, but spaces are replaced with the ` char in most implementations
      $out = strtr("$out \n", " ", "`");
      return($out);
   }
}


#-- decodes uuencoded() data again
if (!function_exists("convert_uudecode")) {
   function convert_uudecode($data) {

      #-- prepare
      $out = "";
      $data = strtr($data, "`", " ");
      
      #-- go through lines
      foreach(explode("\n", ltrim($data)) as $line) {
         if (!strlen($line)) {
            break;  // end reached
         }
         
         #-- current line length prefix
         unset($num);
         $num = ord($line{0}) - 32;
         if (($num <= 0) || ($num > 62)) {  // 62 is the maximum line length
            break;          // according to uuencode(5), so we stop here too
         }
         $line = substr($line, 1);
         
         #-- prepare to decode 4-char chunks
         $add = "";
         for ($n=0; strlen($add)<$num; ) {
         
            #-- merge 24 bit integer from the 4 ascii characters (6 bit each)
            $x = ((ord($line[$n++]) - 32) << 18)
               + ((ord($line[$n++]) - 32) << 12)  // were saner with "& 0x3f"
               + ((ord($line[$n++]) - 32) <<  6)
               + ((ord($line[$n++]) - 32) <<  0);
               
            #-- reconstruct the 3 original data chars
            $add .= chr( ($x >> 16) & 0xff )
                  . chr( ($x >>  8) & 0xff )
                  . chr( ($x >>  0) & 0xff );
         }

         #-- cut any trailing garbage (last two decoded chars may be wrong)
         $out .= substr($add, 0, $num);
         $line = "";
      }

      return($out);
   }
}


#-- return array of filenames in a given directory
#   (only works for local files)
if (!function_exists("scandir")) {
   function scandir($dirname, $desc=0) {
   
      #-- check for file:// protocol, others aren't handled
      if (strpos($dirname, "file://") === 0) {
         $dirname = substr($dirname, 7);
         if (strpos($dirname, "localh") === 0) {
            $dirname = substr($dirname, strpos($dirname, "/"));
         }
      }
      
      #-- directory reading handle
      if ($dh = opendir($dirname)) {
         $ls = array();
         while ($fn = readdir($dh)) {
            $ls[] = $fn;  // add to array
         }
         closedir($dh);
         
         #-- sort filenames
         if ($desc) {
            rsort($ls);
         }
         else {
            sort($ls);
         }
         return $ls;
      }

      #-- failure
      return false;
   }
}


#-- like date(), but returns an integer for given one-letter format parameter
if (!function_exists("idate")) {
   function idate($formatchar, $timestamp=NULL) {
   
      #-- reject non-simple type parameters
      if (strlen($formatchar) != 1) {
         return false;
      }
      
      #-- get current time, if not given
      if (!isset($timestamp)) {
         $timestamp = time();
      }
      
      #-- get and turn into integer
      $str = date($formatchar, $timestamp);
      return (int)$str;
   }
}



#-- combined sleep() and usleep() 
if (!function_exists("time_nanosleep")) {
   function time_nanosleep($sec, $nano) {
      sleep($sec);
      usleep($nano);
   }
}



#-- search first occourence of any of the given chars, returns rest of haystack
#   (char_list must be a string for compatibility with the real PHP func)
if (!function_exists("strpbrk")) {
   function strpbrk($haystack, $char_list) {
   
      #-- prepare
      $len = strlen($char_list);
      $min = strlen($haystack);
      
      #-- check with every symbol from $char_list
      for ($n = 0; $n < $len; $n++) {
         $l = strpos($haystack, $char_list{$n});
         
         #-- get left-most occourence
         if (($l !== false) && ($l < $min)) {
            $min = $l;
         }
      }
      
      #-- result
      if ($min) {
         return(substr($haystack, $min));
      }
      else {
         return(false);
      }
   }
}



#-- logo image activation URL query strings (gaga feature)
if (!function_exists("php_real_logo_guid")) {
   function php_real_logo_guid() { return php_logo_guid(); }
   function php_egg_logo_guid() { return zend_logo_guid(); }
}


#-- no need to implement this
#   (there aren't interfaces in PHP4 anyhow)
if (!function_exists("get_declared_interfaces")) {
   function get_declared_interfaces() {
      trigger_error("get_declared_interfaces(): Current script won't run reliably with PHP4.", E_USER_WARNING);
      return( (array)NULL );
   }
}


#-- creates an array from lists of $keys and $values
#   (both should have same number of entries)
if (!function_exists("array_combine")) {
   function array_combine($keys, $values) {
   
      #-- convert input arrays into lists
      $keys = array_values($keys);
      $values = array_values($values);
      $r = array();
      
      #-- one from each
      foreach ($values as $i=>$val) {
         if ($key = $keys[$i]) {
            $r[$key] = $val;
         }
         else {
            $r[] = $val;   // useless, PHP would have long aborted here
         }
      }
      return($r);
   }
}


#-- apply userfunction to each array element (descending recursively)
#   use it like:  array_walk_recursive($_POST, "stripslashes");
#   - $callback can be static function name or object/method, class/method
if (!function_exists("array_walk_recursive")) {
   function array_walk_recursive(&$input, $callback, $userdata=NULL) {
      #-- each entry
      foreach ($input as $key=>$value) {

         #-- recurse for sub-arrays
         if (is_array($value)) {
            array_walk_recursive($input[$key], $callback, $userdata);
         }

         #-- $callback handles scalars
         else {
            call_user_func_array($callback, array(&$input[$key], $key, $userdata) );
         }
      }

      // no return value
   }
}


#-- complicated wrapper around substr() and and strncmp()
if (!function_exists("substr_compare")) {
   function substr_compare($haystack, $needle, $offset=0, $len=0, $ci=0) {

      #-- check params   
      if ($len <= 0) {   // not well documented
         $len = strlen($needle);
         if (!$len) { return(0); }
      }
      #-- length exception
      if ($len + $offset >= strlen($haystack)) {
         trigger_error("substr_compare: given length exceeds main_str", E_USER_WARNING);
         return(false);
      }

      #-- cut
      if ($offset) {
         $haystack = substr($haystack, $offset, $len);
      }
      #-- case-insensitivity
      if ($ci) {
         $haystack = strtolower($haystack);
         $needle = strtolower($needle);
      }

      #-- do
      return(strncmp($haystack, $needle, $len));
   }
}


#-- stub, returns empty list as usual;
#   you must load "ext/spl.php" beforehand to get this
if (!function_exists("spl_classes")) {
   function spl_classes() {
      trigger_error("spl_classes(): not built into this PHP version");
      return (array)NULL;
   }
}



#-- gets you list of class names the given objects class was derived from, slow
if (!function_exists("class_parents")) {
   function class_parents($obj) {
   
      #-- first get full list
      $all = get_declared_classes();
      $r = array();
      
      #-- filter out
      foreach ($all as $potential_parent) {
         if (is_subclass_of($obj, $potential_parent)) {
            $r[$potential_parent] = $potential_parent;
         }
      }
      return($r);
   }
}


#-- an alias
if (!function_exists("session_commit") && function_exists("session_write_close")) {
   function session_commit() {
      // simple
      session_write_close();
   }
}


#-- aliases
if (!function_exists("dns_check_record")) {
   function dns_check_record($host, $type=NULL) {
      // synonym to
      return checkdnsrr($host, $type);
   }
}
if (!function_exists("dns_get_mx")) {
   function dns_get_mx($host, $mx) {
      $args = func_get_args();
      // simple alias - except the optional, but referenced third parameter
      if ($args[2]) {
         $w = & $args[2];
      }
      else {
         $w = false;
      }
      return getmxrr($host, $mx, $w);
   }
}


#-- setrawcookie(),
#   can this be emulated 100% exactly?
if (!function_exists("setrawcookie")) {
   // we output everything directly as HTTP header(), PHP doesn't seem
   // to manage an internal cookie list anyhow
   function setrawcookie($name, $value=NULL, $expire=NULL, $path=NULL, $domain=NULL, $secure=0) {
      if (isset($value) && strpbrk($value, ",; \r\t\n\f\014\013")) {
         trigger_error("setrawcookie: value may not contain any of ',; \r\n' and some other control chars; thrown away", E_USER_WARNING);
      }
      else {
         $h = "Set-Cookie: $name=$value"
            . ($expire ? "; expires=" . gmstrftime("%a, %d-%b-%y %H:%M:%S %Z", $expire) : "")
            . ($path ? "; path=$path": "")
            . ($domain ? "; domain=$domain" : "")
            . ($secure ? "; secure" : "");
         header($h);
      }
   }
}


#-- write-at-once file access (counterpart to file_get_contents)
if (!function_exists("file_put_contents")) {
   function file_put_contents($filename, $data, $flags=0, $resource=NULL) {

      #-- prepare
      $mode = ($flags & FILE_APPEND ? "a" : "w" ) ."b";
      $incl = $flags & FILE_USE_INCLUDE_PATH;
      $length = strlen($data);

      #-- open for writing
      $f = fopen($filename, $mode, $incl);
      if ($f) {
         $written = fwrite($f, $data);
         fclose($f);
         
         #-- only report success, if completely saved
         return($length == $written);
      }
   }
}


#-- file-related constants
if (!defined("FILE_APPEND")) {
   define("FILE_USE_INCLUDE_PATH", 1);
   define("FILE_IGNORE_NEW_LINES", 2);
   define("FILE_SKIP_EMPTY_LINES", 4);
   define("FILE_APPEND", 8);
   define("FILE_NO_DEFAULT_CONTEXT", 16);
}


#-- more new constants for 5.0
if (!defined("E_STRICT")) {
   define("E_STRICT", 2048);  // _STRICT is a special case of _NOTICE (_DEBUG)
   # PHP_CONFIG_FILE_SCAN_DIR
}


#-- array count_recursive()
if (!defined("COUNT_RECURSIVE")) {
   define("COUNT_NORMAL", 0);       // count($array, 0);
   define("COUNT_RECURSIVE", 1);    // not supported
}


#-- we introduce a new function, because we cannot emulate the
#   newly introduced second parameter to count()
if (!function_exists("count_recursive")) {
   function count_recursive($array, $mode=1) {
      if (!$mode) {
         return(count($array));
      }
      else {
         $c = count($array);
         foreach ($array as $sub) {
            if (is_array($sub)) {
               $c += count_recursive($sub);
            }
         }
         return($c);
      }
   }
}







#------------------------------------------------------------------ 4.3 ---
# money_format - unimpl?
# sha1, sha1_file - too much code to pack it into here; and this
#                   has already been implemented elsewhere, btw


#-- simplified file read-at-once function
if (!function_exists("file_get_contents")) {
   function file_get_contents($filename, $use_include_path=1) {

      #-- open file, let fopen() report error
      $f = fopen($filename, "rb", $use_include_path);
      if (!$f) { return; }

      #-- read max 2MB
      $content = fread($f, 1<<21);
      fclose($f);
      return($content);
   }
}



#-- shell-like filename matching (* and ? globbing characters)
if (!function_exists("fnmatch")) {

   #-- associated constants
   define("FNM_PATHNAME", 1<<0);  // no wildcard ever matches a "/"
   define("FNM_NOESCAPE", 1<<1);  // backslash can't escape meta chars
   define("FNM_PERIOD",   1<<2);  // leading dot must be given explicit
   define("FNM_LEADING_DIR", 1<<3);  // not in PHP
   define("FNM_CASEFOLD", 0x50);  // match case-insensitive
   define("FNM_EXTMATCH", 1<<5);  // not in PHP
   
   #-- implementation
   function fnmatch($pattern, $str, $flags=0x0000) {
      
      #-- 'hidden' files
      if ($flags & FNM_PERIOD) {
         if (($str[0] == ".") && ($pattern[0] != ".")) {
            return(false);    // abort early
         }
      }

      #-- case-insensitivity
      $rxci = "";
      if ($flags & FNM_CASEFOLD) {
         $rxci = "i";
      }
      #-- handline of pathname separators (/)
      $wild = ".";
      if ($flags & FNM_PATHNAME) {
         $wild = "[^/".DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR."]";
      }

      #-- check for cached regular expressions
      static $cmp = array();
      if (isset($cmp["$pattern+$flags"])) {
         $rx = $cmp["$pattern+$flags"];
      }

      #-- convert filename globs into regex
      else {
         $rx = preg_quote($pattern);
         $rx = strtr($rx, array(
            "\\*"=>"$wild*?", "\\?"=>"$wild", "\\["=>"[", "\\]"=>"]",
         ));
         $rx = "{^" . $rx . "$}" . $rxci;
         
         #-- cache
         if (count($cmp) >= 50) {
            $cmp = array();   // free
         }
         $cmp["$pattern+$flags"] = $rx;
      }
      
      #-- compare
      return(preg_match($rx, $str));
   }
}


#-- file search and name matching (with shell patterns)
if (!function_exists("glob")) {

   #-- introduced constants
   define("GLOB_MARK", 1<<0);
   define("GLOB_NOSORT", 1<<1);
   define("GLOB_NOCHECK", 1<<2);
   define("GLOB_NOESCAPE", 1<<3);
   define("GLOB_BRACE", 1<<4);
   define("GLOB_ONLYDIR", 1<<5);
   define("GLOB_NOCASE", 1<<6);
   define("GLOB_DOTS", 1<<7);
   // unlikely to work under Win(?), without replacing the explode() with
   // a preg_split() incorporating the native DIRECTORY_SEPARATOR as well

   #-- implementation
   function glob($pattern, $flags=0x0000) {
      $ls = array();
      $rxci = ($flags & GLOB_NOCASE) ? "i" : "";
#echo "\n=> glob($pattern)...\n";
      
      #-- transform glob pattern into regular expression
      #   (similar to fnmatch() but still different enough to require a second func)
      if ($pattern) {

         #-- look at each directory/fn spec part separately
         $parts2 = explode("/", $pattern);
         $pat = preg_quote($pattern);
         $pat = strtr($pat, array("\\*"=>".*?", "\\?"=>".?"));
         if ($flags ^ GLOB_NOESCAPE) {
            // uh, oh, ouuch - the above is unclean enough...
         }
         if ($flags ^ GLOB_BRACE) {
            $pat = preg_replace("/\{(.+?)\}/e", 'strtr("[$1]", ",", "")', $pat);
         }
         $parts = explode("/", $pat);
#echo "parts == ".implode(" // ", $parts) . "\n";
         $lasti = count($parts) - 1;
         $dn = "";
         foreach ($parts as $i=>$p) {

            #-- basedir included (yet no pattern matching necessary)
            if (!strpos($p, "*?") && (strpos($p, ".?")===false)) {
               $dn .= $parts2[$i] . ($i!=$lasti ? "/" : "");
#echo "skip:$i, cause no pattern matching char found -> only a basedir spec\n";
               continue;
            }
            
            #-- start reading dir + match filenames against current pattern
            if ($dh = opendir($dn ?$dn:'.')) {
               $with_dot = ($p[1]==".") || ($flags & GLOB_DOTS);
#echo "part:$i:$p\n";
#echo "reading dir \"$dn\"\n";
               while ($fn = readdir($dh)) {
                  if (preg_match("\007^$p$\007$rxci", $fn)) {

                     #-- skip over 'hidden' files
                     if (($fn[0] == ".") && !$with_dot) {
                        continue;
                     }

                     #-- add filename only if last glob/pattern part
                     if ($i==$lasti) {
                        if (is_dir("$dn$fn")) {
                           if ($flags & GLOB_ONLYDIR) {
                              continue;
                           }
                           if ($flags & GLOB_MARK) {
                              $fn .= "/";
                           }
                        }
#echo "adding '$fn' for dn=$dn to list\n";
                        $ls[] = "$dn$fn";
                     }

                     #-- initiate a subsearch, merge result list in
                     elseif (is_dir("$dn$fn")) {
                        // add reamaining search patterns to current basedir
                        $remaind = implode("/", array_slice($parts2, $i+1));
                        $ls = array_merge($ls, glob("$dn$fn/$remaind", $flags));
                     }
                  }
               }
               closedir($dh);

               #-- prevent scanning a 2nd part/dir in same glob() instance:
               break;  
            }

            #-- given dirname doesn't exist
            else {
               return($ls);
            }

         }// foreach $parts
      }

      #-- return result list
      if (!$ls && ($flags & GLOB_NOCHECK)) {
         $ls[] = $pattern;
      }
      if ($flags ^ GLOB_NOSORT) {
         sort($ls);
      }
#print_r($ls);
#echo "<=\n";
      return($ls);
   }
} //@FIX: fully comment, remove debugging code (- as soon as it works ;)


#-- redundant alias for isset()
if (!function_exists("array_key_exists")) {
   function array_key_exists($key, $search) {
      return isset($search[$key]);
   }
}


#-- who could need that?
if (!function_exists("array_intersect_assoc")) {
   function array_intersect_assoc( /*array, array, array...*/ ) {

      #-- parameters, prepare
      $in = func_get_args();
      $cmax = count($in);
      $whatsleftover = array();
      
      #-- walk through each array pair
      #   (take first as checklist)
      foreach ($in[0] as $i => $v) {
         for ($c = 1; $c < $cmax; $c++) {
            #-- remove entry, as soon as it isn't present
            #   in one of the other arrays
            if (!isset($in[$c][$i]) || (@$in[$c][$i] !== $v)) {
               continue 2;
            }
         }
         #-- it was found in all other arrays
         $whatsleftover[$i] = $v;
      }
      return $whatsleftover;
   }
}


#-- the opposite of the above
if (!function_exists("array_diff_assoc")) {
   function array_diff_assoc( /*array, array, array...*/ ) {

      #-- params
      $in = func_get_args();
      $diff = array();
      
      #-- compare each array with primary/first
      foreach ($in[0] as $i=>$v) {
         for ($c=1; $c<count($in); $c++) {
            #-- skip as soon as it matches with entry in another array
            if (isset($in[$c][$i]) && ($in[$c][$i] == $v)) {
               continue 2;
            }
         }
         #-- else
         $diff[$i] = $v;
      }
      return $diff;
   }
}


#-- opposite of htmlentities
if (!function_exists("html_entity_decode")) {
   function html_entity_decode($string, $quote_style=ENT_COMPAT, $charset="ISO-8859-1") {
      //@FIX: we fall short on anything other than Latin-1
      $y = array_flip(get_html_translation_table(HTML_ENTITIES, $quote_style));
      return strtr($string, $y);
   }
}


#-- extracts single words from a string
if (!function_exists("str_word_count")) {
   function str_word_count($string, $result=0) {
   
      #-- let someone else do the work
      preg_match_all('/([\w](?:[-\'\w]?[\w]+)*)/', $string, $uu);

      #-- return full word list
      if ($result == 1) {
         return($uu[1]);
      }
      
      #-- array() of $pos=>$word entries
      elseif ($result >= 2) {
         $r = array();
         $l = 0;
         foreach ($uu[1] as $word) {
            $l = strpos($string, $word, $l);
            $r[$l] = $word;
            $l += strlen($word);  // speed up next search
         }
         return($r);
      }

      #-- only count
      else {
         return(count($uu[1]));
      }
   }
}


#-- creates a permutation of the given strings characters
#   (let's hope the random number generator was alread initialized)
if (!function_exists("str_shuffle")) {
   function str_shuffle($str) {
      $r = "";

      #-- cut string down with every iteration
      while (strlen($str)) {
         $n = strlen($str) - 1;
         if ($n) {
            $n = rand(0, $n);   // glibcs` rand is ok since 2.1 at least
         }
         
         #-- cut out elected char, add to result string
         $r .= $str{$n};
         $str = substr($str, 0, $n) . substr($str, $n + 1);
      }
      return($r);
   }
}


#-- simple shorthands
if (!function_exists("get_include_path")) {
   function get_include_path() {
      return(get_cfg_var("include_path"));
   }
   function set_include_path($new) {
      return ini_set("include_path", $new);
   }
   function restore_include_path() {
      ini_restore("include_path");
   }
}


#-- constants for 4.3
if (!defined("PATH_SEPARATOR")) {
   define("PATH_SEPARATOR", ((DIRECTORY_SEPARATOR=='\\') ? ';' :':'));
   define("PHP_SHLIB_SUFFIX", ((DIRECTORY_SEPARATOR=='\\') ? 'dll' :'so'));
}
if (!defined("PHP_SAPI")) {
   define("PHP_SAPI", php_sapi_name());
}


#-- not identical to what PHP reports (it seems to `which` for itself)
if (!defined("PHP_PREFIX") && isset($_ENV["_"])) { 
   define("PHP_PREFIX", substr($_ENV["_"], 0, strpos($_ENV["_"], "bin/")));
}






#------------------------------------------------------------------ 4.2 ---
# almost complete!?


#-- shy away from this one - it was broken in all real PHP4.2 versions, and
#   this function emulation script won't change that
if (!function_exists("str_rot13")) {
   function str_rot13($str) {
      static $from = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
      static $to = "NOPQRSTUVWXYZABCDEFGHIJKLMnopqrstuvwxyzabcdefghijklm";
      return strtr($str, $from, $to);
   }
}


#-- well, if you need it
if (!function_exists("array_change_key_case")) {
   
   #-- introduced constants
   define("CASE_LOWER", 0);
   define("CASE_UPPER", 1);
   
   #-- implementation
   function array_change_key_case($array, $case=CASE_LOWER) {
   
      #-- loop through
      foreach ($array as $i=>$v) {
         #-- do anything for strings only
         if (is_string($i)) {
            unset($array[$i]);
            $i = ($case==CASE_LOWER) ? strtolower($i) : strtoupper($i);
            $array[$i] = $v;
         }
         // non-recursive      
      }
      return($array);
   }
}


#-- create fixed-length array made up of $value data
if (!function_exists("array_fill")) {
   function array_fill($start_index, $num, $value) {

      #-- params
      $r = array();
      $i = $start_index;
      $end = $num + $start_index;
      
      #-- append
      for (; $i < $end; $i++)
      {
         $r[$i] = $value;
      }
      return($r);
   }
}


#-- split an array into evenly sized parts
if (!function_exists("array_chunk")) {
   function array_chunk($input, $size, $preserve_keys=false) {
   
      #-- array for chunked output
      $r = array();
      $n = -1;  // chunk index
      
      #-- enum input array blocks
      foreach ($input as $i=>$v) {
      
         #-- new chunk
         if (($n < 0) || (count($r[$n]) == $size)) {
            $n++;
            $r[$n] = array();
         }
         
         #-- add input value into current [$n] chunk
         if ($preserve_keys) {
            $r[$n][$i] = $v;
         }
         else {
            $r[$n][] = $v;
         }
      }
      return($r);
   }
}


#-- convenience wrapper
if (!function_exists("md5_file")) {
   function md5_file($filename, $raw_output=false) {

      #-- read file, apply hash function
      $data = file_get_contents($filename, "rb");
      $r = md5($data);
      $data = NULL;
         
      #-- transform? and return
      if ($raw_output) {
         $r = pack("H*", $r);
      }
      return $r;
   }
}


#-- object type checking
if (!function_exists("is_a")) {
   function is_a($obj, $classname) {
   
      #-- lowercase everything for comparison
      $classnaqme = strtolower($classname);
      $obj_class =  strtolower(get_class($obj));
      
      #-- two possible checks
      return ($obj_class == $classname) or is_subclass_of($obj, $classname);
   }
}


#-- floating point modulo
if (!function_exists("fmod")) {
   function fmod($x, $y) {
      $r = $x / $y;
      $r -= (int)$r;
      $r *= $y;
      return($r);
   }
}


#-- makes float variable from string
if (!function_exists("floatval")) {
   function floatval($str) {
      $str = ltrim($str);
      return (float)$str;
   }
}


#-- floats
if (!function_exists("is_infinite")) {

   #-- constants as-is
   define("NAN", "NAN");
   define("INF", "INF");   // there is also "-INF"
   
   #-- simple checks
   function is_infinite($f) {
      $s = (string)$f;
      return(  ($s=="INF") || ($s=="-INF")  );
   }
   function is_nan($f) {
      $s = (string)$f;
      return(  $s=="NAN"  );
   }
   function is_finite($f) {
      $s = (string)$f;
      return(  !strpos($s, "N")  );
   }
}


#-- throws value-instantiation PHP-code for given variable
#   (a bit different from the standard, was intentional for its orig use)
if (!function_exists("var_export")) {
   function var_export($var, $return=false, $indent="", $output="") {

      #-- output as in-class variable definitions
      if (is_object($var)) {
         $output = "class " . get_class($var) . " {\n";
         foreach (((array)$var) as $id=>$var) {
            $output .= "  var \$$id = " . var_export($var, true) . ";\n";
         }
         $output .= "}";
      }
      
      #-- array constructor
      elseif (is_array($var)) {
         foreach ($var as $id=>$next) {
            if ($output) $output .= ",\n";
                    else $output = "array(\n";
            $output .= $indent . '  '
                    . (is_numeric($id) ? $id : '"'.addslashes($id).'"')
                    . ' => ' . var_export($next, true, "$indent  ");
         }
         if (empty($output)) $output = "array(";
         $output .= "\n{$indent})";
       #if ($indent == "") $output .= ";";
      }
      
      #-- literals
      elseif (is_numeric($var)) {
         $output = "$var";
      }
      elseif (is_bool($var)) {
         $output = $var ? "true" : "false";
      }
      else {
         $output = "'" . preg_replace("/([\\\\\'])/", '\\\\$1', $var) . "'";
      }

      #-- done
      if ($return) {
         return($output);
      }
      else {
         print($output);
      }
   }
}


#-- strcmp() variant that respects locale setting,
#   existed since PHP 4.0.5, but under Win32 first since 4.3.2
if (!function_exists("strcoll")) {
   function strcoll($str1, $str2) {
      return strcmp($str1, $str2);
   }
}





#------------------------------------------------------------------ 4.1 ---
# nl_langinfo - unimpl?
# getmygid
# version_compare
#
# See also "ext/math41.php" for some more (rarely used mathematical funcs).




#-- aliases (an earlier fallen attempt to unify PHP function names)
if (!function_exists("diskfreespace")) {
   function diskfreespace() {
      return disk_free_sapce();
   }
   function disktotalspace() {
      return disk_total_sapce();
   }
}


#-- variable count of arguments (in array list) printf variant
if (!function_exists("vprintf")) {
   function vprintf($format, $args=NULL) {
      call_user_func_array("fprintf", get_func_args());
   }
}


#-- same as above, but doesn't output directly and returns formatted string
if (!function_exists("vsprintf")) {
   function vsprintf($format, $args=NULL) {
      $args = array_merge(array($format), array_values((array)$args));
      return call_user_func_array("sprintf", $args);
   }
}


#-- can be used to simulate a register_globals=on environment
if (!function_exists("import_request_variables")) {
   function import_request_variables($types="GPC", $pfix="") {
      
      #-- associate abbreviations to global var names
      $alias = array(
         "G" => "_GET",
         "P" => "_POST",
         "C" => "_COOKIE",
         "S" => "_SERVER",   // non-standard
         "E" => "_ENV",      // non-standard
      );
      #-- alias long names (PHP < 4.0.6)
      if (!isset($_REQUEST)) {
         $_GET = & $HTTP_GET_VARS;
         $_POST = & $HTTP_POST_VARS;
         $_COOKIE = & $HTTP_COOKIE_VARS;
      }
      
      #-- copy
      for ($i=0; $i<strlen($types); $i++) {
         if ($FROM = $alias[strtoupper($c)]) {
            foreach ($$FROM as $key=>$val) {
               if (!isset($GLOBALS[$pfix.$key])) {
                  $GLOBALS[$pfix . $key] = $val;
               }
            }
         }
      }
      // done
   }
}


// a few mathematical functions follow
// (wether we should really emulate them is a different question)

#-- me has no idea what this function means
if (!function_exists("hypot")) {
   function hypot($num1, $num2) {
      return sqrt($num1*$num1 + $num2*$num2);  // as per PHP manual ;)
   }
}

#-- more accurate logarithm func, but we cannot simulate it
#   (too much work, too slow in PHP)
if (!function_exists("log1p")) {
   function log1p($x) {
      return(  log(1+$x)  );
   }
   #-- same story for:
   function expm1($x) {
      return(  exp($x)-1  );
   }
}

#-- as per PHP manual
if (!function_exists("sinh")) {
   function sinh($f) {
      return(  (exp($f) - exp(-$f)) / 2  );
   }
   function cosh($f) {
      return(  (exp($f) + exp(-$f)) / 2  );
   }
   function tanh($f) {
      return(  sinh($f) / cosh($f)  );   // ok, that one makes sense again :)
   }
}

#-- these look a bit more complicated
if (!function_exists("asinh")) {
   function asinh($x) {
      return(  log($x + sqrt($x*$x+1))  );
   }
   function acosh($x) {
      return(  log($x + sqrt($x*$x-1))  );
   }
   function atanh($x) {
      return(  log1p( 2*$x / (1-$x) ) / 2  );
   }
}


#-- HMAC from RFC2104, but see also PHP_Compat and Crypt_HMAC
if (!function_exists("mhash")) {

   #-- constants
   define("MHASH_CRC32", 0);
   define("MHASH_MD5", 1);       // RFC1321
   define("MHASH_SHA1", 2);      // RFC3174
   define("MHASH_TIGER", 7);
   define("MHASH_MD4", 16);      // RFC1320
   define("MHASH_SHA256", 17);
   define("MHASH_ADLER32", 18);
   
   #-- implementation
   function mhash($hashtype, $text, $key) {
   
      #-- hash function
      static $hash_funcs = array(
          MHASH_CRC32 => "crc32",   // needs dechex()ing here
          MHASH_MD5 => "md5",
          MHASH_SHA1 => "sha1",
      );
      if (!($func = $hash_funcs[$hashtype]) || !function_exists($func)) {
         return trigger_error("mhash: cannot use hash algorithm #$hashtype/$func", E_USER_ERROR);
      }
      if (!$key) {
         trigger_error("mhash: called without key", E_USER_WARNING);
      }
      
      #-- params
      $bsize = 64;   // fixed size

      #-- pad key
      if (strlen($key) > $bsize) {  // hash key, when it's too long
         $key = $func($key); 
         $key = pack("H*", $key);   // binarify
      }
      $key = str_pad($key, $bsize, "\0");  // fill up with NULs (1)
      
      #-- prepare inner and outer padding stream
      $ipad = str_pad("", $bsize, "6");   // %36
      $opad = str_pad("", $bsize, "\\");  // %5C
      
      #-- call hash func    // php can XOR strings for us
      $dgst = pack("H*",  $func(  ($key ^ $ipad)  .  $text  ));  // (2,3,4)
      $dgst = pack("H*",  $func(  ($key ^ $opad)  .  $dgst  ));  // (5,6,7)
      return($dgst);
   }
}



#-- other stuff
/*
  removed funcs??
      [18] => leak
*/



#-- pre-4.1 -- end
// no need to implement anything below that, because such old versions
// will be incompatbile anyhow (- none of the newer superglobals known),
// but see also "ext/old"


?>
