<?php

/*
   This ewiki_auth() permission plugin provides UNIX filesystem like
   file access rights (chmod) to ewiki pages. It should be used together
   with 'userdb_userregistry' and/or 'userdb_systempasswd', and
   additionally uses the _SYSTEM page "system/groups" to map users into
   groups.
   While this plugin gives rich control over page access permissions
   ("edit" and "view" rights), it fails greatly on controling other ewiki
   page actions ("info" and "links" for example). So this plugin tries
   to map other page actions down to the basic UNIX read and write rights
   (which is configurable, see below).

   To create the "system/groups" page, you can call a URL ending in
   "...?id=edit/system/groups" initially. After it is first saved, it will
   become a _SYSTEM page, and only the admin (ring level 0) can further
   edit it. The format of that "page" is as follows:
     # comment
     groupname:*:user1,user2,...
     group2:*: user3, ...
     alsoallowed:*: user4; user5, user6 + user7 | user8 ...
   Note, that the asterisk is mandatory here (don't use the "x" as in
   a Unix systems /etc/groups). As user name delimiters you may use spaces,
   commas, semicolons and a few others (don't do this within /etc/groups!)

   About the UNIX access rights:
    - The "rwx" refers to the access permissions, where "r" stands for read,
      "w" for write, and "x" for executable.
    - There exists such an access right triple for the "owner" of a page,
      one for the member "groups", and a third for anybody else ("world").
    - access rights are stored in single bits, and again in the three groups:
       ... | unused (11-9) | owner (8-6) | group (5-3) | world (2-0) |
           | [0] [0] [0]   | [r] [w] [x] | [r] [w] [x] | [r] [w] [x] |
      "owner"=="user", and "world"=="other",
      the available 15 bits here are referred to as "BBBAAAuuugggooo"
      (Please also keep in mind, that we here often note the combined value
      in octals.)

   Things worth to mention about how this plugin works:
    - Every page can have just one "owner", it however does not require (but
      for setting the access rights).
    - But a page can be given to zero, one or multiple "groups" (to be
      separated by spaces, commas or semicolon when entered on the edit
      screen).
    - Only the owner can add groups.
    - Users are not required to be member of any group.
    - Ring permission levels are still in use with this plugin. You for
      example should have at least one administrator (ring 0) user defined
      in 'system/passwd' or 'system/UserRegistry' (or any other userdb).
    - Administrators (ring 0) are as usual unrestricted in what they do.
    - Moderators (ring level 1) always have read/write permission too (but
      cannot change a pages access rights if they don't own it).
    - To disallow guest users to change pages, you would want to set
      EWIKI_PERM_UNIX_UMASK to 0002 - or even 0022 to disallow even group
      members to edit it (this setting only is for initial defaults).
    - The owner of a page may edit (not view!) it, even if access rights
      were in fact insufficient to do that (they must however be reenabled
      before saving can succeed).
    - Only moderators can change access bits (12-9), and only superusers
      can tweak (15-13) (not shown, not used currently).
    - One must LOGIN BEFORE a page can be occupied. It was possible to add
      auto-login for page ownership requests; but this would fail for badly
      written auth_method plugins (only auth_method_http currently would
      work).
    - A page owner may chown the page to any other user.
    - The initial page owner (any account allowed) can be set by logged-in
      users only, unless _PERM_USERSET_FREE is enabled.
    - Virtual pages (page plugins) cannot have access rights assigned,
      unless you create faked database entries with admin/page_searchcache.
      But editing the access permissions then also required temporarily(!)
      enabling the edit/ function for the superuser with higher priority:
      $ewiki_plugins["action_always"]["edit"]="ewiki_page_edit";
    - The "rwx" access values are combined additive (owner,group,world)
      and then compared against the minimum rights needed to perform a
      certain ewiki $action - this way you can map "r", "w" and "x" to
      "view/", "info/" and "edit/" actions - see ["perm_rights_actions"]
      in $ewiki_config[].
      "Additive combination" means merging the effective rights ("uuugggooo")
      into a 3 bit integer. The "BBBAAA" bits are doubled into bits 8-3, so
      "BBBAAABBBAAAeee" is available for that comparison - but you'll often
      just want to check the last three bits.
    - initial page access rights are (0777 minus _UMASK)
    - Pages that were already in the database will suffer from virtual 
      initial access rights (0777 minus _UMASK);
    - The _UMASK value is denoted in 'octals' (numbering system based upon
      eight) - which in PHP are differentiated from plain decimals by the
      preceding zero. Beware, that you should not enclose these numbers in
      quotes (especially when defining() the _UMASK constant); else it won't
      work.
    - Also don't get disturbed by the info/ page showing the "rights" value
      from the {meta} field as decimal value (instead of octals).

   To use this you'll also need:
    - plugins/auth/auth_method_http (or _form) plugin
    - plugins/auth/userdb_* (your choice)
    - EWIKI_PROTECTED_MODE enabled

   You should not forget that the access rights implemented by this auth
   plugin don't have anything to do with the ewiki page flags. The database
   entries {flags} are independent from these and can only be set by the
   superuser (via external tools or admin/control plugins). That is why
   _DB_F_READ and _DB_F_WRITE still have precedence over all the _perm_unix
   page attributes (and thus could be used for exceptions).

   This plugin again proves that ewiki is far ahead of Hurd and coWiki  ;-)


TODO
- add strings, error messages, translations
- make a simplified variant (coWiki like)
*/


#-- settings
define("EWIKI_USERDB_SYSTEMGROUPS", "system/groups");
define("EWIKI_PERM_UNIX_UMASK", 0000);  //access rights to strip for new pages
#define("EWIKI_PERM_UNIX_SHAREHOLDERS", 1);  //group members may revoke rights
define("EWIKI_PERM_USERSET_FREE", 0);  //anybody could set the initial owner
define("EWIKI_USERDB_GROUPDELIMS", ",;:|+*\t");


#-- interface
$ewiki_plugins["auth_perm"][] = "ewiki_auth_perm_unix";
$ewiki_plugins["edit_form_append"][] = "ewiki_edit_form_append_unix_access_rights";
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_unix_access_rights";


#-- text/translations
$ewiki_t["de"]["access rights"] = "Zugriffsrechte";
$ewiki_t["de"]["owner"] = "Besitzer";
$ewiki_t["de"]["groups"] = "Gruppen";
$ewiki_t["de"]["world"] = "Welt";



#-- association of (string)$ewikiaction => (bitmask)$UnixAccessRights
$ewiki_config["perm_rights_actions"] = array(

   #-- actions
   "view" => 4,   #=read
   "edit" => 2,   #=write
   "info" => 4,   #=read
   "links" => 4,  #=read
   "search" => 1, #=executable
   "nop" => 0,    #=always yields ok

   #-- pages
   "SecretPage" => 16+8,  #=special flags (can't be set currently)

   #-- fallback for any other combination:
   "*" => 4+1,    #=read+executable
);

#-- also always allowed for ring level 1 users:
$ewiki_config["moderators_may_do"] = array(
   "edit",
   "view", "info", "links", "search",
   "control",
);


#-- implementation
function ewiki_auth_perm_unix($id, &$data, $action, $ring=NULL, $force=0) {

   global $ewiki_plugins, $ewiki_config, $ewiki_auth_user, $ewiki_author, $ewiki_ring;

   #-- eventually refetch page $data
   if (!$data || (count($data)<5)) {
      $data = ewiki_db::GET($id);
   }

   #-- pages access rights
   ($p_rights = $data["meta"]["rights"]) and isset($p_rights)
   or ($p_rights = 0777 ^ EWIKI_PERM_UNIX_UMASK);

   #-- state vars
   $effective_rights = 00;   // owner/groups/world rights ORed together
   $effective_rights |= ($p_rights >> 9);   // add high/special bits

   #-- take "world" rights also in account
   $effective_rights |= ($p_rights & 0x07);

   #-- user login (???)
   # (already done)

   #-- check for authenticated user
   if (($user = $ewiki_auth_user) || ($user = $ewiki_author)) {

      #-- if user owns the current page
      $p_owner = $data["meta"]["owner"];
      if ($p_owner==$user) {
         if ($action=="edit") {
            #-- allow to change any permissions on edit page,
            #   this may still require to load '...?id=edit/CurrentPage' by hand
            $effective_rights |= 2;
         }
         else {
            $effective_rights |= ($p_rights >> 6) & 0x07;
         }
      }

      #-- if user is in one of the mentioned groups
      if (ewiki_auth_user_in_groups_str($user, $data["meta"]["groups"])) {
         $effective_rights |= ($p_rights >> 3) & 0x07;
      }
   }

   #-- compare against required permissions for current action or/and page
   ($requ = $ewiki_config["perm_rights_actions"]["$action/$page"]) or
   ($requ = $ewiki_config["perm_rights_actions"][$page]) or
   ($requ = $ewiki_config["perm_rights_actions"][$action]) or
   ($requ = $ewiki_config["perm_rights_actions"]["*"]) or
   ($requ = 0x0);
   $goal = (($effective_rights & $requ) == $requ);

#$ps=base_convert($p_rights, 10, 2);
#echo "eff=$effective_rights, requ=$requ, p_r=$ps<br />\n";

   #-- advanced comparasions could be added here(?)
   /*
      ...
   */

   #-- ring permission levels are still here
   $goal = $goal
        || isset($ewiki_ring) && ($ewiki_ring == 0) // superuser can always do everything
        || ($ewiki_ring == 1) && (in_array($action, $ewiki_config["moderators_may_do"]));

   #-- we also take the ring level into account, if one is required
   if (isset($ring) && is_int($ring)) {
      $goal = $goal && ($ewiki_ring <= $ring);
   }

   return($goal);
}




/*
   The following function allows to change access rights via the edit/
   page 'interface' - so using the mysql client and phpserialize()
   is not required
*/

#-- print page access rights below textarea on edit page
function ewiki_edit_form_append_unix_access_rights($id, &$data, $action) {

   global $ewiki_ring;
$ewiki_ring=0;

   #-- get meta data
   $owner = htmlentities($data["meta"]["owner"]);
   $groups = htmlentities($data["meta"]["groups"]);
   ($rights = $data["meta"]["rights"]) and isset($rights)
   or ($rights = 0777 ^ EWIKI_PERM_UNIX_UMASK);

   #-- build checkboxes
   $rs = array("", "", "", "", "");
   for ($n=15; $n>=0; $n--) {
      $i = (int) ($n/3);
      $checked = (($rights >> $n) & 0x1) ? ' checked="checked"' : "";
      $disabled = (($i>=3) && ($ewiki_ring>=2) || ($i>=4) && ($ewiki_ring>=1)) ? ' disabled="disabled"' : "";
      if (!($disabled && ($ewiki_ring>=2)))
      $rs[$i] .= ' <input type="checkbox" value="1" name="perm_unix_rights['.$n.']"'
          . $checked . $disabled . '>';
   }
   if ($rs[4]) { $rs[4] = "aaa ".$rs[4]; }
   if ($rs[3]) { $rs[3] = "mmm ".$rs[3]; }

   #-- output
   $o = ewiki_t(<<<END
<table border="0" class="access-rights">
<tr><th align="left">_{access rights}&nbsp;&nbsp; <br />
         {$rs[4]} </th>
    <td><b>_{owner}</b> <input type="text" name="perm_unix_owner" size="10" value="{$owner}"></td>
    <td><b>_{groups}</b> <input type="text" name="perm_unix_groups" size="12" value="{$groups}"></td>
    <td><b>_{world}</b> </td> </tr>
<tr><td> {$rs[3]} </td>
    <td> rwx {$rs[2]} </td>
    <td> rwx {$rs[1]} </td>
    <td> rwx {$rs[0]} </td>
</tr></table>
END
   );
   return($o);
}



/*
   This part stores the changes from the edit/ screen back into the
   database, thereby sanitizing the input and rejecting invalid changes.
   Only the "owner" has permission to change _all_ settings, but members of
   one of the mentioned groups can also change (revoke) access rights for
   "groups" and "world".
*/
#-- adds changed page access rights into {meta} - to get stored into database
function ewiki_edit_save_unix_access_rights(&$save, &$old_data) {

   global $ewiki_auth_user, $ewiki_author, $ewiki_ring, $ewiki_errmsg;

   ($user = $ewiki_auth_user)
   or ($user = $ewiki_author)
   or ($user = "");

   #-- get current settings
   $new_owner = $p_owner = $save["meta"]["owner"];
   $new_groups = $p_groups = $save["meta"]["groups"];
   ($p_rights = $save["meta"]["rights"]) or ($p_rights = 0777 ^ EWIKI_UNIX_PERM_UMAKS);
   $new_rights = $p_rights;

   #-- fetch entered settings
   if (true) {

      #-- access rights
      $set_rights = 0;
      foreach ($_POST["perm_unix_rights"] as $n=>$is) {
         if ($is) {
            $set_rights = $set_rights | (1<<$n);
         }
      }

      #-- attempt to set new page owner?
      $set_owner = trim($_REQUEST["perm_unix_owner"]);
      if (!preg_match("/^[".EWIKI_CHARS."]+$/", $set_owner) && ewiki_auth_user_exists($set_owner)) {
         $save = array();
         $ewiki_errmsg = "broken username";
         return;
      }

      #-- clean up list of group names
      $set_groups = array();
      $uu = strtr($_REQUEST["perm_unix_groups"], EWIKI_USERDB_GROUPDELIMS, "        ");
      foreach (explode(" ", $uu) as $grp) {
         if (strlen($grp) && preg_match("/^[".EWIKI_CHARS."]+$/", $grp)  /*&& group_exists()*/  ) {
            $set_groups[] = $grp;
         }
      }
      $set_groups = implode(", ", $set_groups);
   }

   #-- check if changing settings is allowed
   $bad = 0;
   if (!$user || ($user!=$p_owner)) {
      $bad = $bad || ($p_owner != $set_owner) && !(
         (empty($p_owner) && EWIKI_PERM_USERSET_FREE)
         || ($set_owner == $user)
      );
#     $bad = $bad || ($p_groups != $set_groups);   # cannot reliably check this
      $bad = $bad || ($p_rights != $set_rights);
   }
   if ($ewiki_ring == 0) {
      $bad = 0;
   }
#$bad=0;

   #-- login_query, if something was changed
   if (($bad)) {
      $uu = 0;
      ewiki_auth($uu, $uu, $uu, $uu, 2);
      $save = array();
      $ewiki_errmsg = "You cannot change these settings without being logged in.";
      return;
   }
#echo "pr=$p_rights,sr=$set_rights<br />\n";
#echo "po=$p_owner,so=$set_owner<br />\n";
#echo "bad=$bad<br />\n";
#die("passed");


   #-- the owner may change anything
   if (($user == $p_owner) || (0 == $ewiki_ring)) {

      #-- new page owner
      $new_owner = $set_owner;   

      #-- new access rights
      $new_rights = $set_rights;
      $new_rights |= 000400;  // owner can always edit a page

      #-- restricted settings
      if ($ewiki_ring == 0) {
         // keep all bits
      }
      elseif ($ewiki_ring == 1) {
         $new_rights = (($new_rights) & 007777) | (($p_rights) & 070000);
      }
      else {
         $new_rights = (($new_rights) & 000777) | (($p_rights) & 077000);
      }

      #-- groups
      $new_groups = $set_groups;
   }

/********
   #-- groups can...(?)
   if (!EWIKI_PERM_UNIX_SHAREHOLDERS) {
     // no they can't.
   }
   elseif (ewiki_auth_user_in_groups_str($user, $p_groups) && ($new_rights=$save["meta"]["rights"])) {
      #-- this can effectively only remove bits
      $new_rights = (077700 & $new_rights) | (000077 & $new_rights & $set_rights);
   }
*******/

   #-- store any changes into database entry
   if ($user) {
      $save["meta"]["rights"] = $new_rights;
      $save["meta"]["owner"] = $new_owner;
      $save["meta"]["groups"] = $new_groups;
   }

#print_r($save);die();
}




//@FIXME: does not work with all ["auth_userdb"] plugins
//(no workaround, but safe to use with system/UserRegistry or system/passwd)
function ewiki_auth_user_exists($user) {
   global $ewiki_plugins;
   if ($pf_u = $ewiki_plugins["auth_userdb"])
   foreach ($pf_u as $pf) {
      if ($pf($user, '$0$')) {
         return(true);
      }
   }
   return(false);
}




#-- fetch groups for user
function ewiki_auth_get_users_groups($user) {

   #-- get "system/groups" page
   $gp = ewiki_db::GET(EWIKI_USERDB_SYSTEMGROUPS);
   if (($gp["version"]) && !($gp["flags"] & EWIKI_DB_F_SYSTEM)) {
      $gp["flags"] |= EWIKI_DB_F_SYSTEM;
      $gp["version"]++;
      ewiki_db::WRITE($gp);   // secure it as _SYSTEM page
   }

   $user_groups = array();
   if (empty($user)) { return($user_groups); }

   #-- go through all the lines in the groups file
   if ($list = explode("\n", $gp["content"]))
   foreach ($list as $line) {

      #--- quick initial check for string occourence
      if (strpos($line, $user)) {

         #-- break line into parts
         $line = trim($line);
         if ($line[0] == "#") { 
            continue;
         }
         if (!($group = strtok($line, ":"))) { 
            continue;
         }
         $uu = strtok(":");  #-- this removes the "*:" we don't care
         $line = strtok("\000");
         if (strlen($uu) > 1) { $line .= " $uu"; }

         #-- convert delimeters into spaces and lookup $user string
         $line = strtr($line, EWIKI_USERDB_GROUPDELIMS, "        ");
         if (strpos(" {$line} ", " {$user} ")) {
            $user_groups[] = $group;
         }
      }
   }

   return($user_groups);
}



/* 
   Retrieves the list of groups the $user belongs to, and compares against
   the list in the $groups list string.
*/
function ewiki_auth_user_in_groups_str($user, $groups="") {
   $p_groups = explode(" ", strtr($groups, EWIKI_USERDB_GROUPDELIMS, "        "));
   $user_groups = ewiki_auth_get_users_groups($user);
   foreach ($user_groups as $ugrp) {
      if (strlen($ugrp) && in_array($ugrp, $p_groups)) {
         return(true);
         break;
      }
   }
   return(false);
}



?>