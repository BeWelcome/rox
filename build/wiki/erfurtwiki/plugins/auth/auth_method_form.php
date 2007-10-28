<?php

/*
   This plugin provides a login <form>, and after verification stores
   username and password into a browser Cookie.

   To use this, you'll also need a user database plugin and a ewiki
   auth_perm plugin in the _PROTECTED_MODE.

*/


#-- glue
$ewiki_plugins["auth_query"][0] = "ewiki_auth_query_form";
define("EWIKI_AUTH_QUERY_SAFE", "eventually");


#-- login form text
$ewiki_t["en"]["LOGIN_QUERY"] = "Please log in to use this function:";
$ewiki_t["en"]["LOGIN_QUERY_2"] = "";
$ewiki_t["de"]["LOGIN_QUERY"] = "Bitte log dich ein, um diese Funktion zu verwenden:";
$ewiki_t["de"]["LOGIN_QUERY_2"] = "";
#-- text snippet translations
$ewiki_t["de"]["user"] = "Nutzer";
$ewiki_t["de"]["password"] = "Paßwort";
$ewiki_t["de"]["login"] = "Einloggen";


#-- code
function ewiki_auth_query_form(&$data, $force_query=0) {

   global $ewiki_plugins, $ewiki_config, $ewiki_errmsg, $ewiki_id,
          $ewiki_action, $ewiki_author, $ewiki_ring;
   $o = &$ewiki_errmsg;

   #-- get user/pw from POST or COOKIE
   if ($_POST["login_user"]) {
      $_user = $_REQUEST["login_user"];
      $_pw = $_REQUEST["login_pw"];
   }
   elseif ($_COOKIE["ewiki_login"]) {
      list($_user,$_pw) = explode(":", base64_decode($_COOKIE["ewiki_login"]));
   }

   #-- check password
   $_success=0;
   if (strlen($_user) && strlen($_pw)) {
      $_success = ewiki_auth_user($_user, $_pw);
   }
  
   #-- store login data as Cookie
   if ($_success && $_POST["login_user"]) {
      setcookie("ewiki_login", base64_encode("$_user:$_pw"), time()+7*24*3600);
   }

   #-- login form
   if ($force_query && !$_success || ($force_query >= 2)) {

      #-- it's safe to call this plugin for interception of running submits
      $_REPOST = "";
      if (defined("EWIKI_AUTH_QUERY_SAFE")) {
         foreach($_POST as $i=>$v) {
            if ($i=="login_name" || $i=="login_pw") {  continue;  }
            $_REPOST .= '<input type="hidden" name="'.$i.'" value="'
                     . preg_replace('/([^\w\d\260-\377])/e', '"&#".ord("$1").";"', $v)
                     . '">'."\n";
         }
         $_REPOST = '<!-- $_REPOST -->'."\n".$_REPOST.'<!-- $_END -->'."\n";
      }

      #-- print
      $o = '<div class="login-form auth-login">'
         . ewiki_make_title($ewiki_id, "Login", $_title_class=4, $ewiki_action, $_go_action="info")
         . ewiki_t("LOGIN_QUERY") . "\n<br /><br />\n"
         . '<form action="'.$_SERVER["REQUEST_URI"].'" method="POST">' . "\n"
         . ewiki_t(
               '_{user} <input type="text" size="14" name="login_user"><br />' . "\n"
             . '_{password} <input type="password" size="10" maxsize="12" name="login_pw"><br /><br />' . "\n"
             . '<input type="submit" value="_{login}"><br /><br />' . "\n"
           )
         . $_REPOST
         . "</form><br /><br />\n"
         . ewiki_t("LOGIN_QUERY_2")
         . '</div>';
   }

   #-- end
   return($_success);
}



?>