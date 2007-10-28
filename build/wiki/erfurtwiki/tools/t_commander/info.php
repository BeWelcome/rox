<?php
  include("t_config.php");
?>
<html>
<head>
 <title>WikiCommander:SplashScreen</title>
 <link rel="stylesheet" type="text/css" href="80x25.css">
</head>
<body bgcolor="#0000c0" text="#dddddd" class="SplashScreen Panel"><div style="height:100%">
<?php
  $ver = EWIKI_VERSION;
  $php = PHP_VERSION;
  $os = PHP_OS;
  $web = strtok(trim($_SERVER[SERVER_SOFTWARE]), " \r\n\t\f");
  $db = $ewiki_plugins["database"][0];
  $db = substr($db, strrpos($db, "_"));
  echo<<<END
this is the<br>
<b>WikiCommander&trade; 1.0</b><br>
<br>
operating sys: $os<br>
WebServer: $web<br>
environment: $_SERVER[GATEWAY_INTERFACE], $_SERVER[SERVER_PROTOCOL]<br>
PHP interpreter: $php<br>
ewiki version: <b>$ver</b><br>
database type: $db<br>
<br>
This tool accesses your database directly, all actions are <i>irreversible</i>.
<br>
<br>
<div style="font-family:sans-serif;font-size:60%;background:#555555;">
This utility allows for raw database editing. You can select pages in the right
pane and then edit page contents, flags or misc. settings in the left.
<br>
Following database flags are used currently:
<table border="0" cellspacing="0">
<tr><td>T</td><td>txt</td><td>_TEXT</td><td>type of ordinary wikipages</td></tr>
<tr><td>B</td><td>bin</td><td>_BINARY</td><td>non-text entries</td></tr>
<tr><td>Z</td><td>sys</td><td>_SYSTEM</td><td>special/secret data and control pages</td></tr>
<tr><td>d</td><td>off</td><td>_DISABLED</td><td>inaccesible, forbidden entries</td></tr>
<tr><td>P</td><td>prt</td><td>_PART</td><td>incomplete data fragments</td></tr>
<tr><td>r</td><td>ro</td><td>_READONLY</td><td>edit-locked</td></tr>
<tr><td>w</td><td>rw</td><td>_WRITEABLE</td><td>always unlocked pages</td></tr>
<tr><td>x</td><td>exe</td><td>_EXEC</td><td>db entry with script code</td></tr>
<tr><td>a</td><td>app</td><td>_APPENDONLY</td><td>can only add text at bottom</td></tr>
<tr><td>m</td><td>min</td><td>_MINOR</td><td>minor edits don't appear on RC</td></tr>
<tr><td>h</td><td>hid</td><td>_HIDDEN</td><td>pages don't appear in listings, searches</td></tr>
<tr><td>v</td><td>arv</td><td>_ARCHIVE</td><td>automatic page deletion (cron) won't do</td></tr>
<tr><td>H</td><td>htm</td><td>_HTML</td><td>raw html allowed in page text</td></tr>
</table>
<br>
Unless you increase the {version} number by hand, saving edited pages will
overwrite the same revision. You can simply duplicate a page under another
name by editing the {id} field there - the original page+version will remain
unchanged. Use [refr] to load a different version. The [del] button always
deletes only the shown version. The "..." brings you to a complete version
history of the current database entry. The checkbox behind {lastmodified}
will correctly update it using the system time() when saving.
<br>
<br>
You also can use typical *nix calls in the commandline (just try
"help" for a complete list).<br>
<pre>  rm PageName
  ls -l
  mv OldName "New Name"
  exit
</pre>
<br>
It is all very rough still and both menus heavily depend on enabled JS. You
can select multiple files (for deletion e.g.) if you hold the Ctrl key down,
but you must take care to click beside the filename/link, because Mozilla and
Co. would otherwise always open a new tab for it.
<br><br>
</div>
<br>
END;
?>
</div></body>
</html>
