<?php

#-- glue
$ewiki_plugins["page"]["MiniDump"] = "ewiki_page_wiki_mini_tarball_dump";




function ewiki_page_wiki_mini_tarball_dump($id, $data, $action) {

   global $ewiki_config, $ewiki_plugins;


   #-- get all pages / binary files
   $result = ewiki_db::GETALL(array("id", "version", "flags"));
   if ($result) {

      #-- HTTP headers
      header("Content-Type: application/x-tar");
      header("Content-Disposition: attachment; filename=\"InitPages.tar.gz\"");

      #-- start tar file
      $tarball = new ewiki_virtual_tarball();
      $tarball->open(0);

      #-- convert all pages
      while ($row=$result->get(0, 0x1037)) {

         $id = $row["id"];
         $row = ewiki_db::GET($id);
         $content = &$row["content"];
         $fn = ($id);

         if (!$row || !$row["id"] || !$row["content"]) {
            continue;
         }

         #-- for tarball
         $perms = array(
            "mtime" => $row["lastmodified"],
            "uname" => "ewiki",
            "mode" => 0664 | (($row["flags"]&EWIKI_DB_F_WRITEABLE)?0002:0000),
         );

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



if (!class_exists("ewiki_virtual_tarball")) {

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

}

?>