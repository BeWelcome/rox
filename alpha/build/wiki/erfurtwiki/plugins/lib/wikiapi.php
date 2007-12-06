<?php

/*
   this provides the limited WikiApi used by the XML+RPC interfaces and
   also the WikiScript interpreter (sandboxed server-side JavaScript)
*/

$wikiapi = (array)@$wikiapi + array(
   "wiki.supportedVersion"	=> "wikiapi_supportedVersion",
   "wiki.getPage"		=> "wikiapi_getPage",
   "wiki.getPageVersion"	=> "wikiapi_getPage",
   "wiki.getPageHtml"		=> "wikiapi_getPageHtml",
   "wiki.getPageHtmlVersion"	=> "wikiapi_getPageHtml",
   "wiki.getPageInfo"		=> "wikiapi_getPageInfo",
   "wiki.getPageInfoVersion"	=> "wikiapi_getPageInfo",
   "wiki.getAllPages"		=> "wikiapi_getAllPages",
   "wiki.getRecentChanges"	=> "wikiapi_getRecentChanges",
   "wiki.listLinks"		=> "wikiapi_listLinks",
   "wiki.putPage"		=> "wikiapi_putPage",
   "ewiki.getBackLinks"		=> "ewiki_get_backlinks",
   "ewiki.getLinks"		=> "ewiki_get_links",
// "ewiki.linkDatabase"		=> "wikiapi_linkdatabase",
// "ewiki.searchPages"		=> "wikiapi_search",
// "ewiki.titleSearch"		=> "wikiapi_search_titles",
   "ewiki.url"			=> "ewiki_script",
   "ewiki.ahref"		=> "ewiki_link",
   "ewiki.getInterWikiUrl"	=> "ewiki_interwiki",
   "ewiki.log"			=> "ewiki_log",
);
$wikiapi_encode = 0; 


# API switch helper functions (XML+RPC needs encoded fields)
#
function wikiapi_b64encode($str) {
   global $wikiapi_encode;
   if ($wikiapi_encode) {
      return(new xmlrpc_base64(base64_encode($str)));
             // NOTE: possible Wiki XML-RPC bugs everywhere:
             // this text snippet will be in L1 charset, as <base64>
             // is for binary transportation (as opposed to text
             // <string>s)
   }
   else return($str);
}

function wikiapi_time($t) {
   global $wikiapi_encode;
   if ($wikiapi_encode) {
      return(new xmlrpc_datetime($t));
   }
   else return($str);
}


#---------------------------------------------------------------------------
# API implementation



// we're now version TWO - no more base64+urlencoding!!
function wikiapi_supportedVersion() {
   return(2);
}


function wikiapi_getAllPages() {
   return(wikiapi_getRecentChanges(NULL));
}


# helper function
#
function wikiapi_pageinfo(&$data) {
   $author = strtok($data["author"], "(,|  ");
   $res = array(
      "name"		=> $data["id"],
      "lastModified"	=> wikiapi_time($data["modified"]),
      "author"		=> $author,
      "version"		=> $data["version"],
   );
   return($res);
}

function wikiapi_getRecentChanges($since=UNIX_MILLENNIUM) {
   $r = array();
   $result = ewiki_db::GETALL(array("flags", "lastmodified"));
   while ($row = $result->get(0, 0x0020)) { 
      if (EWIKI_DB_F_TEXT == ($row["flags"] & EWIKI_DB_F_TYPE)) {
         if (!isset($since)) {
            $r[] = $row["id"];
         }
         elseif ($row["lastmodified"] >= $since) {
            $r[] = wikiapi_pageinfo($data);
         }
      }
   }
   return($r);
}


function wikiapi_getPageInfo($id, $ver=NULL) {
   $data = ewiki_db::GET($id, $ver);
   if ($data) {
      $res = wikiapi_pageinfo($data);
      return($res);
   }
}


function wikiapi_getPage($id, $ver=NULL) {
   $data = ewiki_db::GET($id, $ver);
   if ($data) {
      return($data["content"]);  #wikiapi_b64encode()
   }
}


function wikiapi_getPageHtml($id, $ver=NULL) {
   $data = ewiki_db::GET($id, $ver);
   if ($data) {
      $render_args = array(
         "scan_links" => 1,
         "html" => (EWIKI_ALLOW_HTML||(@$data["flags"]&EWIKI_DB_F_HTML)),
      );
      $res = ewiki_format($data["content"], $render_args);
      return($res);  #wikiapi_b64encode()
   }
}


function wikiapi_listLinks($id, $ver=NULL) {
   global $ewiki_config;
   $iw = &$ewiki_config["interwiki"];

   $data = ewiki_db::GET($id, $ver);
   if ($data) {
      $r = array();
      $refs = explode("\n", trim($data["refs"]));
      foreach ($refs as $link) {
         #-- interwiki
         if (($m = strtok($link, ":")) && ($p = strtok("\000"))) {
            if ($url = ewiki_array($iw, $m)) {
               while (($uu = ewiki_array($iw, $url)) !== NULL) {
                  $url = $uu;
               }
               $link = $url . $p;
            }
         }
         #-- absolute URLs
         if (strpos($link, "://")) {
            if (strpos($link, EWIKI_IDF_INTERNAL) === 0) {
               $link = ewiki_script_binary("", $link);
               # BAD NEWS: we can't get absolute URLs for
               # internal:// image links
            }
            $r[] = array(
               "page" => $link,
               "type" => "external",
               "href" => $href,
            );
         }
         #-- internal WikiLinks
         else {
            $r[] = array(
               "page" => $link,
               "type" => "local",
               "href" => ewiki_script("", $link),
            );
         }
      }
      return($r);
   }
}


function wikiapi_putPage($id, $raw) {
   if (EWIKI_PROTECTED_MODE) {
      return(false);
   }
   return(false);
}


?>