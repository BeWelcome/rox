<?php
/*
   XUP/XProfile parser and verification class
   ------------------------------------------
   
   $xuser = new xprofile("http://example.org/profile.xml");
   ...
   if ($xuser->login($password)) {
      ...
      echo $xuser->info["nickname"];
   }

   Should work with the older PEAR and the HTTP request class (PD) from
   upgradephp.
*/

#-- libs
if (!class_exists("http_request")) {
  include_once("http.php");          # from upgradephp/contrib/
  //include_once("http/request.php");  # from PEAR
}

#-- config
//define("XPROFILE_SITE", "example.com");   // preferred identifier of website
define("XPROFILE_SITE", preg_replace("/^www\./","",$_SERVER["SERVER_NAME"]));


#-- retrieves and verifies xml user profile
class xprofile {

   #-- constructor takes URL of profile or "user@domain" shortcut
   function xprofile($url, $xml=NULL, $uu0=0, $_redir=1) {
   
      #-- shortcut id
      if (strpos($url, "@") && !strpos($url, "/")) {
         $un = strtok($url, "@");
         $url = "http://" . strtok("@") . "/xprofile/" . $un;
      }
   
      #-- keep id
      $this->url = $url;

      #-- fetch data
      if (empty($xml) && !strncmp($url, "http://", 7)) {
         list($xml, $head) = $this->http("GET", $url);
         if (!$xml) { return; }
      }
      
      #-- parse it
      $p = new xprofile_parser($xml);
      $p->parse();
      if (isset($p->control) || isset($p->info)) {
         $this->control = (array)$p->control;
         $this->info = (array)$p->info;
         $this->text = (array)$p->text;   // already lcased
      }
      #-- "real" URL found?
      if ($this->url != $this->control["self"]) {
         $this->url = $this->control["self"];
         if ($_redir) { 
            $this->xprofile($this->url, NULL, 0, 0);
         }
      }
   }

   #-- verifies a user as owner of profile instance
   function login($password) {
      if ($login = $this->control["login"]) {
         $params = array(
            "url" => $this->url,
            "password" => $password,
            "site" => XPROFILE_SITE,
         );
         list($result, $headers) = $this->http("POST", $login, $params);
           // should come without appended \r or \n (as per spec, but hey)
         return(rtrim($result) === "1");
      }
   }

   #-- HTTP requests
   function http($method, $url, $formdata=array()) {
      if (class_exists("http_request")) {
         $req = new http_request();
         $req->setMethod($method);
         $req->setURL($url);
         $req->addHeader("Accept", "xml/user-profile, text/boolean, */*; q=0.1");
         if ($formdata) {
            $req->params += $formdata;
            $req->_postData = $formdata;  // PEAR tries to annoy us
         }
         $r = $req->sendRequest();
         if ($r && ($r->getResponseStatus() == 200)) {
            return array($r->getResponseBody(), $r->getResponseHeader());
         }
      }
      elseif ($method=="GET") {
         return array(file_get_contents($url), array());
      }
      elseif ($method=="POST") {
         // give up
      }
      return array(NULL, NULL);
   }
}


#-- xup parsing
class xprofile_parser extends easy_xml {

   function xprofile_parser($xml) {
      #-- prepare
      $this->easy_xml();
      $this->xmlns["urn:mime:xml/user-profile"] = "xup";
      $this->xmlns2["xup"] = "";
      #-- go
      $this->parse($xml);
      return($this);
   }

   #-- XML tags
   function start($xp, &$tag, &$attr) {
      parent::start($xp, $tag, $attr);
      $p = $this->parent;
      unset($this->text_meta);
      if ($tag == "link") {
         $this->{$p}[strtolower($attr["rel"])] = $attr["href"];
      }
      elseif ($tag == "meta") {
         $name = strtolower($attr["name"]);
         if (isset($attr["content"])) {
            $this->{$p}[$name] = $attr["content"];
         }
         elseif ($this->parent == "text") {
            $this->text_meta = $name;
         }
      }
   }
   #-- text:meta content
   function cdata($xp, $text) {
      if (isset($this->text_meta)) {
         $this->text[$this->text_meta] = $text;
         unset($this->text_meta);
      }
   }
}


?>