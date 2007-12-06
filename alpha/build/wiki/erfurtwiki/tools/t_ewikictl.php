<?php

  include("t_config.php");

?>
<html>
<head>
 <title>ewikictl (www-only interface)</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>ewikictl<sup><small>/web</small></sup></h1>

<?php

  if (empty($_REQUEST["action"])) {

    ?>

    <tt>ewikictl</tt> is a commandline tool, ought to be used from within
    a shell account on your Web server. If your provider doesn't give you
    that you have to stick to the features that can be provided through
    this www-only interface.
    <br>
    <br>
    You often need to prepare a www-deamon writeable directory, into
    which you upload files before or download from after execution of one
    of the actions. Inconvinient as it is, this is the only way to work
    with this tool.
    <br>


    <br>
    <table border="0" width="85%">
    <tr>
    <td width="50%" valign="top" class="add-border-right">
      <h4>--backup</h4>
      <?php
         aform(
           "backup",
           array(
             "dest" => array("", "", "directory", "(must exist on web server and be world-writeable)"),
             "format" => array(array(
                  "fast" => "fast files (recommended)",
                  "flat" => "flat files (rfc822 format)",
                  "plain" => "plain text pages",
                  "meta" => "plain text companion .meta",
                  "xmlmeta" => "plain with .meta in xml",
                  "xml" => "XML-like format",
                  "sql" => "MySQL INSERT (backup only)",
                ), "select", "file format", "(not all can be imported again!)<br>"),
             "all" => array("1", "x", "all versions", "backs up database fully"),
             "enc" => array("1", "x", "urlencode", "makes filenames DOS/Win-compatible"),
             "force" => array("1", "checkbox", "force", "don't stop on errors"),
           )
         );
      ?>
    </td>
    <td width="50%" valign="top">
      <h4>--insert</h4>
      <?php
         aform(
           "insert",
           array(
             "source" => array("", "", "directory", "(you must upload the backed up files there beforehand)"),
             "format" => array(array(
                  "fast" => "fast files (serialized)",
                  "flat" => "flat files (message/http)",
                  "plain" => "plain text pages",
                ), "select", "file format", "<br>"),
             "all" => array("1", "x", "versioned files", "if they have .NNN extensions"),
             "keep" => array("1", "x", "keep", "don't overwrite existing page versions"),
             "dec" => array("1", "x", "urldecode filenames", "from Win-sys"),
             "force" => array("1", "checkbox", "force", "don't stop on errors"),
           )
         );
      ?>
    </td>
    </tr></table>


    <br>
    <table border="0" width="80%">
    <tr>
    <td width="60%" valign="top" class="add-border-right">
      <h4>--holes</h4>
      Removes older revisions (in given range) from all pages. It can
      automatically make a backup of the killed data.
      <br><br>
      <?php
         aform(
           "holes",
           array(
             "holes_start" => array("2", "", "keep versions", "how many of the first few versions to keep (no 1 must remain for flat file databases!)"),
             "holes_end" => array("-10", "", "kill from end", "that many versions shall remain at the end (minimum is 1 here of course!)<br>"),
             "dest" => array("", "", "backup dir", "(must exist on web server and be world-writeable)"),
             "format" => array(array(
                  "fast" => "fast files",
                  "flat" => "flat files",
                  "plain" => "plain text",
                ), "select", "backup format", "<br>"),
             "all" => array("1", "x", "backup all", "(implicit)"),
             "enc" => array("1", "x", "backup filnames encode", ""),
             "force" => array("1", "checkbox", "force", "don't stop on errors"),
           )
         );
      ?>
    </td>
    <td width="40%" valign="top">
      <h4>--help</h4>
      to learn about new features in the commandline version ;)
      <br><br>
      <?php
         aform(
            "help",
            array()
         );
      ?>
    </td>
    </tr></table>



    <br>
    <table border="0" width="99%">
    <tr>
    <td width="25%" valign="top" class="add-border-right">
      <h4>--list</h4>
      shows a wide list of all existing pages, including flags, size
      author name and timestamp (like 'ls -l' under Unix)
      <?php
         aform(
           "ls",
           array()
         );
      ?>
    </td>
    <td width="40%" valign="top" class="add-border-right">
      <h4>--rename</h4>
      <?php
         aform(
            "rename",
            array(
              "0" => array("", "", "from", "(pagenames)"),
              "1" => array("", "", "to"),
            )
         );
      ?>
    </td>
    <td width="40%" valign="top" class="add-border-right">
      <h4>--chmod</h4>
      <?php
         aform(
            "chmod",
            array(
              "file" => array("SandBox", "", "page", ""),
              "0" => array("+TEXT,-OFF", "", "flags", "can be either decimal (54), octal (0377) or hex (0x137f) - or a combination of <a href=\"t_commander/info.php\">flag abbreviations</a> separated by comma, + or a - sign<br> &nbsp; like <tt>+HTM,WRITE,-OFF</tt>"),
            )
         );
      ?>
    </td>
    <td width="40%" valign="top">
      <h4>--unlink</h4>
      deletes a page completely (with all its versions)<br><br>
      <?php
         aform(
            "unlink",
            array(
              "0" => array("SandBox", "", "page", ""),
            )
         );
      ?>
    </td>
    </tr></table>


    <br>
    <br>
    
    There are equations for some of the above commands in the collection
    of <a href=".">database tools</a>, that sometimes provide more options
    or are easier to use.
    
    <br>
    <br>

    <?php

  }



  else {

     #-- build request
     $argv = array();
     $action = $_REQUEST["action"];
     
     // action
     $argv[0] = "t_ewikictl";
     $argv[1] = "--$action";
     
     // special treatment to
     if ($action == "holes") {
        $argv[] = $_REQUEST["holes_start"] . ".." . $_REQUEST["holes_end"];
     }
     if (($action == "holes") && ($dir = $_REQUEST["dir"])) {
        $argv[] = "--backup";
     }

     // numeric args
     foreach ($_REQUEST as $field=>$str) {
        if (is_int($field) && $str) {
           $argv[] = $str;
        }
     }

     // take common --args asis
     foreach (array("force", "dest", "source", "dir", "all",
        "urlencode", "urldecode", "keep", "db", "file")
     as $field)
     {
        if ($str = $_REQUEST[$field]) {
           $argv[] = "--$field";
           if ($str !== "1") {
              $argv[] = $str;
           }
        }
     }
     

     #-- perform
     echo "<h3>--$action</h3>\n<br>\n";

     ob_start();
     $GLOBALS["argv"] = $_SERVER["argv"] = $argv;
     $GLOBALS["argv"] = $_SERVER["argc"] = count($argv);
     $_SERVER["SERVER_SOFTWARE"] = 0;
     include("ewikictl");
     
     #-- output
     $text = ob_get_contents();
     ob_end_clean();
     #-- transform
     $text = htmlentities($text);
     $text = preg_replace('/\033\[([\d;]+)m/e', 'ansiesc2font("$1")', $text);
     echo '<pre style="background:#111611;color:white;font:monospace;display:block;padding:5px;">'
        . "\n\n$text\n\n"
        . "</pre>";



  }// end of everything here
     
  


#-- output form for one action
function aform($action, $args) {
   static $xn=0;
   echo '<form action="t_ewikictl.php">';
   echo "\n<input type=\"hidden\" name=\"action\" value=\"$action\">\n";
   foreach ($args as $i=>$l) {
      echo "<label for=\"$i$xn\">$l[2]</label> ";
      if (is_array($l[0])) {
         echo "<select name=\"$i\" id=\"$i\">";
         foreach ($l[0] as $v=>$vd) {
            echo "<option value=\"$v\">$vd</option>";
         }
         echo "</select>\n";
      }
      else {
         if (!strlen($l[1])) {
            $l[1] = "text";
         }
         elseif ($l[1]=="x") {
            $l[1] = 'checkbox" checked="checked';
         }
         echo "<input type=\"$l[1]\" name=\"$i\" id=\"$i$xn\" value=\"$l[0]\">\n";
      }
      echo "<small>$l[3]</small>\n<br>\n";
   }
   echo '<input type="submit" value="do">';
   echo "</form>\n";
   $xn++;
}



function ansiesc2font($str) {
   $cols = array(
      "black", "red", "green", "magenta", "blue", "yellow", "grey", "white",
   );
   foreach (explode(";", $str) as $n) {
      $col = $cols[0 + ((0+$n) % 10)];
      if (($n == "37") or ($n == "0") or ($n == "27")) {
         return "</b>";
      }
      elseif ($n >= 40) {
         return "<b style=\"background:$col\">";
      }
      elseif ($n >= 30) {
         return "<b style=\"color:$col\">";
      }
   }
}
  
?>

</body>
</html>