<?php
/*
   Load [http://localhost/ewiki/tools/t_setupwiz.php] with your browser 
   to generate an "ewiki.ini" file by using a simple configuration
   wizard, which queries you about all the features (plugins) and
   settings you wish to use.

   Save the generated .ini file to disk and then load the "ini.php"
   script instead of "config.php" or "ewiki.php".
   
   - not yet 100% compatible with the new *.meta data files
   - beware of spaghetti code
*/


#-- .meta info
include("../plugins/lib/pluginmetadata.php");
$_cached = !empty($_POST);
ewiki_pmd($_cached);

#-- defaults for the separately handled database settings in $db[]
if (!($db = $_REQUEST["db"])) {
   $db = array(
     "type" => NULL,
     "server" => "localhost",
     "dbname" => "test",
     "table" => "ewiki",
     "dir" => "/tmp",
     "dba" => "/tmp/wiki.dbm",
   );
}

#-- read in ewiki.ini, if one was uploaded (POST <input file>)
if ($li = $_FILES["load_ini"]["tmp_name"]) {
   $ini = array();
   $uu = preg_split('/^\[(\w+)\]/m', implode("",file($li)), -1, PREG_SPLIT_DELIM_CAPTURE);
   for ($i=1; $i<=count($uu); $i+=2) {
      $sect = $uu[$i];
      preg_match_all('/^\s*(\w[^\s]+)\s*=[\t ]*(.+?)(\s;.+)?\s*$/m', $uu[$i+1], $rows);
      foreach ($rows[1] as $r=>$name) {
         $ini[$sect][$name][] = trim($rows[2][$r]);
      }
   }

   #-- pre-set the separate $db[] hash
   if ($ini["db"]) {
      foreach ($ini["db"] as $i=>$val) {
         $db[$i] = $val[0];
   }  }
}

#-- data
$default_priority_level = array(
   "core"=>"1", "required"=>"1",
   "default"=>1, "standard"=>1,
   "recommended"=>1, "important"=>1,
);


#-- heavily mixed list of features and options
#
# - an array of arrays
# - each entry gives a 'feature' or simply a text fragment
# - first level subarrays have following entries
#   [0] type setting, "!"=headline, "="=always_enabled_feature,
#       0=disabled, 1=enabled, "..."=text_fragment_only
#   [1] title
#   [2] text / description
#   [3] list of plugin file names (without .php)
#   [4] another subarray of option settings
# - an option setting subarray has following structure:
#   [0] <input> field type
#   [1] title
#   [2] EWIKI_ constant or $ewiki_ var name
#   [3] default setting (value)
#   [4] text / description
#   [5] options for <select> input, separated by "|" with "values=titles"
# - html text fragments can be inserted anywhere in titles or text and
#   description entries (used for "database selection" part)
#
#
$list = array();
foreach (ewiki_pmd_by_category() as $category=>$pmd) {
   $list[] = array(
      "!",
      "$category",
      "",
   );
   foreach ($pmd as $id=>$row) {
      if (!ewiki_pmd_hidden($row)) {
         $options = array();
         foreach (explode("\n", $row["config"]) as $l) {
            if ($l = trim($l)) {
               $name = trim(strtok($l, "="));
               $val = strtok("\n");
               $comm = "";
               if ($p = strpos($val, " //")) {
                  $comm = substr($val, $p+3);
                  $val = trim(trim(substr($val, 0, $p)), '"');
               }
               $options[] = array(
                  preg_match("/^([01](\|[01])?)$/", $val) ? "checkbox" : (strpos($val, "|") ? "select" : "text"),
                  $name,
                  $name,
                  strtok($val, "|="),
                  $comm,
                  $val,
               );
            }
         }
         $list[] = array(
            ($uu = $default_priority_level[$row["priority"]]) ? $uu : 0,
            htmlentities($row["title"]),
            htmlentities($row["description"]),
            $id,
            $options,
         );
      }
   }
}


array(
      "...", "",
      <<<EOT
  <br>
  <input type="radio" id="db-0" name="db[type]" value="none"><label for="db-0"> don't care</label>
   <span class="feature-desc">If you don't need to open the database connection in the config.php - if a MySQL connection was already established somewhere else</span>
   <br>
  <br>
  <input type="radio" id="db-1" name="db[type]" value="mysql"><label for="db-1"> built-in MySQL</label>   <br>
  <input type="radio" id="db-2" name="db[type]" value="pgsql"><label for="db-2"> or PostgreSQL</label> <span class="feature-desc">(with anydb_ wrapper)</span>
   <div class="option">
      <label for="db-1-1">server </label><input type="text" id="db-1-1" name="db[server]" value="$db[server]"><br>
      <label for="db-1-2">user name </label><input type="text" id="db-1-2" name="db[user]" value="$db[user]"><br>
      <label for="db-1-3">password </label><input type="password" id="db-1-3" name="db[pw]" value="$db[pw]"><br>
      <label for="db-1-4">database name </label><input type="text" id="db-1-4" name="db[dbname]" value="$db[dbname]"><br>
      <label for="db-1-5">table name </label><input type="text" id="db-1-5" name="db[table]" value="$db[table]"> will be created automatically, when you activate ewiki for the first time<br>
   </div>
  <br>
  <input type="radio" id="db-3" name="db[type]" value="flat"><label for="db-3"> flat file</label> <span class="feature-desc">database backend</span>   <br>
  <input type="radio" id="db-4" name="db[type]" value="fast"><label for="db-4"> fast file</label> <span class="feature-desc">(compressed)</span>   <br>
  <input type="radio" id="db-5" name="db[type]" value="dzf2"><label for="db-5"> new flat file backend 'dzf2'</label> <span class="feature-desc">(provides case-insensitive storage, plattform compatible, but more complicated structure)</span>
   <div class="option">
      <label for="db-3-1">storage directory </label><input type="text" id="db-3-1" name="db[dir]" value="$db[dir]">
      <span class="option-desc">Note: the directory "/tmp" exists on most
Unix/Linux webservers, but will be purged on reboot; so you normally want to
use a different location for your pages. Choose a <i>relative path name</i>
(like for example "<kbd>./files</kbd>") and create that directory ("<kbd>mkdir
<i>files</i></kbd>" in FTP/shell) and give it <i>world-write permissions</i>
("<kbd>chmod 777 <i>files</i></kbd>" in FTP/shell).</span>
   </div>
  <br>
  <input type="radio" id="db-6" name="db[type]" value="dba"><label for="db-6"> .dbm</label> <span class="feature-desc"> Berkely database file</span>  <br>
   <div class="option">
      <label for="db-6-1">database file </label><input type="text" id="db-6-1" name="db[dba]" value="$db[dba]">
      <span class="option-desc">The file name extension must be one of:
      .dbm, .db2, .db3, .db4, .ndbm, .gdbm or .flatfile, and the file must
      be world-writable of course</span>
   </div>
EOT
);






#---------------------------------------------------------------------------



/*

#-- read in plugins/ dir
function find_plugin_files($realdir, $dirname) {
   $r = array();
   $dh = opendir($realdir);
   while ($dh && ($fn = readdir($dh))) {
      if ($fn[0] == ".") {
        continue;
      }
      elseif (is_dir("$realdir/$fn")) {
         $r = array_merge($r, find_plugin_files("$realdir/$fn", "$dirname/$fn"));
      }
      elseif ($len = strrpos($fn, ".")) {
         $fn = substr($fn, 0, $len);
         if (isset($r[$fn]) && $_SERVER["REQUEST_METHOD"]=="GET") {
            echo "WARNING: two same named plugins '$fn' in two different locations!<br>\n";
         }
         $r[$fn] = "$dirname/$fn";
      }
   }
   closedir($dh);
   return($r);
}

$uu = dirname(__FILE__)."/../plugins";
$plugin_files = find_plugin_files($uu, "plugins");

#-- adjust incomplete filenames in feature $list
foreach ($list as $i1=>$d) {
   if (is_array($d[3])) foreach($d[3] as $i2=>$fn) {
      if (!strpos($fn, "/")) {
         if ($fullfn = $plugin_files[$fn]) {
            $list[$i1][3][$i2] = $fullfn;
         }
         else {
            echo "WARNING: could not determine real file name for plugin '$fn'<br>\n";
         }
      }
   }
}


*/

#---------------------------------------------------------------------------




#-- inject values (into $list[]) imported from earlier loaded ewiki.ini
if ($ini) {
   foreach ($list as $fid=>$row) {

      #-- enable feature, if all requ/mentioned plugins were loaded in .ini
      if (($row[0]===0) || ($row[0]===1)) { 
         $is = all_in_array($row[3], $ini["plugins"]["load"]);
         $list[$fid][0] = ($is ? 1 : 0);
      }

      #-- set feature options
      if ($row[4]) {
         foreach ($row[4] as $oid=>$opts) {
            $name = $opts[2];
            $val = $ini["settings"][$name][0];
            if (strlen($val)) {
               $list[$fid][4][$oid][3] = $val;
      }  }  }
}  }


#-- compare two arrays, all elements of first must be in second
function all_in_array($a1, $a2) {
   $a3 = array_intersect($a1, $a2);
   return(count($a1) == count($a3));
}


#---------------------------------------------------------------------------



#-- prepare generation of config.php or ewiki.ini
#   (builds plugin and constant/var lists from _REQUEST settings)
#
if ($_REQUEST["feature"]) {

  $set = &$_REQUEST["feature"];
  $opt = &$_REQUEST["option"];

  $c_plugins = array();
  $c_settings = array(0=>array(), 1=>array());

  #-- go through hardcoded feature $list
  foreach ($list as $fid=>$row) {

     #-- compare if feature array enabled in _REQUEST
     $enabled = ($row[0] === "=") || ($set[$fid]);
     if ($enabled) {
#echo "ENABLED=$set[$fid] feature[$fid], r0=$row[0], r1=$row[1],\n";

        #-- list of plugins (always triggered)
        $c_plugins[] = $row[3];

        #-- settings, individual $_REQUEST entries
        if ($options = $row[4]) {
           foreach ($options as $oid=>$row) {
              $i = $row[2];
              $v = $opt[$fid][$oid];
              if (strlen($v)) {
                 $var = ($i[0] == "$") ? 1 : 0;
                 $c_settings[$var][$i] = preg_match('/^\d+$/', $v) ? "$v" : "'$v'";
              }
           }
        }

     }
  }#--if($enabled)
  
  #-- cleanup plugin list (in case one was injected twice)
  $c_plugins = array_unique($c_plugins);

}#--if(<submit>)


#---------------------------------------------------------------------------


function config_php_db() {

  global $db;

  echo "#-- database connection/plugins\n";
  switch ($db["type"]) {
     case "mysql":
        echo "// MySQL support is built-in, we only open the connection\n";
        echo "define(\"EWIKI_DB_TABLE_NAME\", \"$db[table]\");\n";
        echo "mysql_connect('$db[server]', '$db[user]', '$db[pw]');\n";
        echo "mysql_query('USE $db[dbname]');\n\n";
        break;

     case "pgsql":
        echo "define(\"EWIKI_DB_TABLE_NAME\", \"$db[table]\");\n";
        echo "define(\"EWIKI_DB_UTF8\", 0);  //depends on your Postgres db\n";
        echo "include_once(\"plugins/db/any.php\");\n";
        echo "\$db = anydb_connect('', '$db[user]', '$db[pw]', '$db[dbname]', 'pgsql');\n\n";
        break;

     case "fast":
        echo "define(\"EWIKI_DB_FAST_FILES\", 1);\n";
     case "flat":
        echo "define(\"EWIKI_DBFILES_DIRECTORY\", \"$db[dir]\");\n";
        echo "include_once(\"plugins/db/flat_files.php\");\n";
        echo "// the given directory must exist and be world-writable (chmod 777)\n\n";
        break;        

     case "dzf2":
        echo "define(\"EWIKI_DBFILES_DIRECTORY\", \"$db[dir]\");\n";
        echo "define(\"EWIKI_DBFF_ACCURATE\", 1);\n";
        echo "define(\"DZF2_HIT_COUNTING\", 1);\n";
        echo "include_once(\"plugins/db/dzf2.php\");\n";
        echo "// the given directory must exist and be world-writable (chmod 777)\n\n";
        break;

     case "dba":
        echo "define(\"EWIKI_DBA\", \"$db[dba]\");\n";
        echo "include_once(\"plugins/db/dba.php\");\n";
        break;

     default:
        echo "// you must open a connection (MySQL) outside of the config.php,\n";
        echo "// it has not been configured with the setup wizard\n\n";
        break;
  }
}


function config_php_settings() {

  global $c_settings;
  
  echo "#-- constants\n";
  foreach ($c_settings[0] as $id=>$val) {
     echo "define(\"$id\", $val);\n";
  }

  echo "\n#-- set a few configuration variables\n";
  foreach ($c_settings[1] as $id=>$val) {
     echo "$id = $val;\n";
  }
}


#---------------------------------------------------------------------------



#-- write out "config.php" file
#
if ($_REQUEST["config_php"]) {
  header("Content-Type: application/x-httpd-php");
  header("Content-Disposition: attachment; filename=\"config.php\"");

  #-- write out config.php
  echo <<<EOT
<?php
# automatically generated config.php
# (see the ewiki configuration wizard)
#\n\n
EOT;

  config_php_db();
  config_php_settings();

  echo "\n#-- load plugins\n";
  foreach ($c_plugins as $id) if ($id != "core") {
     
     if ($fn = $ewiki_pmd[$id]["fn"]) {
        echo "include_once(\"$fn\");\n";
     }
     else {
        echo "//plugin not found: $id\n";
     }
  }

  echo "\n#-- load ewiki 'lib'\ninclude_once(\"ewiki.php\");\n\n";
  echo "?" . ">";
  die();
}


#---------------------------------------------------------------------------



#-- write out as "ewiki.ini" file
#
if ($_REQUEST["ewiki_ini"]) {
  header("Content-Type: text/x-ini-file");
  header("Content-Disposition: attachment; filename=\"ewiki.ini\"");
  out_ewiki_ini();
  die();
}

#-- write out config.php
function out_ewiki_ini() {
   global $db, $c_settings, $c_plugins, $ewiki_pmd;
   echo "; automatically generated configuration summary\n; see ewiki config wizard\n";
   echo "\n[db]\n";
   echo ";init = \n";
   foreach ($db as $id=>$val) {
      echo "$id = $val\n";
   }
   echo "\n[settings]\n";
   $c_settings = array_merge($c_settings[0], $c_settings[1]);
   foreach ($c_settings as $id=>$val) {
      $val = trim($val, "'");
      echo "$id = $val\n";
   }
   echo "\n[plugins]\n";
   foreach ($c_plugins as $id) {
      if ($fn = $ewiki_pmd[$id]["fn"]) {
         echo "load = $fn\n";
      }
      else {
         echo ";not found = $id\n";
      }
   }
   echo "\n\n";
}



#---------------------------------------------------------------------------



#-- generate a "monsterwiki.php" script (all-in-one)
#
if ($_REQUEST["monsterwiki_php"]) {
  header("Content-Type: application/x-httpd-php");
  header("Content-Disposition: attachment; filename=\"monsterwiki.php\"");
  
  #-- begin output
  echo <<<EOT
<?php
#
# (this script was assembled with the ewiki configuration wizard)
# include() this instead of the bare 'ewiki.php' script as follows:
# <!php
#   include("monsterwiki.php");
#   ...
#   echo ewiki_page();
# !>
#\n\n
EOT;

  config_php_db();
  config_php_settings();
  
  echo "\n#-- end of settings,\n# plugins follow\n\n/*-- config summary in .ini format:\n";
  out_ewiki_ini();  
  echo "--*/\n\n" . "?".">";

  #-- write plugins  
  $PREFIX = "../";
  foreach ($c_plugins as $fn) {
     if (($fn != "core") && ($fn != "ewiki")) {
        if ($fn = $ewiki_pmd[$fn]["fn"]) {
           readfile($PREFIX . $fn);
        }
     }
  }
  #-- write core
  readfile($PREFIX."ewiki.php");  
  
  die();
}



#---------------------------------------------------------------------------
# <html> page output otherwise

?>
<html>
<head>
 <title>ewiki configuration wizard</title>
<style type="text/css"><!--
html {
  show-tags: as-you-like-dear-browser;
}
body {
  margin: 0px; padding: 0px;
  font: Verdana,sans-serif 16px;
  color: #dddddd;
}
.left-bar {
  margin: 0px;
  float: left;
  width: 140px;
  height: 6000px;
  padding: 0px 0px 0px 20px;
}
.left-bar .stripe {
  width: 80px;
  height: 20%;
}
.real-body {
  padding-left: 40px;
  width: 520px;
}
h1,h2,h3,h4,h5 {
  background-color: #4c4c4e;
  color: #ffffff;
  margin-bottom: 3pt;
}
h2 {
  background-color: #464646;
  font-size: 20px;  
}
h1 {
  background-color: #404040;
  font-size: 24px;
}
input,checkbox,textarea,select {
  background-color: #666666;
  border: 1px solid #444444;
  color: #dddddd;
}
input:focus {
  border-color: #663333;
}
.feature-desc, .option-desc {
  color: #aaaaaa;
  font-size: 80%;
}
tt {
  font-size: 120%;
}
.option {
  color: #bbbbbb;
}
.option-desc {
  font-size: 75%;
}
a {
  color: #dddddd;
  text-decoration: none;
  border-bottom: dashed 1px #773333;
}
//--></style>
</head>
<body bgcolor="#555555"><div class="left-bar">
  <div class="stripe" style="background:#662222">&nbsp;</div>
  <div class="stripe" style="background:#642424">&nbsp;</div>
  <div class="stripe" style="background:#622626">&nbsp;</div>
  <div class="stripe" style="background:#602828">&nbsp;</div>
  <div class="stripe" style="background:#5E2A2A">&nbsp;</div>
</div>
<br>
<div class="real-body">
  <h1>ewiki configuration wizard</h1>
  Generates an initial <tt>ewiki.ini</tt> or <tt>config.php</tt> for you.
  The plugin list is unfiltered and therefore rather long. Skip all the
  settings and options that don't look interesting or senseful at first.
  <br><br>
  
  <b>Note</b>: This wizard has not yet been fully updated to handle the
  new plugin *.meta files. Please use the 'tools/setup' console program
  if you can.
  <br><br>

  You can reuse an earlier <tt>ewiki.ini</tt>, if you kept a copy of your
  previously choosen settings:
  <br>
  <form action="t_setupwiz.php" method="POST" enctype="multipart/form-data" >
  <input size="32" type="file" name="load_ini"> <input type="submit" value="load it">
  <br><br>
  
<?php

  #-- go through list
  foreach ($list as $fid=>$row) {

     #-- print main feature field
     switch ($row[0]) {

        case "0":
        case "1":
           echo "  <input type=\"checkbox\" id=\"feature-$fid\" name=\"feature[$fid]\" value=\"1\" ".($row[0]?"checked":"").">\n";
           echo "  <label for=\"feature-$fid\">$row[1]</label>\n";
           if ($row[2]) {
              echo "  <span class=\"feature-desc\">$row[2]</span>";
           }
           echo "<br>\n";
           break;

        case "=":
           echo "  <input type=\"hidden\" name=\"feature[$fid]\" value=\"1\">\n";
           echo "  $row[1]\n";
           if ($row[2]) {
              echo "  <span class=\"feature-desc\">$row[2]</span>";
           }
           echo "<br>\n";
           break;

        case "!":
           echo "  <h3>$row[1]</h3>\n";
        default:
           echo "  $row[2]\n";
           break;
     }

     #-- show up associated options
     if ($options = $row[4])
     foreach ($options as $oid=>$row) {
        $id = "option-$fid-$oid";
        echo '    <div class="option">';
        echo " &nbsp; &nbsp; <label for=\"$id\">$row[1]</label> ";
        switch($row[0]) {
           case "checkbox":
              $checked = ($row[3] ? " checked" : "");
              echo "<input type=\"checkbox\" name=\"option[$fid][$oid]\" id=\"$id\" value=\"1\"$checked>";
              break;
           case "select":
              echo "<select name=\"option[$fid][$oid]\" id=\"$id\">";
              foreach (explode("|", $row[5]) as $val) {
                 $title = $val;
                 if (strpos($val, "=")) {
                    list($val, $title) = explode("=", $val, 2);
                 }
                 $checked = (($row[3]==$value) ? " selected" : "");
                 echo "<option value=\"$val\"$selected>$title</option>";
              }
              echo "</select>";
              break;
           default:
              echo "<input type=\"$row[0]\" name=\"option[$fid][$oid]\" id=\"$id\" value=\"$row[3]\">";
        }
        echo "<span class=\"option-desc\"> $row[4]</span></div>\n";
     }
  }

?>
  <br>
  <h2>fin</h2>
  Now, that you've finished clicking around, you can save your configuration
  settings. A save dialog will open, and you should store the files directly
  into your ewiki/ directory.
  <br><br>

  <input style="color:#ffffff" type="submit" name="config_php" value="save config.php"> is
  what you should do now; you can use it as replacement for the example
  file distributed with ewiki.
  <br><br>

  <input type="submit" name="ewiki_ini" value="save ewiki.ini"> is
  useful to later come back and reuse the settings you've made here.
  <br><br>
  
  <input type="submit" name="monsterwiki_php" value="create monsterwiki script"> with
  above options and extensions merged in; use this script instead of ewiki.php
  then (it is believed to run a bit faster)
  <br><br>

  </form>
</div>
</body>
</html>
