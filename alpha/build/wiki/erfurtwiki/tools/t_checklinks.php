<?php
  include("t_config.php");
?>
<html>
<head>
 <title>check http:// links of a wiki page</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>link check</h1>
<?php


  if (empty($_REQUEST["page"])) {

     echo '
This tool checks all http:// links for availability, afterwards resaves the
wiki page with the dead links marked for easier editing.
<br><br>
<form action="t_checklinks.php" method="POST">

<select name="page_pattern">
  <option value="1" selected>only page</option>
  <option value="2">page pattern</option>
</select>
<input name="page" size="40" value="LinkDirectory"><br><br>

<input type="submit" value="check http:// links"><br><br>

<input type="checkbox" name="opt[modify]" checked value="1" id="opt-modify"><label for="opt-modify"> modify pages, mark bad links</label><br>
<input type="checkbox" name="opt[404]" checked value="1" id="opt-404"><label for="opt-404"> extened error 404 checks (to detect text-only/human-visible error messages of buggy portal scripts)</label><br>
<br>
</form>
';

  }
  else {
  
     echo "<h4>link checking starts...</h4>\n";
     set_time_limit(0);
  
     $ext404 = $_REQUEST["opt"]["404"];
     $modify = $_REQUEST["opt"]["modify"];
     
     #-- single page mode
     if ($_REQUEST["page_pattern"]==1) {
        $id = $_REQUEST["page"];
        check_links_for($id, $ext404, $modify);
     }
     
     #-- multiple pages
     else {
        $pat = trim($_REQUEST["page"], "*");
        $result = ewiki_db::SEARCH("id", $pat);
        while ($row = $get->result) {
           check_links_for($row["id"], $ext404, $modify);
        }
     }

     echo "<br><b>done.</b><br><br>";
  }
  
  
function check_links_for($id, $ext404=1, $modify=1) {

     echo "<b><tt>$id</tt></b><br>\n";

     $get = ewiki_db::GET($id);
     $content = $get["content"];
     
     preg_match_all('_(http://[^\s"\'<>#,;]+[^\s"\'<>#,;.])_', $content, $links);
     $badlinks = array();
     foreach ($links[1] as $href) {

        set_time_limit(20);
        $d = @implode("", @file($href));
        
        if ($ext404) {
           if (strstr($d, "Apache/") && (stristr($d, "file not found") || stristr($d, "error 404"))) {
              $d = "";
           }
        }
        if (empty($d) || !strlen(trim($d))) {
           echo "[DEAD] $href<br>\n";
           $badlinks[] = $href;
        }
        else {
           echo "[OK] $href<br>\n";
        }
     }

     #-- if sometihng found, modify on request
     if ($modify) {
         #-- replace dead links
         foreach ($badlinks as $href) {
            $content = preg_replace("\377^(.*$href)\377m", ' µµ__~[OFFLINE]__µµ   $1', $content);
         }

         #-- compare against db content
         if ($content != $get["content"]) {

            $get["content"] = $content;
            $get["version"]++;
            $get["author"] = ewiki_author("ewiki_checklinks");
            $get["lastmodified"] = time();

            ewiki_db::WRITE($get);
            echo "<sup>(updated)</sup><br>";
         }
     }
}


?>
</body>
</html>