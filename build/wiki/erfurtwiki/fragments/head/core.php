<meta name="wiki.base" content="<?php echo ewiki_script_url(); ?>" />
<meta name="wiki.moniker" content="<?php echo EWIKI_NAME; ?>" />
<?php
/*
   adds a few html <head> meta tags, if appropriate:
   - NOINDEX for old revisions   
*/

if (isset($_REQUEST["version"])
or ($ewiki_data["lastmodified"]+12*60*60 > time())) {
   echo '<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOCOUNT,NOARCHIVE" />'."\n";
}
elseif (isset($ewiki_id) && empty($ewiki_data["version"])) {
   echo '<meta name="ROBOTS" content="NOINDEX,NOARCHIVE" />'."\n";
}

?>