<?php
/*
  Creates a cache entry in the database for retrieved objects.

  @depends: http
*/


#-- retrieve URL, but always create/check cache entry
function ewiki_cache_url($url, $cache_min=1200) {
   global $ewiki_cache_ctype;
   
   #-- check if fresh enough in cache (20min)
   $data = ewiki_db::GET($url);
   if (time() <= $data["lastmodified"] + $cache_min) {
      $ewiki_cache_ctype = $data["Content-Type"];
      return($data["content"]);
   }

   #-- retrieve
   $req = new http_request("GET", $url);
   $req->header["Accept"] = "application/atom+xml, application/rss+xml, text/rss, xml/*, */*rss*";
   if ($data["meta"]["Last-Modified"]) {
      $req->headers["If-Modified-Since"] = $data["meta"]["Last-Modified"];
   }
   if ($data["meta"]["Etag"]) {
      $req->headers["If-None-Match"] = $data["meta"]["Etag"];
   }
   $result = $req->go();
   
   #-- create/overwrite cache entry
   if ($result->status == 200) {
      $data = ewiki_db::CREATE($url, 0x0000, "ewiki_cache_url");
      $data["flags"] = 0x0000;
      $data["content"] = $result->body;
      foreach ($result->headers as $i=>$v) {
         $data["meta"][$i] = $v;
      }
      $data["meta"]["class"] = "temp";
      $data["meta"]["kill-after"] = time() + $cache_min;
      if ($t = $data["meta"]["Last-Modified"]) {
   //    $data["lastmodified"] = ewiki_decode_datetime($t);
      }
      ewiki_db::WRITE($data,"_OVERWRITE=1");
   }

   $ewiki_cache_ctype = $data["Content-Type"];
   return($data["content"]);
}

?>