<?php

/*
   The so called "mpi" plugins (== markup plugin interface), can be
   invoked from inside WikiPages using following syntax:

      <?plugin PluginName option1=attribute ... ?>
      <?plugin-link ToThisPlugin ?>

   The plugins (mpi_*.php) are loaded on demand from the plugins/mpi/
   directory. This behaviour can however be disabled by defining the
   _MPI_DEMANDLOAD constant to 0 - but then you had to load the wanted
   mpi plugins together with include("plugins/mpi/mpi.php") yourself
   and beforehand (from config.php or so).
*/

define("EWIKI_MPI_DEMANDLOAD", 1);
define("EWIKI_MPI_AUTOLOAD_DIR", dirname(__FILE__));

define("EWIKI_MPI_FILE_PREFIX", "mpi_");    # better do not change
define("EWIKI_MPI_FILE_POSTFIX", ".php");
define("EWIKI_MPI_MARKUP_REGEX", "/&lt;\\??(plugin:|e?wiki:|mpi:|plugin(?:-link|-form|-doc|-input|)\s)\s*(.*?)\\??&gt;/i");



#-- register at ewiki pluginterface
//$ewiki_plugins["format_source"][] = "ewiki_mpi_fixsyntax";
$ewiki_plugins["action"]["mpi"] = "ewiki_mpi_action";
$ewiki_plugins["format_block"]["mpi"][] = "ewiki_mpi_call";
$ewiki_config["format_block"]["mpi"] = array("&lt;?plugin", "?&gt;", false, 0x0020);




#-- changes old plugin call syntax to new one (finally disabled with R1.02b)
function ewiki_mpi_fixsyntax(&$src) {
   $src = preg_replace('/&lt;plugin(.+?)\?&gt;/s', '&lt;?plugin\\1?&gt;', $src);
   global $ewiki_config;
   $uu = $ewiki_config["format_block"]["mpi"];   // give all other
   unset($ewiki_config["format_block"]["mpi"]);  // block plugins
   $ewiki_config["format_block"]["mpi"] = $uu;   // precedence
}



#-- called from inside ewiki_format() engine
function ewiki_mpi_call(&$str, &$in, &$iii, &$s) {

   global $ewiki_plugins;

   #-- split out $mpi-action
   $str = trim($str);
   if ($str[0] == "-") {
      $mpi_action = substr(strtolower(strtok($str, " :\n\t\f\r")), 1);
      $str = ltrim(strtok("\000"));
   }
   switch ($mpi_action) {
      case "doc":
      case "desc":
      case "link":
         break;
      default:
         $mpi_action = "html";
   }    

   #-- split mpi plugin name from arguments
   $mpi_name = trim(strtok($str, " \n\t\f\r"));
   $str = strtok("\000");

   #-- split args
   $mpi_args = array();
   $mpi_args["_"] = $str;
   ewiki_stripentities($mpi_args["_"]);
   if (preg_match_all('/(\w+)="(.+)(?<![\\\\])"|(\w+)=([^\s]+)|([^\s"=]+)/', $mpi_args["_"], $uu)) {
      $pos = 0;
      foreach ($uu[5] as $i=>$d) {
         if (strlen($d)) {
            $mpi_args[$pos] = $d;
            $pos++;
         }
         elseif ($uu[$f=1][$i] || $uu[$f=3][$i]) {
            $mpi_args[$uu[$f][$i]] = stripslashes($uu[$f+1][$i]);
         }
      }
      #-- std arg
      isset($mpi_args["id"])
      or ($mpi_args["id"] = $mpi_args[0])
      or ($mpi_args["id"] = $mpi_args["page"]);
   }

   #-- plugin-link
   if ($action == "link") {
      $str = '<a href="' . ewiki_script("mpi", $name, $args) . '">' . $name . '</a>';
      $iii[$in][1] = 0x0010|0x0020;  # InlineBlock+ScanForWikiWords
   }
   else {
      $str = ewiki_mpi_exec($mpi_action, $mpi_name, $mpi_args, $iii, $s);
   }
}
 


function ewiki_mpi_exec($action, $rname, &$args, &$iii, &$s) {

   global $ewiki_plugins, $ewiki_t, $ewiki_config;

   #-- select plugin function
   $name = strtolower($rname);
   $pf = $ewiki_plugins["mpi"][$name];

   #-- load plugin
   if (!function_exists($pf) && EWIKI_MPI_DEMANDLOAD) {

      $mpi_file = EWIKI_MPI_AUTOLOAD_DIR . "/" . EWIKI_MPI_FILE_PREFIX
                . $name . EWIKI_MPI_FILE_POSTFIX;
      @include_once($mpi_file);

      $pf = $ewiki_plugins["mpi"][$name];
   }

   #-- execute plugin
   if (function_exists($pf)) {
      return($pf($action, $args, $iii, $s));
   }
   else {
      return("<!-- referenced mpi '$rname' not available -->");
   }
}




function ewiki_mpi_action($id, $data, $action) {
   global $ewiki_plugins;
   return(ewiki_mpi_exec("html", $id, $_REQUEST, $uu, $uu));
}



?>