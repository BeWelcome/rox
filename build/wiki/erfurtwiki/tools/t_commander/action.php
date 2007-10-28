<?php
  include("t_config.php");
?>
<html>
<head>
 <title>WikiCommander:CommandExec</title>
 <link rel="stylesheet" type="text/css" href="80x25.css">
</head>
<body bgcolor="#202527" text="#eeeeee" class="CommandExec Shell Exec" style="padding:3px;">
<?php

   #-- page
   $id = $_REQUEST["id"];

   #-- delete all page versions
   if ($_REQUEST["delete"]) {
      $ls = $_REQUEST["ls"];
      if ($id) {
         $ls = array($id);
      }
      cmd_rm($ls);
   }

   #-- move page to new name
   elseif ($_REQUEST["rename"]) {
      cmd_mv(array($_REQUEST["id"], $_REQUEST["new_id"]));
   }

   #-- duplicate page
   elseif ($_REQUEST["copy"]) {
      cmd_cp(array($_REQUEST["id"], $_REQUEST["new_id"]));
   }

   #-- nothing
   elseif ($_REQUEST["nop"]) {
   }

   #-- commandline actions
   elseif ($_REQUEST["cmd"]) {

      $args = split_input($_REQUEST["input"]);
      $action = array_shift($args);
      
      $funcs = array(
         "ls" => "cmd_ls",
         "ll" => "cmd_ll",
         "rm" => "cmd_rm",
         "mv" => "cmd_mv",
         "ren" => "cmd_ren",
         "cp" => "cmd_cp",
         "cat" => "cmd_cat",
         "touch" => "cmd_touch",
         "?" => "cmd_help",
         "help" => "cmd_help",
         "del" => "cmd_rm",
         "move" => "cmd_ren",
         "copy" => "cmd_cp",
         "type" => "cmd_cat",
      );
      $help = array(
         "cmd_mv" => "syntax: mv <i>FromPage ToPage</i>\n<br><br>\nrenames a page, and overwrites target page versions by that",
         "cmd_ren" => "ren: works similar to 'mv' but refuses to overwrite existing page versions <br>\n('move' is an alias to 'ren')",
         "cmd_ls" => "ls: lists existing pages, accepts glob parameters",
         "cmd_ll" => "ll: long/verbose page listing (see also 'ls --help')",
         "cmd_cp" => "cp: duplicates a page completely to another name, and overwrites any existing target page versions",
         "cmd_rm" => "rm: completely deletes pages so that even no backup version will remain in the database (irreversible)",
         "cmd_touch" => "touch: updates {lastmodified} field of given pages - but beware: RecentChanges may get you!",
         "cmd_help" => "syntax: help [command]<br> or even [command] --help<br>\n<br>shows some notes on the given command",
         "cmd_cat" => "syntax: help [command]<br> or even [command] --help<br>\n<br>shows some notes on the given command",
      );
      
      #-- do
      $pf = $funcs[$action];
      if (!$action) {
         echo "nothing to do";
      }
      elseif ($help[$pf] && (($args[0] == "--help") or ($args[0] == "-h") or ($args[0] == "-?"))) {
         echo $help[$pf];
      }
      elseif ($pf && function_exists($pf)) {
         $pf($args);
      }
      elseif ($pf == "cmd_help") {
         if ($args[0]) {
            ($text = $help[$funcs[$args[0]]]) or ($text = "$args[0]: no help available");
            echo $text;
         }
         else {
            echo "following commands are defined:<br>\n";
            echo implode(", ", array_keys($funcs));
         }
      }
      else {
         echo "unknown command '$action'\n";
      }
      
   }
   
   #-- oooops
   else {
      echo "unknown command() call\n<br>";
   }





#-- action calls / commands -----------------------------------------------


#-- delete page completely
function cmd_rm($ls) {
   foreach ($ls as $id) {
      echo "<em style=\"color:red\">deleting</em> page '$id' (<small>";
      while ($data = get_almost_latest($data, $id)) {
         $version = $data["version"];
         if (ewiki_db::DELETE($id, $version)) {
            echo "error/";
         }
         echo "#$version ";
      }
      echo "</small>)<br>\n";
   }
}


#-- output page contents
function cmd_cat($ls) {
   foreach ($ls as $id) {
      if ($data = ewiki_db::GET($id)) {
         echo htmlentities($data["content"]);
      }
   }
}


#-- directory listing
function cmd_ls($ls) {
   if ((count($ls)==1) && ($ls[0]=="-l")) {
      cmd_ll(NULL);
   }
   else {
      $rx = preg_glob($ls);
      $all = ewiki_db::GETALL(array("id"));
      while ($row = $all->get()) {
         if ($rx && !preg_match($rx, $row["id"])) {
            continue;
         }
         echo $row["id"] . "\n";
      }
   }
}


function cmd_ll($ls) {
   echo "ll: not implemented";
}


#-- renaming
function cmd_mv($ls, $bad=1) {
   cmd_ren($ls, $bad);
}
function cmd_ren($ls, $overwrite=0) {
   if (count($ls) != 2) {
      echo "mv: needs exactly two arguments\n";
   }
   else {
      $old = $ls[0];
      $new = $ls[1];
      echo htmlentities("$old -> $new ");
      if (bad_old_new($old, $new)) {
         return;
      }
      while ($data = get_almost_latest($data, $old)) {

         $data["id"] = $new;

         if (ewiki_db::WRITE($data, $overwrite)) {
            if (ewiki_db::DELETE($old, $data["version"])) {
               echo ".";
            }
            else {
               echo "<sup>(DE#$data[version])</sup>";
               $de += 1;
            }
         }
         else {
            echo "<sup>(WE#$data[version])</sup>";
            $we += 1;
         }

      }
      echo "\n<br><br>\n";
      if ($we) {
         echo "- $we write errors (target page existed)<br>\n";
      }
      if ($de) {
         echo "- $de errors deleting source page versions (uh, bad!)<br>\n";
      }
   }
}


#-- duplication
function cmd_cp($ls) {
   if (count($ls) != 2) {
      echo "cp: needs exactly two arguments\n";
   }
   else {
      $old = $ls[0];
      $new = $ls[1];
      echo htmlentities("$old => $new ");
      if (bad_old_new($old, $new, "TARGET_EXIST_WARN")) {
         return;
      }
      while ($data = get_almost_latest($data, $old)) {
         $data["id"] = $new;
         if (ewiki_db::GET($new, $data["version"])) {
            echo "<sup>(OW#$data[version])</sup>";
            $ow += 1;
         }
         if (ewiki_db::WRITE($data)) {
            echo ". ";
         }
         else {
            echo "<sup>(WE#$data[version])</sup>";
            $we += 1;
         }
      }
      echo "\n<br><br>\n";
      if ($ow) {
         echo "- $ow target page versions overwritten<br>\n";
      }
      if ($we) {
         echo "- $we write errors (uh, bad!)<br>\n";
      }
   }
}


#-- updated {lastmodified} stamps
function cmd_touch($ls) {
   foreach ($ls as $id) {
      if ($data = ewiki_db::GET($id)) {
         echo "$data[id]<br>\n";
         $data["lastmodified"] = time();
         ewiki_db::WRITE($data, $overwrite=1);
      }
      else {
         echo "'$id' does not exist<br>\n";
      }
   }
}





#-- helper calls ---------------------------------------------------------


function split_input($s) {

   $r = array();

   while (strlen($s = ltrim($s))) {
   
      if (preg_match("/^\"([^\"]+|\\\")\"|^'([^']+|\\')'|^([^\s]+)/", $s, $uu)) {
         if (($e = $uu[1]) || ($e = $uu[2])) {
            $r[] = stripslashes($uu[1]);
         }
         else {
            $r[] = $uu[3];
         }
      }
      else {
         $r[] = $s;
      }
      
      $s = substr($s, strlen($uu[0]));
   }
   
   return($r);
}


function preg_glob($ls=NULL) {
   foreach ((array)$ls as $glob) {
      $rx[] = str_replace("\\*", ".*", preg_quote($glob));
   }
   if ($rx) {
      return( "/^(" . implode("|", $rx) . ")$/" );
   }
}


#-- fetch version after version (decreasing),
#   skip over remaining ones (when deleting fails)
function get_almost_latest($data, $id) {
   if (!$data) {
      $data = ewiki_db::GET($id);
   }
   else {
      $last_ver = $data["version"];
      $data = NULL;
      while ((--$last_ver) > 0) {
         if ($data = ewiki_db::GET($id, $last_ver)) {
            break;
         }
         
      }
   }
   return($data);
}


function bad_old_new($old, $new, $tt=0) {
   if (! ewiki_db::GET($old)) {
      echo "<br>src page not found!\n";
      return 1;
   }
   else {
      echo "<br>hmm, no - won't do that (same page name)\n";
      return 1;
   }
   if ($tt && ewiki_db::GET($new)) {
      echo "<br><b>warning</b>: target page exists, and latest versions may not get overwritten by this copy process\n<br>\n";
   }
}

?>
</body>
</html>