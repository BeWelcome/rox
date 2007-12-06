<?php

/*
   Belongs to mpi_bugreports; provides bug status change buttons.
*/

$ewiki_plugins["mpi"]["bugreportstate"] = "ewiki_mpi_bugreportstate";


#-- show submit buttons or change associated pages (from backlinks)
function ewiki_mpi_bugreportstate($action, $args, &$iii, &$s) 
{
    global $ewiki_id;

    #-- possible states    
    $values = array("open", "closed", "done", "frozen", "answered");
    foreach ($args as $i=>$name) if (is_int($i)) {
       if (preg_match("/^\w+$/", $name) && !in_array($name, $values)) {
          $values[] = $name;
       }
    }
    $rxvals = implode("|", $values);

    #-- if button pressed
    if (($new = $_REQUEST["brs_change"]) && in_array($new, $values)) {
    
       $pages = ewiki_get_backlinks($ewiki_id);
       $pages[] = $ewiki_id;

       #-- change assoc pages
       foreach ($pages as $id) {
          $data = ewiki_db::GET($id);
          if (preg_match("/\n\|.*stat(e|us).*\|.*($rxvals)/", $data["content"])
          or preg_match("/\n\|.*($rxvals).*\|.*$ewiki_id/", $data["content"])) {
             $data["content"] = preg_replace(
                "/(\n\|.*stat(?:e|us).*\|.*?)[_*]*(?:$rxvals)[_*]*/",
                "$1$new",
                $data["content"]
             );
             $data["content"] = preg_replace(
                "/(\n\|.*?)[_*]*(?:$rxvals)[_*]*(.*?\|.*$ewiki_id)/",
                "$1$new$2",
                $data["content"]
             );
             ewiki_db::UPDATE($data);
             $data["version"]++;
             ewiki_db::WRITE($data);
          }
       }

       $o = "<p>(status changed)</p>";
       $iii[0][0] = preg_replace("/($rxvals)/", "$new", $iii[0][0]);
    }
    
    #-- show form/buttons
    else {

       $url = ewiki_script("", $ewiki_id);
       $o .=<<<EOT
<form action="$url#added" method="POST" enctype="multipart/form-data">
<input type="submit" name="brs_change" value="open">
<input type="submit" name="brs_change" value="closed">
<input type="submit" name="brs_change" value="frozen">
</form>
EOT;
    }

    return($o);
}


?>