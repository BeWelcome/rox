<?php
   header("Status: 200 Intermediate Page");
   ($url = $_REQUEST["url"])
   or ($url = implode("+", $_GET))
   or ($url = $_SERVER["QUERY_STRING"])
   or ($url = "unknown-destination");
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Zero PageRank</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta name="description" content="filters suspect external URLs to prevent any Google ranking bonus if listed on a Wiki">

  <!-- the magic: -->
  <meta name="robots" content="NOINDEX, NOFOLLOW, CACHE, NOARCHIVE">

</head>
<body bgcolor="#ddddff" text="#000000" style="font:Verdana,sans-serif 19pt;">

 <h1>Zero PageRank</h1>
 
 <p>The link to <a class="spam" href="<?php echo $url;?>"><?php echo $url;?></a> was
 identified as possible <a href="http://www.usemod.com/cgi-bin/mp.pl?WikiSpam">WikiSpam</a>
 and therefore is filtered through this page, which prevents indexing by
 search engines and thus hinders spammers to achieve their (anyhow minimal)
 Google page ranking bonus.</p>

 
 <p><hr noshade="noshade"><small>this page was brought to you by the
 <a href="http://ewiki.berlios.de/">ewiki</a> (hypertext management system)
 project </small></p>
 
</body>
</html>