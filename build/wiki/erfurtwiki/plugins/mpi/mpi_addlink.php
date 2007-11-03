<?php

/*
   <?plugin AddLink ?> will add an inline <form> to add a link to the
   current page
*/

$ewiki_plugins["mpi"]["addlink"] = "ewiki_mpi_addlink";


// view <form>
function ewiki_mpi_addlink($action, $args, &$iii, &$s) 
{
    global $ewiki_id, $ewiki_action;
    $o = "";

    #-- add URL
    if ($_REQUEST["link_save"]) {

       #-- check parameters
       $url = trim($_REQUEST["link_url"]);
       $text = "";
       $title = $desc = "";
       if (!strpos($url, "example.com") && (strlen($url) > 12) && preg_match('#^https?://#', $url)) {
          $text = implode("", file($url));
          if ($text) {
             (preg_match('#<title[^>]*>([^<]+)</title>#ims', $text, $uu))
             and ($title = $uu[1])
	     or (preg_match('#//([^/]+)#', $url, $uu))
	     and ($title = $uu[1]);
             (preg_match('#<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']#ims', $text, $uu))
             and ($desc = $uu[1])
             or (preg_match('#<body[^>]+>(.+?)</body#ims', $text, $uu))
             and ($desc = strip_tags($uu[1]));
             $desc = substr(preg_replace('/\s+/', " ", $desc), 0, 300);
          }
          $add = ":$title:\n   $url %%%\n   $desc\n";
       }

       #-- store bugreport
       if ($text)  {
           $data = ewiki_db::GET($ewiki_id);
           $data["content"] = rtrim($data["content"]) . "\n" . $add;
           ewiki_data_update($data);
           $data["version"]++;
           ewiki_db::WRITE($data);

           #-- append to page output
           $iii[] = array(
              $add,
              0xFFFF,
              "core"
           );
       }
    }
    else {
       $url = ewiki_script("", $ewiki_id);
       $o .=<<<EOT
<form style="border:2px #333370 solid; background:#7770B0; padding:5px;"class="BugReport" action="$url" method="POST" enctype="multipart/form-data">
<input type="hidden" name="id" value="$ewiki_action/$ewiki_id">
Link <input type="text" name="link_url" value="http://www.example.com/" size="50">
<input type="submit" name="link_save" value="hinzufügen">
</form>
EOT;
    }

    return($o);
}


?>