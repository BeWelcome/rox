<?php

/*
   Allows to download a tarball including all WikiPages and images that
   currently are in the database.
*/


#-- text
$ewiki_t["en"]["WIKIDUMP"] = "Here you can tailor your WikiDump to your needs.  <br /> When you are ready, click the \"_{DOWNLOAD_ARCHIVE}\" button.";
$ewiki_t["en"]["DOWNLOAD_ARCHIVE"] = "Download WikiDump";

define("EWIKI_WIKIDUMP_ARCNAME", "WikiDump_");
define("EWIKI_WIKIDUMP_DEFAULTTYPE", "TAR");
define("EWIKI_WIKIDUMP_MAXLEVEL", 1);
define('EWIKI_DUMP_FILENAME_REGEX',"/\W/");

#-- glue
if((function_exists(gzcompress) && EWIKI_WIKIDUMP_DEFAULTTYPE=="ZIP") || EWIKI_WIKIDUMP_DEFAULTTYPE=="TAR"){
  $ewiki_plugins["page"]["WikiDump"] = "ewiki_page_wiki_dump_tarball";
  $ewiki_plugins["action"]['wikidump'] = "ewiki_page_wiki_dump_tarball";
}

$ewiki_t["c"]["EWIKIDUMPCSS"] = '
  <style  TYPE="text/css">
  <!--
  body {
    background-color:#eeeeff;
    padding:2px;
  }	
  
  H2 {
    background:#000000;
    color:#ffffff;
    border:1px solid #000000;
  }
  -->
  </style>
  ';  
  

function ewiki_page_wiki_dump_tarball($id=0, $data=0, $action=0) {

   #-- return legacy page
   if (empty($_REQUEST["download_tarball"])) {
    if($action=="wikidump"){
      $url = ewiki_script("", "WikiDump");
      return(ewiki_make_title($id, $id, 2) . ewiki_t(<<<END
_{WIKIDUMP}
<br /><br />
<form action="$url" method="POST" enctype="multipart/form-data">
<input type="hidden" name="dump_id" value="$id">
<input type="hidden" name="dump_depth" value=1>
<input type="submit" name="download_tarball" value= "_{DOWNLOAD_ARCHIVE}">
<br /><br />
<input type="checkbox" name="dump_images" value="1" checked> _{with images}<br />
<input type="hidden" name="dump_fullhtml" value="1">
<input type="hidden" name="dump_virtual" value="0"><br />
Archive Format:
<select NAME="dump_arctype">
  <option VALUE="ZIP">ZIP
  <option VALUE="TAR">TAR
</select>

</form>
END
      ));
      } else {
        return "";
      }
   }
   #-- tarball generation
   else {
      $di = $_REQUEST["dump_images"];
      $fh = $_REQUEST["dump_fullhtml"];
      $vp = $_REQUEST["dump_virtual"];
      $rp = $_REQUEST["dump_id"];
      
      #-- $_REQUEST["dump_depth"]==100 will give a complete dump
      if(($_REQUEST["dump_depth"]>EWIKI_WIKIDUMP_MAXLEVEL) && ($_REQUEST["dump_depth"]!=100)){
        $dd=EWIKI_WIKIDUMP_MAXLEVEL;
      } else {
        $dd = $_REQUEST["dump_depth"];
      }
      $at = $_REQUEST["dump_arctype"];
      $al = 9;#$_REQUEST["dump_arclevel"];
      $_REQUEST = $_GET = $_POST = array();
      if(!ewiki_auth($rp, $str_null, "view")){
        return ewiki_make_title($id, $id, 2)."<p>You either do not have permission to access the page $rp or it does not exist.</p>";
      }
      ewiki_page_wiki_dump_send($di, $fh, $vp, $rp, $dd, $at, $al);
   }
}


function ewiki_page_wiki_dump_send($imgs=1, $fullhtml=0, $virtual=0, $rootid, $depth=1, $arctype=EWIKI_WIKIDUMP_DEFAULTTYPE, $complevel=1) {

  global $ewiki_config, $ewiki_plugins;
  
  #-- disable protected email
  foreach($ewiki_plugins["link_url"] as $key => $linkplugin){
    if($linkplugin == "ewiki_email_protect_link"){
      unset($ewiki_plugins["link_url"][$key]);
    }
  }

  #-- set uservars
  $a_uservars = ewiki_get_uservar("WikiDump", array());
  if(!is_array($a_uservars)){
    $a_uservars = unserialize($a_uservars);
  }
  $a_uservars[$rootid] = $depth;
  ewiki_set_uservar("WikiDump", $a_uservars);
  
  #-- if $fullhtml
  $HTML_TEMPLATE = '<html>
    <head>'.ewiki_t("EWIKIDUMPCSS").'
    <title>$title</title>
    </head>
    <body bgcolor="#ffffff";>
    <div id="PageText">
    <h2>$title</h2>
    $content
    </div>
    </body>
    </html>
    ';
  
  #-- reconfigure ewiki_format() to generate offline pages and files
  $html_ext = ".htm";
  if ($fullhtml) {
    $html_ext = ".html";
  }
  $ewiki_config["script"] = "%s$html_ext";
  $ewiki_config["script_binary"] = "%s";
  
  #-- fetch also dynamic pages
  $a_virtual = array_keys($ewiki_plugins["page"]);
  

  #-- get all pages / binary files
  $a_validpages = ewiki_valid_pages(1, $virtual);
  $a_pagelist = ewiki_sitemap_create($rootid, $a_validpages, $depth, 1);

  foreach($a_pagelist as $key => $value){
    if(is_array($a_validpages[$value]["refs"])){
      foreach($a_validpages[$value]["refs"] as $refs){
        if($a_validpages[$refs]["type"]=="image"){
          $a_pagelist[]=$refs;
        }
      }
    }
  }
  
  foreach($a_pagelist as $key => $value){
    if($a_validpages[$value]["type"]=="image"){
      $a_images[]=urlencode($value);
      $a_rimages[]=urlencode(preg_replace(EWIKI_DUMP_FILENAME_REGEX, "", $value));
      unset($a_validpages[$value]);
    }
  }

  $a_sitemap = ewiki_sitemap_create($rootid, $a_validpages, $depth, 0);

  if ($a_pagelist) {
    #-- create new zip file
    if($arctype == "ZIP"){
      $archivename=EWIKI_WIKIDUMP_ARCNAME."$rootid.zip";
      $archive = new ewiki_virtual_zip();
    } elseif ($arctype == "TAR") {
      $archivename=EWIKI_WIKIDUMP_ARCNAME."$rootid.tar";
      $archive = new ewiki_virtual_tarball();
    } else {
      die();
    }
    
    $a_pagelist = array_unique($a_pagelist);
    
    #-- convert all pages
    foreach($a_pagelist as $pagename){
      if ((!in_array($pagename, $a_virtual))) {
        $id = $pagename;
        #-- not a virtual page
        $row = ewiki_db::GET($pagename);
        $content = "";
      } elseif($virtual) {
        $id = $pagename;
        #-- is a virtual page
        $pf = $ewiki_plugins["page"][$id];
        $content = $pf($id, $content, "view");
        if ($fullhtml) {
          $content = str_replace('$content', $content, str_replace('$title', $id, $HTML_TEMPLATE));
        }
        $fn = urlencode($id);
        $fn = preg_replace(EWIKI_DUMP_FILENAME_REGEX, "", $fn);
        $fn = $fn.$html_ext;
      } else {
        continue;
      }
    
      if (empty($content)){
        switch ($row["flags"] & EWIKI_DB_F_TYPE) {
          case (EWIKI_DB_F_TEXT):
            $content = ewiki_format($row["content"]);
            $content = str_replace($a_images, $a_rimages, $content);
            $fn = preg_replace(EWIKI_DUMP_FILENAME_REGEX, "",  urlencode($id));
            $fn = $fn.$html_ext;
            if ($fullhtml) {
              $content =  str_replace('$content', $content, str_replace('$title', $id, $HTML_TEMPLATE));
            }
            break;
          
          case (EWIKI_DB_F_BINARY):
            if (($row["meta"]["class"]=="image") && ($imgs)) {
              $fn = urlencode(preg_replace(EWIKI_DUMP_FILENAME_REGEX, "", $id));
              $content = &$row["content"];
            }
            else {
              #-- php considers switch statements as loops so continue 2 is needed to 
              #-- hit the end of the for loop 
              continue(2);
            }
            break;
          
          default:
            # don't want it
            continue(2);
        }
      }
  
      $content=preg_replace_callback(
        '/(<a href=")(.*?)(\.html">)/',
        create_function(
        // single quotes are essential here,
        // or alternative escape all $ as \$
        '$matches',
        'return($matches[1].preg_replace(EWIKI_DUMP_FILENAME_REGEX,"",$matches[2]).$matches[3]);'
        ),
        $content
        );

      #-- add file
      $archive->add($content, $fn, array(
        "mtime" => $row["lastmodified"],
        "uname" => "ewiki",
        "mode" => 0664 | (($row["flags"]&EWIKI_DB_F_WRITEABLE)?0002:0000),
        ), $complevel);
    }
    
    #-- create index page
    $timer=array();
    $level=-1;
    $fordump=1;
    $str_formatted="<ul>\n<li><a href=\"".$rootid.$html_ext."\">".$rootid."</a></li>";
    $fin_level=format_sitemap($a_sitemap, $rootid, $str_formatted, $level, $timer, $fordump);
    $str_formatted.="</ul>".str_pad("", $fin_level*6, "</ul>\n");
    $str_formatted=preg_replace_callback(
        '/(<a href=")(.*?)(\.html">)/',
        create_function(
           // single quotes are essential here,
           // or alternative escape all $ as \$
           '$matches',
           'return($matches[1].preg_replace(EWIKI_DUMP_FILENAME_REGEX,"",$matches[2]).$matches[3]);'
        ),
        $str_formatted
      );
  
    #-- add index page
    $archive->add($str_formatted, "Index_$rootid".$html_ext, array(
      "mtime" => $row["lastmodified"],
      "uname" => "ewiki",
      "mode" => 0664 | (($row["flags"]&EWIKI_DB_F_WRITEABLE)?0002:0000),
      ), $complevel);
         
    #-- Headers
    Header("Content-type: application/octet-stream");
    Header("Content-disposition: attachment; filename=\"$archivename\"");
    Header("Cache-control: private");
    Header("Original-Filename: $archivename");    
    Header("X-Content-Type: application/octet-stream");
    Header("Content-Location: $archivename");


    #-- end output
    echo $archive->close();
  
  }
  
  #-- fin 
  die();
}




############################################################################




#-- allows to generate a tarball from virtual files
#   (supports no directories or symlinks and other stuff)
class ewiki_virtual_tarball {

   var $f = 0;
   var $datasec = array(); 

   function close() {
      #-- fill up file
      $this->write(str_repeat("\000", 9*1024));
      $data = implode("", $this -> datasec); 
      return $data;
   }


   function write($str) {
     $this ->datasec[] = $str;
   }


   function oct($int, $len) {
      $o = "\000";
      while (--$len) {
         $o = ($int & 0x07) . $o;
         $int = $int >> 3;
      }
      return($o);
   }


   #-- add virtual file
   function add($content, $filename, $args=array(), $ignored) {
   $args = array_merge($args, array(
         "mode" => 000664,
         "mtime" => time(),
         "ctime" => time(),
         "uid" => 65534,       #-- common for user "nobody"
         "gid" => 65534,
         "uname" => "nobody",
         "gname" => "nobody",
         "type" => "0",
      ));
      $args["mode"] |= 0100000;
      $args["size"] = strlen($content);
      $checksum = "        ";
      $magic = "ustar  \000";
      $filename = substr($filename, 0, 99);

      #-- header record
      $header  = str_pad($filename, 100, "\000")            # 0x0000
               . $this->oct($args["mode"], 8)               # 0x0064
               . $this->oct($args["uid"], 8)                # 0x006C
               . $this->oct($args["gid"], 8)                # 0x0074
               . $this->oct($args["size"], 12)              # 0x007C
               . $this->oct($args["mtime"], 12)             # 0x0088
               . ($checksum)                                # 0x0094
               . ($args["type"])                            # 0x009C
               . str_repeat("\000", 100)                    # 0x009D
               . ($magic)                                   # 0x0101
               . str_pad($args["uname"], 32, "\000")        # 0x0109
               . str_pad($args["gname"], 32, "\000")        # 0x0129
               ;                                            # 0x0149
      $header = str_pad($header, 512, "\000");

      #-- calculate and add header checksum
      $cksum = 0;
      for ($n=0; $n<512; $n++) {
         $cksum += ord($header[$n]);
      }
      $header = substr($header, 0, 0x0094)
              . $this->oct($cksum, 7) . " "
              . substr($header, 0x009C);

      #-- output
      if ($fill = (512 - (strlen($content) % 512))) {
         $content .= str_repeat("\000", $fill);
      }
      $this->write($header . $content);
   }
}


class ewiki_virtual_zip 
{ 
	var $datasec = array(); 
	var $ctrl_dir = array(); 
	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00"; 
	var $old_offset = 0; 
	
	function add($data, $name, $ignored, $complevel) { 
		$name = str_replace("\\", "/", $name); 
		$unc_len = strlen($data); 
		$crc = crc32($data); 
		$zdata = gzcompress($data, $complevel); 
		$zdata = substr ($zdata, 2, -4); 
		$c_len = strlen($zdata); 
		$fr = "\x50\x4b\x03\x04"; 
		$fr .= "\x14\x00"; 
		$fr .= "\x00\x00"; 
		$fr .= "\x08\x00"; 
		$fr .= "\x00\x00\x00\x00"; 
		$fr .= pack("V",$crc); 
		$fr .= pack("V",$c_len); 
		$fr .= pack("V",$unc_len); 
		$fr .= pack("v", strlen($name) ); 
		$fr .= pack("v", 0 ); 
		$fr .= $name; 
		$fr .= $zdata; 
		$fr .= pack("V",$crc); 
		$fr .= pack("V",$c_len); 
		$fr .= pack("V",$unc_len); 
		
		$this -> datasec[] = $fr; 
		$new_offset = strlen(implode("", $this->datasec)); 
		
		$cdrec = "\x50\x4b\x01\x02"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x14\x00"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x08\x00"; 
		$cdrec .="\x00\x00\x00\x00"; 
		$cdrec .= pack("V",$crc); 
		$cdrec .= pack("V",$c_len); 
		$cdrec .= pack("V",$unc_len); 
		$cdrec .= pack("v", strlen($name) ); 
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("V", 32 ); 
		$cdrec .= pack("V", $this -> old_offset ); 
		
		$this -> old_offset = $new_offset; 
		
		$cdrec .= $name; 
		$this -> ctrl_dir[] = $cdrec; 
	} 
	
	function close() { 
		$data = implode("", $this -> datasec); 
		$ctrldir = implode("", $this -> ctrl_dir); 
		
		return 
			$data . 
			$ctrldir . 
			$this -> eof_ctrl_dir . 
			pack("v", sizeof($this -> ctrl_dir)) . 
			pack("v", sizeof($this -> ctrl_dir)) . 
			pack("V", strlen($ctrldir)) . 
			pack("V", strlen($data)) . 
			"\x00\x00"; 
	} 
}


?>