<?php
  include("t_config.php");

  #-- get current page id
  $id = $_REQUEST["id"];
?>
<html>
<head>
 <title>WikiCommander:PageDetails:<?php echo $id; ?></title>
 <link rel="stylesheet" type="text/css" href="80x25.css">
</head>
<body bgcolor="#0000c0" text="#eeeeee" class="Edit PageDetails Panel">
<?php

   #-- get current page data
   ($version = $_REQUEST["version"])
   or ($version = $_REQUEST["new"]["version"]);
   $data = ewiki_db::GET($id, $version);
   $version = $data["version"];


   #--  single-version page deletion --------------------------------------
   if ($_REQUEST["delete"] && $version) {
      {
         ewiki_db::DELETE($id, $version);
         echo '<div class="msg">deleted version #'.$version.'</div>'."\n";
      }
      $data = ewiki_db::GET($id);
      $id = $data["id"];
      $version = $data["version"];
   }


   #-- show errors
   if (!$data || !$version) {
      echo "<div class=\"msg\">page does not exist</div>\n";
   }


   #-- save edited page data --------------------------------------------
   if ($_REQUEST["save"] && $_REQUEST["new"]["id"]) {

      // read in given fields as-is
      $data;
      foreach ($_REQUEST["new"] as $field=>$value) {
         $data[$field] = $value;
      }
      // {id}
      $id = $data["id"];
      // {meta}
      $data["meta"] = recreate_meta($_REQUEST["new"]["meta"]);
      // {flags}
      $data["flags"] = 0x0000;
      foreach ($_REQUEST["new_flags"] as $bit=>$state) {
         if ($state) {
            $data["flags"] += (1<<$bit);
         }
      }
      // {lastmodified}
      if ($_REQUEST["update"]["lastmodified"]) {
         $data["lastmodified"] = time();
      }
      // {refs}
      if ($_REQUEST["update"]["refs"]) {
         ewiki_scan_wikiwords($data["content"], $links, "_STRIP_EMAIL=1");
         $data["refs"] = "\n\n" . implode("\n", array_keys($links)) . "\n\n";
      }
      else {
         $data["refs"] = "\n\n" . trim(trim($data["refs"], "\n"), "\r") . "\n\n";
      }
      // {content}
      if ($data["flags"] & EWIKI_DB_F_TEXT) {
         $data["content"] = preg_replace("/\r*(\n)\r?/", "$1", $data["content"]);
      }

      #-- ok, throw into DB
      if (ewiki_db::WRITE($data, $overwrite=1)) {
         echo "<div class=\"msg\">page written into database</div>\n\n";
      }
      else {
         echo "<div class=\"msg\">error writing entry to db</div>\n\n";
      }
   }
   
   
   #-- fill with inital data
   if ($_REQUEST["create"] && !$data) {
      $data = ewiki_db::CREATE($id);
   }

      
   #-- start output
   echo '<form action="edit.php" method="POST" enctype="multipart/form-data">';
   echo '<input type="hidden" name="id" value="'.htmlentities($id).'">';

   #-- {id}
   print_field($data, "id", 32);
   
   #-- {version}
   print_field($data, "version", 3, "", "");
   echo ' <input type="submit" name="refresh" value="refr" title="refresh view, show given version"> ';
   echo ' <a href="list_vers.php?id='.urlencode($id).'">...</a> ';
   echo '<input type="submit" name="delete" value="del" title="delete current page version"><br>' . "\n";
   
   #-- {flags}
   print_flags($data);
   
   #-- {author} and {hits}, {created}, {lastmodified}
   print_field($data, "author", 26);
   print_field($data, "hits", 4);
   print_field($data, "created", 11);
   print_field($data, "lastmodified", 11, "", "");
   echo '<input type="checkbox" name="update[lastmodified]" value="1" title=":= time()"><br>';
  
   #-- [save]
   echo "\n<small><br>\n"
      . '<input type="submit" name="save" value="update database entry">'
      . "\n<br></small><br>\n";
      
   #-- {content}
   if (($data["flags"]&EWIKI_DB_F_BINARY) && !$_REQUEST["show_content"]) {
      echo '<label for="show_content">content</label><br>'
         . '<input id="show_content" type="submit" name="show_content" value="show" title="contains binary data, beware!"><br><br>';
   }
   else {
      print_textfield($data["content"], "content", 16);
   }
   
   #-- {meta}
   print_textfield(flatten_meta($data["meta"]), "meta", 5);

   #-- {refs}   
   $inj = ' &nbsp;<input type="checkbox" checked name="update[refs]" value="1" id="update_refs">'
        . '<label for="update_refs"> update automatically</label><br>';
   print_textfield(ltrim($data["refs"]), "refs", 4, $inj);
   
   // done


   

#------------------------------------------------------- helper functions ---   

   #-- output <input> tag for one of the database {field}s
   function print_field(&$data, $name, $size, $inj="", $add="<br>") {
      echo "<nobr><label for=\"d_$name\">$name&nbsp;</label>"
      . "<input id=\"d_$name\" type=\"text\" size=\"$size\" name=\"new[$name]\" value=\""
      . htmlentities($data[$name]) . "\"$inj></nobr>$add\n";
   }
   
   #-- output checkboxes for {flags}
   function print_flags(&$data) {
      $names = array("txt", "bin", "off", "htm", "ro", "wr", "app", "sys",
      "prt", "min", "hid",
      "arv", "u12",
      // 13=>"u13", "u14", "u15", "u16",
      17=>"exe",
      // 18=>"u18", "u19", "u20"
      );
      echo "<label for=\"d_flags\">flags</label>&nbsp;<small id=\"d_flags\">";
      foreach ($names as $i=>$str) {
         echo "<input type=\"checkbox\" id=\"d_f_$i\" name=\"new_flags[$i]\""
            . " value=\"1\"" . (($data["flags"] & (1<<$i)) ? " checked": "")
            . "><label for=\"d_f_$i\">$str</label> ";
      }
      echo "</small><br>\n";
   }

   #-- output <textarea> for bit database {field}
   function print_textfield($text, $name, $rows, $inj="<br>", $add="<br><br>") {
      echo "<label for=\"d_$name\">$name</label>$inj"
      . "<textarea id=\"d_$name\" cols=\"32\" rows=\"$rows\""
      . " name=\"new[$name]\" wrap=\"soft\" style=\"width:95%\">"
      . htmlentities($text) . "</textarea>$add\n";
   }
   
   #-- fold {meta} array into block
   function flatten_meta(&$a, $base="") {
      $text = "";
      $a = (array)$a;
      foreach ($a as $name=>$value) {
         if (is_array($value) && is_int($name)) {
            $value = implode(", ", $value);  // for {meta}{meta} fields
         }
         if (is_array($value)) {
            $text .= flatten_meta($value, "$base$name:");
         }
         else {
            $text .= "$base$name: $value\n";
         }
      }
   }


   #-- fold {meta} array into block
   function recreate_meta($text) {
      $m = array();
      foreach (explode("\n", $text) as $line) {
         $line = preg_replace("/\s+/", " ", rtrim($line));
         if ($r = strpos($line, " ")) {
            $l = strrpos(substr($line, 0, $r), ":");
         }
         else {
            $l = strrpos($line, ":");
         }
         if (!$l) {
            continue;
         }
         $name = substr($line, 0, $l);
         $line = substr($line, $l + 1);
         $sub = explode(":", $name);
         if ($sub[0] == "meta") {
            $m["meta"][$sub[1]] = preg_split("/\s*,\s*/", $line);
         }
         else {
            $p = & $m;
            foreach ($sub as $name) {
               if (!isset($m[$name])) {
                  $p[$name] = array();
               }
               $p = & $p[$name];
            }
            $p[$name] = $line;
         }
      }
      return $m;
   }
        
?>
</table>
</body>
</html>
