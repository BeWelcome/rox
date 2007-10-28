<?php

/*
   This plugin works basically the same as "userdb_systempasswd", but adds a
   page, where users can register themselfes into the "UserRegistry" to get
   access with permission ring level 2 ("editing"). You could use this one
   in conjunction with "userdb_systempasswd" (to separate ordinary users from
   higher level moderators).

   The virtual page "UserRegistry" (you can change this name) can be used
   by users to create an account and to change its settings (you could add
   data fields). The _SYSTEM page "system/UserRegistry" can only be edited
   by a superuser (ring level 0), and the permission/ring level could also
   be changed for individual users therein (making use of "_systempasswd"
   plugin optional).
*/

#-- settings
define("EWIKI_PAGE_USERREGISTRY", "UserRegistry");
define("EWIKI_USERDB_USERREGISTRY", "system/UserRegistry");
define("EWIKI_REGISTERED_LEVEL", 2);     # ordinary users

#-- glue
$ewiki_plugins["auth_userdb"][] = "ewiki_auth_userdb_userregistry";
$ewiki_plugins["page"][EWIKI_PAGE_USERREGISTRY] = "ewiki_page_userregistry";


#-- text
$ewiki_t["en"]["PLEASE_RETRY"] = "Please retry.";
$ewiki_t["en"]["WRONG_PW"] = "You supplied the wrong password!";
$ewiki_t["en"]["RETYPE_PW"] = "Password and retyped password do not match!";
$ewiki_t["en"]["PW_ONLY_LETTERS"] = "Only letters and numbers are allowed in user names and passwords.";
$ewiki_t["en"]["USERNAME_MIN"] = "User names must be at least 3 characters long.";
$ewiki_t["en"]["USERNAME_ALREADY_USED"] = "This user name is already used.";
#-- translations
$ewiki_t["de"]["PLEASE_RETRY"] = "Bitte, versuch's nochmal.";
$ewiki_t["de"]["WRONG_PW"] = "Du hast ein falsches Paßwort eingegeben!";
$ewiki_t["de"]["RETYPE_PW"] = "Paßwort und Wiederholung stimmen nicht überein!";
$ewiki_t["de"]["PW_ONLY_LETTERS"] = "Nur Buchstaben und Zahlen sind im Paßwort und Benutzernamen erlaubt.";
$ewiki_t["en"]["USERNAME_MIN"] = "Benutzernamen müssen wenigstens 3 Zeichen lang sein.";
$ewiki_t["en"]["USERNAME_ALREADY_USED"] = "Dieser Benutzername wird schon verwendet.";
// partial text _{snippets}
$ewiki_t["de"]["New Account"] = "Neues Konto";
$ewiki_t["de"]["Account Settings"] = "Konto Einstellungen";
$ewiki_t["de"]["change password"] = "Paßwort Ändern";
$ewiki_t["de"]["new password"] = "neues Paßwort";
$ewiki_t["de"]["password"] = "Paßwort";
$ewiki_t["de"]["retype"] = "wiederholen";
$ewiki_t["de"]["user/login name"] = "Benutzer/Login-Name";
$ewiki_t["de"]["name"] = "Name";
$ewiki_t["de"]["save"] = "Speichern";
$ewiki_t["de"]["optional infos"] = "Optionale Angaben";
$ewiki_t["de"]["personal WikiPage"] = "persönliche WikiSeite";
$ewiki_t["de"]["email address"] = "EMail-Adresse";
$ewiki_t["de"]["create account"] = "Konto anlegen";
$ewiki_t["de"]["change settings"] = "Einstellungen ändern";
$ewiki_t["de"]["Data saved"] = "Daten gespeichert";
$ewiki_t["de"]["Error saving"] = "Fehler beim Speichern";


#-- user database query
function ewiki_auth_userdb_userregistry($username, $password=NULL) {

   global $ewiki_config;

   #-- get pw list
   $data = ewiki_db::GET(EWIKI_USERDB_USERREGISTRY);
   
   #-- search user
   $entry = array();
   foreach (explode("\n",$data["content"]) as $line) {
      $line = trim($line);
      if (strtok($line, ":") == $username) {
         $line = substr($line, strpos($line, ":") + 1);
         $entry = explode(":", $line);
         break;
      }
   }

   return($entry);
}



#-- virtual registration page -------------------------------------------
function ewiki_page_userregistry($id, &$data, $action) {

   global $ewiki_plugins, $ewiki_config, $ewiki_auth_user;

   $o = ewiki_make_title($id, $id, 2, $action);
   $url = ewiki_script("", $id);

   #-- auto-login
   if ($ewiki_auth_user && empty($_REQUEST["userreg_name"])) {
      $user = $ewiki_auth_user;
      $uu = ewiki_auth_userdb_userregistry($ewiki_auth_user);
      $pw = $uu[0];
      $_REQUEST["userreg_login"] = 1;
   }
   else {
      $user = trim($_REQUEST["userreg_name"]);
      $pw = $_REQUEST["userreg_pw"];
   }

   #-- try to get user entry
   $ue = ewiki_auth_userdb_userregistry($user);

   #-- account creation ---------------------------------------------------
   if ($_REQUEST["userreg_register"] && empty($ue)) {

      $o .= ewiki_t(<<< END
<h4>_{New Account}</h4>
<form action="$url" method="POST" enctype="multipart/form-data" accept-encoding="ISO-8859-1">
_{user/login name} <input type="text" size="14" name="userreg_name" value="$user"> <br />
<input type="hidden" name="userreg_pw" value="">
<br />
_{password} <input type="password" name="new_pw" size="10" maxsize="12" value="$pw"> <br />
_{retype} <input type="password" name="new_pw2" size="10" maxsize="12" value=""> <br />
<br />
<input type="submit" name="userreg_store" value="_{create account}">
</form><br /><br />
END
      );

      return($o);  // finished here, prevent fallthrough-display of login-form
   }

   #-- check password
   if ($ue && $user && !ewiki_auth_user($user,$pw)) {
      $o .= $_REQUEST["userreg_register"]
               ? ewiki_t("USERNAME_ALREADY_USED")
               : (ewiki_t("WRONG_PW") . "\n" . ewiki_t("PLEASE_RETRY"));
      return($o);
   }

   #-- set fallback settings for account creation
   if (empty($ue) && $_REQUEST["userreg_store"]) {
      $ue =
      $_REQUEST["userreg_ue"] =
      array(
         $pw,
         EWIKI_REGISTERED_LEVEL,
         "", "", ""
      );
   }
   
   #-- check username
   if (preg_match("/[^".EWIKI_CHARS_U.EWIKI_CHARS_L."]/", $user.$pw)) {
      $o .= ewiki_t("PW_ONLY_LETTERS") . "\n" . ewiki_t("PLEASE_RETRY");
      return($o);
   }
   elseif ($name && (strlen($user) < 3)) {
      return($o . ewiki_t("USERNAME_MIN"));
   }

   #-- save changes -------------------------------------------------------
   if ($_REQUEST["userreg_store"] && $user) {

      #-- new user entry
      ($new_ue = $_REQUEST["userreg_ue"]) or ($new_ue = array());
      $new_ue[0] = $pw;
      ($new_ue[1] = $ue[1]) or ($new_ue[1] = EWIKI_REGISTERED_LEVEL);
      if ($new_pw = $_REQUEST["new_pw"]) {
         if ($new_pw == $_REQUEST["new_pw2"]) {
            $new_ue[0] = md5($new_pw);
         }
         else {
            $o .= ewiki_t("RETYPE_PW") ."\n<br />";
            return($o);
         }
      }
      foreach ($new_ue as $i=>$v) {
         $new_ue[$i] = preg_replace("/[^-@._ \w\d".EWIKI_CHARS_L.EWIKI_CHARS_U."]/", " ", $v);
      }

      #-- get user db page
      ($data = ewiki_db::GET(EWIKI_USERDB_USERREGISTRY))
      or ($data = array(
        "id" => EWIKI_USERDB_USERREGISTRY,
        "version" => 1, flags => 0,
        "created" => time(), "lastmodified" => time(),
        "content" => "nobody:*:3::", "meta" => "",
        "author" => ewiki_author("$user@$id"),
      ));
      $data["flags"] |= EWIKI_DB_F_SYSTEM;
      $list = explode("\n", $data["content"]);

      #-- update entry
      ksort($new_ue);
      $new_ue = $user . ":" . implode(":", $new_ue);
      $found = 0;
      foreach ($list as $i=>$line) {
         $line = trim($line);
         if (strtok($line, ":") == $user) {
            $list[$i] = $new_ue;
            $found = 1;
         }
      }
      if (!$found) {
         $list[] = $new_ue;
      }

      #-- save back
      $data["content"] = implode("\n", $list);
      $retry = 3;
      while ($retry--) {
         $data["version"]++;
         if ($ok = ewiki_db::WRITE($data)) {
            break;
         }
      }
      if ($ok) {
         $o .= ewiki_t("Data saved")."\n<br />";
      }
      else {
         $o .= ewiki_t("Error saving")."\n<br />";
         ewiki_log("_userdb_userregistry: failed to update db for user $user, retries=$retry", 2);
      }

      #-- fallthru to view_settings
      $_REQUEST["userreg_login"] = 1;
      $ue = ewiki_auth_userdb_userregistry($user);
   }


   #-- view settings ----------------------------------------------------
   if ($_REQUEST["userreg_login"]) {

      #-- edit <form>
      $o .= ewiki_t(<<< END
<h4>_{Account Settings}</h4>
<form action="$url" method="POST" enctype="multipart/form-data" accept-encoding="ISO-8859-1">
<input type="hidden" name="userreg_name" value="$user">
<input type="hidden" name="userreg_pw" value="$pw">
<b>_{change password}</b><br />
_{new password} <input type="password" size="10" maxsize="12" name="new_pw" value=""> <br />
_{retype} <input type="password" size="10" maxsize="12" name="new_pw2" value=""> <br />
<br />
<b>_{optional infos}</b><br />
_{personal WikiPage} <input type="text" name="userreg_ue[2]" value="{$ue[2]}"><br />
_{email address} <input type="text" name="userreg_ue[3]" value="{$ue[3]}"><br />
<!--
opt string <input type="text" name="userreg_ue[4]" value="{$ue[4]}"><br />
opt string <input type="text" name="userreg_ue[5]" value="{$ue[5]}"><br />
opt string <input type="text" name="userreg_ue[6]" value="{$ue[6]}"><br />
-->
<br />
<input type="submit" name="userreg_store" value="_{save}">
</form><br /><br />
END
      );
   }

   #-- print login <form> ---------------------------------------------
   else {

      $url = ewiki_script("", $id);
      $o .= ewiki_t(<<< END
<form action="$url" method="POST" enctype="multipart/form-data" accept-encoding="ISO-8859-1">
<div class="userreg-form-settings">
<div class="userreg-form-register">
_{name} <input type="text" size="14" name="userreg_name"> &nbsp;
<input type="submit" name="userreg_register" value="_{create account}"><br />
</div>
<br />
_{password} <input type="password" size="10" maxsize="12" name="userreg_pw"><br />
<br />
<input type="submit" name="userreg_login" value="_{change settings}">
</div>
</form><br /><br />
END
      );
   }

   return($o);
}


?>