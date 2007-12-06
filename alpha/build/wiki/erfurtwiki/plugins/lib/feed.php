<?php
/*
   Outputs RSS/Atom feeds, multiple versions supported (RSS 0.91, 1.0, 2.0,
   3.0 and Atom 0.3); what clients get depends on what they asked for:
    - Accept: text/x-rss; revision=2.0
    - Accept: application/atom+xml
*/

//define("EWIKI_DESC", "ThisWiki - site slogan...");  # site description
//define("EWIKI_COPY", "PrimarilyPublicDomain");      # site copyright
//define("EWIKI_CATEGORY", "Technique");              # site subject
//define("EWIKI_LOGO_URL", "http://.../logo.png");


#---------------------------------------------------------------------------
# outputs a feed from the given array of page $data hashes
#
function ewiki_feed($pages, $type="AUTO")
{
   global $ewiki_config;
   ob_end_clean();

   #-- general feed infos
   $feed_info = array(
      "title" => EWIKI_NAME,
      "lang" => EWIKI_DEFAULT_LANG,
      "desc" => defined("EWIKI_DESC")?EWIKI_DESC : EWIKI_NAME . " - an open hypertext site",
      "copyright" => defined("EWIKI_COPY")?EWIKI_COPY : "copyrighted",
      "category" => defined("EWIKI_CATEGORY")?EWIKI_CATEGORY : "None",
//    "logo" => defined("EWIKI_LOGO_URL")?EWIKI_LOGO_URL : "http://erfurtwiki.sf.net/squirrel.jpeg",
      "url" => ewiki_script_url(),
      "rc_url" => ewiki_script_url("", "UpdatedPages"),
      "ewiki" => "ewiki/".EWIKI_VERSION,
      "charset" => EWIKI_CHARSET,
   );
   
   #-- fix/prepare feed entries
   $lm = UNIX_MILLENNIUM;
   foreach ($pages as $i=>$data) {
      if (!is_array($data)) {
         $data = ewiki_db::GET($data);
         $pages[$i] = $data;
      }
      // ...
      if ($data["id"]) {
         if (empty($data["title"])) { $pages[$i]["title"] = ewiki_split_title($data["id"], -1, 0); }
         if (empty($data["url"])) { $pages[$i]["url"] = ewiki_script_url("", $data["id"]); }
         if (empty($data["uri"])) { $pages[$i]["uri"] = "x-wiki:".EWIKI_NAME.":".ewiki_xmlentities(urlencode($data["id"])); }
         if (empty($data["guid"])) { $pages[$i]["guid"] = ewiki_script_url("", $data["id"], "version=$data[version]"); }
      }
      $pages[$i]["content"] = strtr(ewiki_xmlentities(substr($data["content"], 0, 300)), "\r\n\t\f", "    ");
      $pages[$i]["pdate"] = gmstrftime("%a, %d %b %G %T %Z", $data["lastmodified"]);
      $pages[$i]["idate"] = gmstrftime("%G%m%dT%TZ", $data["lastmodified"]);
      $pages[$i]["icdate"] = gmstrftime("%G%m%dT%TZ", $data["created"]);
      if ($lm < $data["lastmodified"]) {
         $lm = $data["lastmodified"];
      }
      $data["content"] = "";
   }
   $info["modified"] = $lm;

   #-- respect some common parameters
   if (($limit = $_REQUEST["limit"])
   or ($limit = $_REQUEST["items"])
   or ($limit = $ewiki_config["list_limit_rss"])
   or ($limit = $ewiki_config["list_limit"])) {
      $pages = array_slice($pages, 0, $limit);
   }
   
   #-- encode everything into UTF-8?
   // no

   #-- engage compression
   if ($_SERVER["HTTP_ACCEPT_ENCODING"]) {
      ob_start("ob_gzhandler");
      ob_implicit_flush(0);
   }

   #-- what to return
   if (!is_string($type) || (strtoupper($type)=="AUTO") || ($type=="*")) {
      $type = ewiki_feed_type();
   }
   header("Vary: accept,negotiate");
   header("TCN: choice");
   switch ($type) {
      case "RSS0":
         ewiki_feed_rss0($feed_info, $pages);
      case "RSS2":
         ewiki_feed_rss2($feed_info, $pages);
      case "RSS3":
         ewiki_feed_rss3($feed_info, $pages);
      case "RSS1":
         ewiki_feed_rss1($feed_info, $pages);
      case "ATOM":
         ewiki_feed_atom($feed_info, $pages);
      case "DUMB":
      default:
         header("Status: 406 Not Acceptable");
         header("Content-Type: text/plain");
         die("You are using a pretty dumb feed reader, it didn't\n".
             "send any appropriate Accept: header. Go away.");
   }
   die();
}



#---------------------------------------------------------------------------
# returns Netscape RSS 0.91 (other versions are neglectable)
#
function ewiki_feed_rss0($info, $pages)
{
#   header('Content-Type: text/x-rss');
   header('Content-Type: application/rss+xml; revision="0.91"');

   $pages = array_slice($pages, 0, 15);
   $name = $info["title"];

echo<<<EOT
<?xml version="1.0" encoding="$info[charset]"?>
<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://my.netscape.com/publish/formats/rss-0.91.dtd">
<rss version="0.91">
 <channel>
    <title>$info[title]</title>
    <language>$info[lang]</language>
    <description>$info[desc]</description>
    <link>$info[url]</link>  \n
EOT;

   #-- items
   foreach ($pages as $data) {
echo<<<EOT
   <item>
     <title>$data[title]</title>
     <link>$data[url]</link>
     <description>$data[content]</description>
     <pubDate>$data[pdate]</pubDate>
   </item>\n
EOT;
   } //<?

echo " </channel>\n</rss>\n";
   die();
}



#---------------------------------------------------------------------------
# writes RSS 2.0
#
function ewiki_feed_rss2($info, $pages)
{
   header('Content-Type: application/rss+xml; revision="2.0"');

   $name = $info["title"];

echo<<<EOT
<?xml version="1.0" encoding="$info[charset]"?>
<rss version="2.0">
 <channel>
   <title>$info[title]</title>
   <link>$info[url]</link>  
   <language>$info[lang]</language>
   <description>$info[desc]</description>
   <generator>$info[ewiki]</generator>
   <webMaster>$_SERVER[SERVER_ADMIN]</webMaster>\n
EOT;

   #-- items
   foreach ($pages as $data) {
echo<<<EOT
   <item>
     <title>$data[title]</title>
     <link>$data[url]</link>
     <description>$data[content]</description>
     <pubDate>$data[pdate]</pubDate>
     <guid>$data[guid]</guid>
   </item>\n
EOT;
   } //<?

echo " </channel>\n</rss>\n";
   die();
}



#---------------------------------------------------------------------------
# outputs RSS 3.0 (text/plain, much like 822)
#
function ewiki_feed_rss3($info, $pages)
{
   header('Content-Type: text/plain; charset="'.$info["charset"].'"');

   $name = $info["title"];

echo<<<EOT
title: $info[title]
link: $info[url]
language: $info[lang]
description: $info[desc]
generator: $info[ewiki]
webMaster: $_SERVER[SERVER_ADMIN]
rights: $info[copyright]
\n
EOT;

   #-- items
   foreach ($pages as $data) {
echo<<<EOT
title: $data[title]
link: $data[url]
guid: $data[guid]
uri: $data[uri]
description: $data[content]
created: $data[icdate]
last-modified: $data[idate]
\n
EOT;
   }

   echo "\n";
   die();
}



#---------------------------------------------------------------------------
# returns RDF/RSS1.0 (no real RSS, obsoleted by Atom)
#
function ewiki_feed_rss1($info, $pages)
{
   header('Content-Type: application/rss+xml; revision="1.0"');
   
   $name = $info["title"];
   $urnpfix = "x-wiki";  // uniform resource name prefix ("urn:x-wiki" was ok)

   #-- parts
   $_logo = $info["logo"] ? "<image rdf:resource=\"$urnpfix:$name:logo#1\" />\n" : "";

echo<<<EOT
<?xml version="1.0" encoding="$info[charset]"?>
<rdf:RDF
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:RSS="http://purl.org/rss/1.0/"
xmlns:wiki="http://purl.org/rss/1.0/modules/wiki/">
  <RSS:channel rdf:about="$info[url]">
    <RSS:title>$name</RSS:title>
    <dc:title>$name</dc:title>
    <RSS:link>$info[rc_url]</RSS:link>
    <RSS:description>UpdatedPages on $name</RSS:description>
    <wiki:interwiki>$name</wiki:interwiki>
    $_logo
    <RSS:items>
      <rdf:Seq>\n
EOT;
   #-- output Seq ids
   foreach ($pages as $i=>$data) {
      $pages[$i]["rdf_id"] = "$urnpfix:$name:".ewiki_xmlentities(urlencode($data["id"]))."#$data[version]_$data[lastmodified]";
      echo '        <rdf:li rdf:resource="'.$pages[$i]["rdf_id"].'"/>' . "\n";
   } //<?
echo<<<EOT
      </rdf:Seq>
    </RSS:items>
  </RSS:channel>
<!-- RDF associated data for references from above -->\n
EOT;
   #-- logo
   if ($_logo) {
echo<<<EOT
  <RSS:image rdf:about="$urnpfix:$name:logo#1">
    <RSS:title>$name</RSS:title>
    <RSS:link>$url</RSS:link>
    <RSS:url>$info[logo]</RSS:url>
  </RSS:image>\n
EOT;
   } //<?

   #-- write items
   foreach ($pages as $i=>$data) {
     preg_match('/^([^\s]+).+\(.*([\d.]+)\)/', $data["author"], $uu);
     $author_host = $uu[2];
     $author_name = $uu[1];
     $url_diff = ewiki_script_url("diff", $data["id"]);
     $url_info = ewiki_script_url("info", $data["id"]);
     $stat = ($data["version"]==1) ? "created" : "updated";
echo<<<EOT
  <RSS:item rdf:about="$data[rdf_id]">
    <RSS:title>$data[title]</RSS:title>
    <dc:title>$data[title]</dc:title>
    <RSS:description>$data[content]</RSS:description>
    <RSS:link>$data[url]</RSS:link>
    <wiki:diff>$url_diff</wiki:diff>
    <wiki:history>$url_info</wiki:history>
    <wiki:version>$data[version]</wiki:version>
    <wiki:status>$stat</wiki:status>
    <dc:date>$data[idate]</dc:date>
    <dc:contributor>
      <rdf:Description wiki:host="$author_host">
        <rdf:value>$author_name</rdf:value>
      </rdf:Description>
    </dc:contributor>
  </RSS:item>\n
EOT;
   } //<?

   echo "</rdf:RDF>\n";
   die();
}



#---------------------------------------------------------------------------
# returns ATOM 0.3 feed
#
function ewiki_feed_atom($info, $pages)
{
   header('Content-Type: application/atom+xml');
   $name = $info["title"];
   $ilm = gmstrftime("%G%m%dT%TZ", $info["modified"]);
   
echo<<<EOT
<?xml version="1.0" encoding="$info[charset]"?>
<feed version="0.3" xmlns="http://purl.org/atom/ns#">
  <title>$name</title>
  <link rel="alternate" type="text/html" href="$info[url]"/>
  <modified>$ilm</modified>
  <author>*</author>
  <generator>$info[ewiki]</generator>\n
EOT;

   #-- write items
   foreach ($pages as $i=>$data) {
      $etag = ewiki_etag($data);
      echo<<<EOT
  <entry>
    <title>$data[title]</title>
    <link rel="alternate" type="text/html" href="$data[url]"/>
    <id>$etag</id>
    <issued>$data[icdate]</issued>
    <created>$data[icdate]</created>
    <modified>$data[idate]</modified>
    <content>$data[content]</content>
  </entry>\n
EOT;
   } //<?

   echo "</feed>\n";
   die();
}



#---------------------------------------------------------------------------
# checks HTTP Accept: header for guessing what's desired
# (btw, we just ignore dumb and HTTP incompliant clients)
#
function ewiki_feed_type()
{
   $regex_RSS = '#^(text|application)/(x[-.])*rss(\+xml)?$|^\*/\*$#';
   $regex_RDF = '#^(text|application)/(x[-.])*(rss[-.+])?rdf([-.]rss)?(\+xml)?$#';
   $regex_ATOM= '#^(text|application)/(x[-.])*atom(\+xml)*$#';
   $what = "DUMB";
   $types = ewiki_sort_accept($_SERVER["HTTP_ACCEPT"]);
   foreach ($types as $type=>$attr) {
      #-- RSS
      if (preg_match($regex_RSS, $type) || ($type == "text/xml")) {
         $ver = isset($attr["version"]) ? (int) $attr["version"] : (int) $attr["revision"];
         if ($ver < 4) {
            $WHAT = "RSS$ver";    // one of "RSS0", "RSS1", "RSS2", "RSS3"
            if ($ver != 1) {
               break;
            }
         }
      }
      #-- ATOM
      elseif (preg_match($regex_ATOM, $type)) {
         $WHAT = "ATOM";
         break;
      }
      #-- exceptions
      elseif (preg_match($regex_RDF, $type))
         { $WHAT = "RSS1"; break; }
      elseif ($type == "text/plain")
         { $WHAT = "RSS3"; break; }
      elseif ($type == "*/*")
         { $WHAT = "RSS2"; }
   }
   return($WHAT);
}



#---------------------------------------------------------------------------
# evaluates and sorts Accept: header (and alikes)
#
function ewiki_sort_accept($str) {
   $r = array();
   $attr = array();
   $def = 0.99;
   foreach (explode(",", $str) as $type) {
      $type = strtok(trim($type), ";");
      $q = ($def *= 0.99);
      if ($params = trim(strtok("\000"))) {
         foreach (explode(";", $params) as $p) {
            $pname = trim(strtok($p, "="));
            $val = trim(strtok("\000"));
            if ($pname == "q") {
               ($q = $val * 1.0) or ($q = $def);
            }
            $attr[$type][$pname] = $val;
         }
      }
      $r[$type] = $q;
   }
   arsort($r);
   foreach ($r as $t=>$uu) {
      $r[$t] = $attr[$t];
   }
   return($r);   
}


#-- PHP backwards compatibility
if (!function_exists("xmlentities")) {
  function xmlentities($str) {
     return strtr($str, array("&"=>"&amp;", "<"=>"&lt;",
          ">"=>"&gt;", "\""=>"&quot;", "'"=>"&apos;"));
  } 
}

#-- does not reescape numeric XML entities
function ewiki_xmlentities($str) {
   $xe = array("&"=>"&amp;", "<"=>"&lt;", ">"=>"&gt;", "\""=>"&quot;", "'"=>"&apos;");
   return preg_replace("/(&(?>!#x|#\d)|[<>\"'])/e", '$xe["$1"]', $str);
}


?>