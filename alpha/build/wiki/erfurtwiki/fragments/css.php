<style type="text/css"><!--
<?php

  /*
     Just include() this fragment in the <head> part of yoursite.php,
     it will include the appropriate stylesheet snippets for the
     current page.
     You may want to remove the surrounding <style> tags here, if
     you already have them in yoursite.
  */

  #-- base dir
  $_css_dir = dirname(__FILE__) . "/css/";

  #-- page id
  if (!$ewiki_id) {
     $ewiki_action = EWIKI_DEFAULT_ACTION;
     $ewiki_id = ewiki_id();
     if (strpos($ewiki_id, "/")) {
        list($ewiki_action, $ewiki_id) = explode("/", $ewiki_id, 2);
     }
  }

  #-- PageName.css
  if (file_exists($_css = $_css_dir.$ewiki_id.".css") || file_exists($_css = $_css_dir.strtolower($ewiki_id).".css")) {
     include($_css);
  }

  #-- action.css
  if (file_exists($_css = $_css_dir.$ewiki_action.".css")) {
     include($_css);
  }

?>
//--></style>
