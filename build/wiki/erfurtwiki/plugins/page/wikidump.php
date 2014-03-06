<?php

/*
   Allows to download a tarball including all WikiPages and images that
   currently are in the database.
*/


#-- text
$ewiki_t["en"]["WIKIDUMP"] = "Here you can download all pages of this Wiki in HTML format at once. They'll get packed into a an archive of the UNIX .tar format.";
$ewiki_t["de"]["WIKIDUMP"] = "Du kannst dir hier alle Seiten des Wikis im HTML-Format herunterladen. Das Archiv wird im UNIX .tar Format erstellt.";
$ewiki_t["de"]["download tarball"] = "tarball herunterladen";
$ewiki_t["de"]["with images"] = "mit Graphiken";
$ewiki_t["de"]["complete .html files"] = "vollst�ndige .html Dateien";
$ewiki_t["de"]["include virtual pages"] = "auch virtuelle Seiten";

#-- glue
$ewiki_plugins["page"]["WikiDump"] = "ewiki_page_wiki_dump_tarball";



#-- template (if $fullhtml)
function ewiki_dump_template($id, $content, $linksto=0, $html_ext=".html") {

   $title = $head_title = ewiki_split_title($id);
   if ($linksto != 0) {
      $title = '<a href="' . urlencode(urlencode($id)). (($linksto>0) ? '.links':''). $html_ext . '">' . $title . '</a>';
   }
   $version = EWIKI_VERSION;

   return <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 <title>$head_title</title>
 <meta name="generator" content="WikiDump, ewiki/$version">
</head>
<body bgcolor="#ffffff";>
<h2>$title</h2>
$content
</body>
</html>
EOT;
}



function ewiki_page_wiki_dump_tarball($id, $data, $action) {

   #-- return legacy page
   if (empty($_REQUEST["download_tarball"])) {
      $url = ewiki_script("", $id);
      return(ewiki_make_title($id, $id, 2) . ewiki_t(<<<END
_{WIKIDUMP}
<br /><br />
<form action="$url" method="POST" enctype="multipart/form-data">
<input type="hidden" name="id" value="$id">
<input type="submit" class="button" name="download_tarball" value="_{download tarball}">
<br /><br />
<input type="checkbox" name="dump_images" value="1"> _{with images}<br />
<input type="checkbox" name="dump_virtual" value="1"> _{include virtual pages}<br />
<input type="checkbox" name="dump_fullhtml" value="1" checked> _{complete .html files}<br />
&nbsp; &nbsp; &nbsp; <input type="checkbox" name="dump_linksto" value="1"> _{with backlink pages}<br />
</form>
END
      ));
   }
   #-- tarball generation
   else {
      $di = $_REQUEST["dump_images"];
      $fh = $_REQUEST["dump_fullhtml"];
      $bl = $_REQUEST["dump_linksto"];
      $vp = $_REQUEST["dump_virtual"];
      $_REQUEST = $_GET = $_POST = array();
      set_time_limit(180);
      ewiki_page_wiki_dump_send($di, $fh, $vp, $bl);
   }
}


function ewiki_page_wiki_dump_send($imgs=1, $fullhtml=0, $virtual=0, $linksto=0) {

   global $ewiki_config, $ewiki_plugins;

   #-- reconfigure ewiki_format() to generate offline pages and files
   $html_ext = ".htm";
   if ($fullhtml) {
      $html_ext = ".html";
   }
   $ewiki_config["script"] = "%s$html_ext";
   $ewiki_config["script_binary"] = "%s";
   $ewiki_config["print_title"] = 0;

   #-- fetch also dynamic pages
   if ($virtual) {
      $virtual = array_keys($ewiki_plugins["page"]);
   } else {
      $virtual = array();
   }


   #-- get all pages / binary files
   $result = ewiki_db::GETALL(array("id", "version", "flags"));
   if ($result) {

      #-- HTTP headers
      header("Content-Type: application/x-tar");
      header("Content-Disposition: attachment; filename=\"WikiDump.tar.gz\"");

      #-- start tar file
      $tarball = new ewiki_virtual_tarball();
      $tarball->open(0);

      #-- convert all pages
      while (($row=$result->get()) || count($virtual)) {

         $content = "";

         #-- fetch page from database
         if ($id = $row["id"]) {
            $row = ewiki_db::GET($id);
         }
         #-- virtual page plugins
         elseif ($id = array_pop($virtual)) {
            $pf = $ewiki_plugins["page"][$id];
            $content = $pf($id, $content, "view");
            $row = array(
               "flags" => EWIKI_DB_F_TEXT|EWIKI_DB_F_HTML,
               "lastmodified" => time(),
            );
         }
         else {
            break;
         }

         #-- file name
         $fn = $id;
         $fn = urlencode($fn);

         #-- post process for ordinary pages / binary data
         if (empty($content))
         switch ($row["flags"] & EWIKI_DB_F_TYPE) {

            case (EWIKI_DB_F_TEXT):
               $content = ewiki_format($row["content"]);
               break;

            case (EWIKI_DB_F_BINARY):
               if (($row["meta"]["class"]=="image") && ($imgs)) {
                  $content = &$row["content"];
               }
               else {
                  return;
               }
               break;

            default:
               # don't want it
               continue;
         }

         #-- size check
         if (empty($content)) {
            continue;
         }

         #-- for tarball
         $perms = array(
            "mtime" => $row["lastmodified"],
            "uname" => "ewiki",
            "mode" => 0664 | (($row["flags"]&EWIKI_DB_F_WRITEABLE)?0002:0000),
         );

         #-- html post process
         if (!($row["flags"] & EWIKI_DB_F_BINARY)) {

            #-- use page template
            if ($fullhtml) {
               $content = ewiki_dump_template($id, $content, $linksto);
            }

            #-- add links/ page
            if ($linksto) {
               $tarball->add(
                  "$fn.links$html_ext",
                  ewiki_dump_template(
                     $id,
                     ewiki_page_links($id, $row, "links"),
                     $lto=-1
                  ),
                  $perms
               );
            }

            $fn .= $html_ext;
         }

         #-- add file
         $tarball->add(
            $fn,
            $content,
            $perms
         );
      }

      #-- end output
      $tarball->close();

   }

   #-- fin 
   die();
}




############################################################################




#-- allows to generate a tarball from virtual files
#   (supports no directories or symlinks and other stuff)
class ewiki_virtual_tarball {

   var $f = 0;

   function open($fn="/dev/stdout") {

      #-- init;
      $this->f = 0;

      #-- file output?
      if ($fn && ($fn != "-")) {
         $this->f = gzopen("$fn", "wb9");
      }
      else {
         $_ENV["HTTP_ACCEPT_ENCODING"] = "gzip, deflate";
         $_SERVER["HTTP_ACCEPT_ENCODING"] = "gzip, deflate";
         ob_start("ob_gzhandler");
      }
   }


   function close() {

      #-- fill up file
      $this->write(str_repeat("\000", 9*1024));

      #-- close file handle
      if ($this->f) {
         gzclose($this->f);
      }
   }


   function write($str) {
      if ($this->f) {
         gzwrite($this->f, $str);
         fflush($this->f);
      }
      else {
         echo $str;
         ob_flush();
      }
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
   function add($filename, $content, $args=array()) {

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



?>