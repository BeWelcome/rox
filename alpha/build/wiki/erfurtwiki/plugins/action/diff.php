<?php

/*
  this is the "stupid diff", which shows up changes between two
  saved versions of a WikiPage; even if working very unclean it
  allows to see what has changed
  it is accessible through the "info about page" action
*/



 $ewiki_plugins["action"]["diff"] = "ewiki_page_stupid_diff";
 $ewiki_config["action_links"]["info"]["diff"] = "diff";



 function ewiki_page_stupid_diff($id, $data, $action) {


    if ($uu=$GLOBALS["ewiki_diff_versions"]) {
       list($new_ver, $old_ver) = $uu;
       $data = ewiki_db::GET($id, $new_ver);
    }
    else {
       $new_ver = $data["version"];
       $old_ver = $new_ver - 1;
    }
    if ($old_ver > 0) {
       $data0 = ewiki_db::GET($id, $old_ver);
    }

    $o = ewiki_make_title($id, "Differences between version $new_ver and $old_ver of »{$id}«");

    $o .= ewiki_stupid_diff($data["content"], $data0["content"]);

    return($o);
 }


 function ewiki_stupid_diff($new, $old, $show_unchanged=1, $informational=0) {

    $old = preg_split("/\s*\n/", trim($old));
    $new = preg_split("/\s*\n/", trim($new));

    $diff_rm = array_diff($old, $new);
    $diff_add = array_diff($new, $old);
    if ($informational) {
       $i = array_intersect($new, $old);
       if (empty($i)) {
          if (count($diff_add) >= (6.5 * count(array_unique($diff_rm)))) {
             $o .= '<div class="note"><b>(overwritten with previous[?] content)</b></div>' . "\n";
             $diff_add = array();
          }
          elseif ($diff_rm) {
             $o .= '<div class="note"><b>(previous content completely removed)</b></div>' . "\n";
             $diff_rm = array();
          }
       }
    }

    foreach ($new as $i=>$line) {
    
       $i2 = $i;
       while ($rm = $diff_rm[$i2++]) {
          $o .= '<div class="del"><b>-</b><font color="#990000"> <tt>' . htmlentities($rm) . "</tt></font></div>\n";
          unset($diff_rm[$i2-1]);
       }

       if (in_array($line, $diff_add)) {
          $o .= '<div class="add"><b>+</b><font color="#009900"> <tt>' . htmlentities($line) . "</tt></font></div>\n";
       }
       elseif ($show_unchanged) {
          $o .= "<div><b>&nbsp;</b> " . htmlentities($line) . "</div>\n";
       }

    }

    foreach ($diff_rm as $rm) {
       $o .= '<div class="del"><b>-</b><font color="#990000"> <tt>' . htmlentities($rm) . "</tt></font></div>\n";
    }
    
    return('<div class="diff">' . $o . '</div>');
 }


?>