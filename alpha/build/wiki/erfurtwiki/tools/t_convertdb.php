<?php
/*
   database conversion util
   ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
   If you need to special settings to the ewiki database you're converting
   to, then please insert all required define(); statements above the first
   include line, so you can override the settings from your main config.php
   script.
*/


include("t_config.php");
?>
<html>
<head>
 <title>database conversion (wiki engine transition)</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>database conversion</h1>
<?php

 if (empty($_REQUEST["convert"])
 and empty($_REQUEST["readtest"])
 and empty($_REQUEST["writetest"])) {

?>
  This tool can be used to convert an existing (foreign) wiki database with
  all its pages to the ewiki database and page format. It is very experimental
  and you should consider using the export utility of your previous Wiki with
  <tt>ewikictl</tt> afterwards to reimport it here.

  <br>
 <small>
  <br>
  Some of the databases you can export from need more parameters, and if you
  transfer data from one SQL-based database scheme to another, you need to
  have source and target tables in the same database (cannot cross-convert
  from/to different servers/dbms). And the target tables for foreign schemes
  must already exist.

  <br>
  <br>
  You must have your target ewiki database and plugin already defined
  in your <tt>config.php</tt>. If you want a special target, then you must
  edit <i>this</i> script (<tt title="<?php echo __FILE__; ?>">t_convertdb.php</tt>)
  and uncomment the other targets and insert correct define() or assignment
  statements to make it work yourself.
 </small>

  <br>
  <br>
  <form action="t_convertdb.php" method="POST">
    <table border="0" width="100%"> 
    <tr>
    <td width="50%" valign="top" class="add-border-right">

      <h4>convert from</h4>
      <label for="from_type">database type</label>
      <select name="from_type" id="from_type">
       <option value="pmwiki10" selected>PmWiki 0.6.x/1.0.x</option>
       <option value="phpwiki13">PhpWiki 1.3.x</option>
       <option value="ewiki">configured $ewiki_db</option>
       <option value="mysql">ErfurtWiki/MySQL</option>
       <option value="flat">ErfurtWiki/FlatFiles</option>
       <option value="fast">ErfurtWiki/FastFiles</option>
       <option value="dzf2">ErfurtWiki/dzf2</option>
       <option value="dba">ErfurtWiki/dba+dbm</option>
       <option value="zip">ErfurtWiki/zip</option>
       <option value="anydb">ErfurtWiki/anydb+sql</option>
       <option value="xmlrpc">XML-RPC remote database</option>
      </select>
      <br>

      <br>
      <input type="checkbox" name="from_convert_markup" value="1" id="from_cnv">
      <label for="from_cnv">convert into ewiki markup</label>

      <br>
      <br>
      <small>miscellaneous database access options</small>
      <br>
      <label for="from_o1">file / directory</label>
      <input type="text" name="from_dir" id="from_o1" size="16" title="for dzf2, zip, PmWiki">
      <br>
      <label for="from_o2">subwiki identifier</label>
      <input type="text" name="from_subwiki" value="Main" size="7" id="from_o2" title="for PmWiki, you can only import one of the various db fragments at a time">
      <br>
      <label for="from_o3">sql table name</label>
      <input type="text" name="from_sqltable" id="from_o3" size="10" title="for most SQL databases">

      <br>
      <br>
      <input type="submit" name="readtest" value="test read access">
      
      <br><br>
      <br>
     <small>
      <u>notes</u>
      <br>
      - If you access the '<b>configured $ewiki_db</b>' then you do not need to
        set any option, because this refers to whatever you configured in your
        'config.php' script as default database. Always the best option.
      <br>   
      - For <b>PmWiki</b> you must set the 'subwiki identifier' (to "Main" at best), so
        the conversion utility can extract one of the database fragments. You
        also need to define the 'file/directory' setting to the exact position
        of PmWikis 'wiki.d' or 'wikilib.d' data dirs (relative to the ewiki
        installations base directory; and separate multiple dir names using
        spaces, commas or colons).
      <br>
     </small>
      

    </td>
    <td width="50%" valign="top">

      <h4>into</h4>
      <label for="to_type">destination</label>
      <select name="to_type" id="to_type">
       <option value="configured" selected>configured $ewiki_db</option>

<!-- can't use the following without extra config:
       <option value="mysql">ErfurtWiki/MySQL</option>
       <option value="flat">ErfurtWiki/FlatFiles</option>
       <option value="fast">ErfurtWiki/FastFiles</option>
       <option value="dzf2">ErfurtWiki/dzf2</option>
       <option value="dba">ErfurtWiki/dba+dbm</option>
       <option value="zip">ErfurtWiki/zip</option>
       <option value="anydb">ErfurtWiki/anydb+sql</option>
-->       
       <option value="phpwiki13">PhpWiki 1.3.x</option>
<!-- 
       <option value="mysql">old PhpWiki 1.2</option>
-->
      </select>
      <br>

      <br>
      <input type="checkbox" name="to_convert_markup" value="1" id="to_cnv">
      <label for="to_cnv">use wiki markup export filters</label>

      <br>
      <input type="checkbox" name="all_versions" value="1" checked="checked" id="all_vers">
      <label for="all_vers">transfer all page versions/revisions</label>
      <br>
      <small>and</small>
      <input type="checkbox" name="overwrite" value="1" id="to_over">
      <label for="to_over">overwrite page versions that already exist in target database</label>
      <small>(or copied page will simply be appended as newest)</small>

      <br>
      <br>
      <input type="submit" name="convert" value="start transfer">

    </td>
    </tr>
    </table>
  </form>
 
<?php

 }

 #------------------------------------------------------------------ go ---
 else {
 
    #-- localize expected vars
    $from_convert = $_REQUEST["from_convert_markup"];
    $from_type = $_REQUEST["from_type"];
    $to_type = $_REQUEST["to_type"];
    $to_convert = $_REQUEST["to_convert_markup"];
    $all_versions = $_REQUEST["all_versions"];
    $overwrite = $_REQUEST["overwrite"];
    $read = $_REQUEST["readtest"];
    $write = $_REQUEST["convert"];
    
    
    #-- database types and plugins
    $t_scr = array(
       "ewiki" => "",
       "mysql" => "",
       "pmwiki10" => "read_pmwiki1.php",
       "phpwiki13" => "phpwiki13.php",
       "dba" => "dba.php",
       "flat" => "flat_files.php",
       "fast" => "fast_files.php",
       "dzf2" => "dzf2.php",
       "zip" => "zip.php",
       "anydb" => "anydb.php",
       "xmlrpc" => "xmlrpc.php",
    );
    $t_api = array(
       "ewiki" => "$",
       "mysql" => "ewiki_database_mysql",
       "pmwiki10" => "ewiki_database_pmwiki",
       "phpwiki13" => "ewiki_database_phpwiki13",
       "dba" => "ewiki_database_dba",
       "flat" => "ewiki_database_files",
       "fast" => "ewiki_database_files",
       "dzf2" => "ewiki_database_DirZlibFiles2",
       "zip" => "ewiki_database_zip",
       "anydb" => "ewiki_database_anydb",
       "xmlrpc" => "ewiki_database_xmlrpc",
    );
    $t_init = array(   // auto-create database scheme (::INIT)
       "ewiki" => 1,
       "mysql" => 1,
       "dba" => 1,
       "flat" => 1,
       "fast" => 1,
       "dzf2" => 1,
       "anydb" => 1,
    );
    $t_in_convert = array(
       "phpwiki13" => array("phpwiki.php"),
       "usemod" => array("usemod.php"),
    );
    $t_out_convert = array(
       "phpwiki13" => array(),  // nothing ready yet
    );
    
    #-- preparations
    define("PHPWIKI13_WRITEACCESS", 1);


    #-- open databases
    if ($script = $t_scr[$from_type]) include_once("plugins/db/$script");
    $FROM = instantiate_db_api($t_api[$from_type]);
    echo "- source database module loaded/instantiated<br>\n";
    if ($read) {
       read_test();
    }
    if ($script = $t_scr[$from_to]) include_once("plugins/db/$script");
    $DEST = instantiate_db_api($t_api[$to_type]);
    echo "- target database module loaded/instantiated<br>\n";
    
    
    #-- set up options for $FROM database
    if ($grp = $_REQUEST["from_subwiki"]) {
       $FROM->group = $grp;
    }
    if ($dir = $_REQUEST["from_dir"]) {
       $FROM->zip = $dir;
       $FROM->dir = $dir;
       $FROM->dirs = preg_split("/[\s:;,]+/", $dir);
    }
    if ($tbl = $_REQUEST["from_sqltable"]) {
       $FROM->table = $tbl;
    }
    
    
    
    #-- load markup conversion scripts
    $ewiki_plugins["format_source"] = array();   // kill default ones
    if ($from_convert)
    if ($scr_a = $t_in_convert[$from_type]) {
       foreach ($scr_a as $script) {
          echo "- loading markup conversion module '$script'<br>\n";
          include_once("../plugins/markup/$script");
       }
    }
    $ewiki_plugins["markup_convert"] = array();
    if ($to_convert)
    if ($scr_a = $t_out_convert[$from_type]) {
       foreach ($scr_a as $script) {
          echo "- loading markup export plugin '$script'<br>\n";
          include_once("plugins/markup/$script");
       }
    }
    

    #-- creating ewiki database
    if ($t_init[$to_type]) {
       echo "- <u>creating</u> destination target database scheme/whatever...<br>\n";
       $DEST->INIT();
    }
    else {
       echo "- cannot create database scheme for that target type [SKIPPED]<br>\n";
    }
    echo "<br>\n";


    #-- start --------------------------------------------------------
    echo "\n\n<br>\n<h4>copying pages:</h4>\n\n";

    $result = $FROM->GETALL(array("flags", "version"));   // not "id"!, we're doing raw access here, and need to obey the internal rules
    while ($row = $result->get()) {
    
       #-- run forever, if necessary
       set_time_limit(2000);
       
       #-- page id
       $id = $row["id"];
       echo htmlentities("$id");


       #-- read
       $data = $FROM->GET($id);


       #-- operation mode
       if ($all_versions) {
       
          #-- copy all page versions
          for ($ver=$data["version"]; $version>=1; $version--) {
             if ($data = $FROM->GET($id, $version)) {
                markup_convert($data);
                if ($DEST->WRITE($data, $overwrite)) {
                   echo ".$version";
                }
                else {
                   echo "<b>.E</b>";
                }
             }
             
          }
       
       }
       else {
       
          #-- check for existence of current page
          $old = $DEST->GET($id);
          if (!$old["version"]) {
             $data["version"] = 1;
          }
          elseif ($overwrite && $old) {
             $data["version"] = $old["version"] + 1;
          }
          else {
             continue;
          }
          
          markup_convert($data);
          if ($DEST->WRITE($data)) {
             echo "[" . $data["version"] . "]";
          }
          else {
             echo "<b>[ERROR]</b>";
          }
       
       }


       echo ",\n";
    }

    #-- fin
    echo "<br><br>done\n";

 }






#----------------------------------------------------------- utility code ---




#-- rewriting of page content to match target wiki engines markup
function markup_convert() {
   global $ewiki_plugins;

   #-- in-conversion
   if ($pf_a = $ewiki_plugins["format_source"]) foreach ($pf_a as $pf) {
      $pf($data["content"]);
   }

   #-- out-conversion
   if ($pf_a = $ewiki_plugins["markup_convert"]) foreach ($pf_a as $pf) {
      $pf($data["content"]);
   }
   
   #-- update {refs}  (helpful for PmWiki, which does not have an equivalent)
   ewiki_scan_wikiwords($data["content"], $ewiki_links, "_STRIP_EMAIL=1");
   $data["refs"] = "\n\n".implode("\n", array_keys($ewiki_links))."\n\n";

   //fin
} 
 

#-- submodule
function read_test() {
   global $FROM;
   echo "- now performing only an<br>\n<br>\n<h4>read test</h4>\n";
   $all = $FROM->GETALL(array("version","flags"));
   $list = array();
   echo "- following pages were found in that database:<br>\n";
   while ($row = $all->get()) {
      echo $row["id"] . "[" . $row["version"] . "], ";
      $list[] = $row["id"];
   }
   echo "\n<br><br>\n<h4>randomly chosen entry</h4>\n";
   $id = $list[rand(0,count($list)-1)];
   $row = $FROM->GET($id);
   echo "<pre>";
   print_r($row);
   echo "</pre>\n\n<br>\n</body></html>";
   die();
}



#-- create database object (or simply return reference to default $ewiki_db)
function & instantiate_db_api($class) {
   global $ewiki_db;
   if ((!$class) || ($class=="$")) {
      if (!isset($ewiki_db)) {
         $ewiki_db = & new ewiki_database_mysql();
      }
      $db = & $ewiki_db;
   }
   else {
      $db = & new $class;
   }
   return($db);
}


?>
</body></html>