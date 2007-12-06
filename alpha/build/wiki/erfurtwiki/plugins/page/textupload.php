<?php

/*
   This plugin allows to upload files, which then get inserted as wiki
   pages. It accepts plain text files, but also ZIP files or tarballs
   containing multiple ones (if your server can 'tar' or 'unzip'). Also
   it can 'parse' html files and gives acceptable results for them.

   It may also convert files from proprietary word processing formats, if
   you have the according progams available server-side. But only enable
   this for filters you really have, as you otherwise could end up with
   empty pages. Eventually this runs reliable on Unix systems only.

   To make it run on Win4/NT systems, you'll need to uncomment unsupported
   text filters (often all), and set _UNTAR and _UNZIP to more useful
   values. _UNZIP is also needed for reading OpenOffice files.
*/


#-- are following tools available?
define("EWIKI_UNTAR", "tar");		#-- GNU/Linux tar, Cygwin tar.exe
define("EWIKI_UNZIP", "unzip");		#-- unzip or pkunzip.exe
#-- else
@define("EWIKI_UNTAR", 0);
@define("EWIKI_UNZIP", 0);
#-- additional settings
if (DIRECTORY_SEPARATOR=="/") {
   define("EWIKI_DEV_STDOUT", "/dev/stdout");     #-- Unix
} else { 
   define("EWIKI_DEV_STDOUT", "CON");             #-- DOS
}


#-- filter table (Unix rules!)
$ewiki_textfilters = array(
   array("text/plain", "text/plain", "cat %f"),
   array("application/x-msword", "text/wiki", "wvWiki %f -"),
   array("application/x-msword", "text/html", "wvHtml %f -"),
   array("application/x-msword", "text/html", "word2x -f html %f"),
   array("application/x-msword", "text/plain", "antiword -t %f"),
   array("application/x-wordperfect", "text/html", "wpd2html %f"),
   array("application/pdf", "text/html", "pdftotext -htmlmeta -q %f"),
   array("application/x-latex", "text/html", "latex2html < %f"),
   array("x.file-ext/x.docbook", "text/html", "docbook2html --nochunks %f"),
   array("text/html", "text/html", "tidy -quiet -latin1 %f"),
   array("text/xhtml", "text/html", "tidy -quiet -latin1 %f"),
   array("application/vnd.sun.xml.writer", "text/x.office.content.xml", EWIKI_UNZIP." -p %f content.xml"),
#  array("text/xml+docbook", "text/wiki", "db2html %f -"), ????
#  array("*/*", "application/postscript", "a2ps -q"),
   array("application/postscript", "text/plain", "pstotext"),
);
$mime_ext["docbook"] = "x.file-ext/x.docbook";
$mime_ext["db"] = "x.file-ext/x.docbook";
$mime_ext["sgml"] = "x.file-ext/x.docbook";
$mime_ext["mar"] = "x-multipart/parallel";
/*
   In each line, the accepted input mime-type, and the resulting output
   type are listed. The command (third entry) must read the file "%f" as
   input (or from stdin), and send the entire output to stdout or "%o".
   "%f" and "%o" are placeholders, which are set automatically (eventually
   then read "/dev/stdin" and "/dev/stdout" with "<" or ">").

   Some of these filters are usually already available with modern UNIX
   distros.  As fallback text data gets ripped out from binary files
   (garbage will remain in the page), or the file could be rejected
   completely.
*/


#-- plugin registration
$ewiki_plugins["page"]["TextUpload"] = "ewiki_page_textupload";




/*  this prints the
    upload <form>
*/
function ewiki_page_textupload($id, $data, $action) {

   $o = ewiki_make_title($id, $id, 2);

   if (empty($_FILES["upload_text_file"])) {

      $ACCEPT="text/plain,text/wiki,text/html,text/*,application/x-tar,application/x-gtar,application/x-ustar,application/zip";

      $url = ewiki_script("", $id);
      $o .= ewiki_t(<<<END
Use this upload form to insert text files as pages into the Wiki. This
function also has super cow powers and can extract multiple files from a zip
archive or tarball (compressed or not).
<br />
<br />
<form action="$url" method="POST" enctype="multipart/form-data">
  file <input type="file" name="upload_text_file" accept-type="$ACCEPT">
   <small><br /><br /></small>
  <input type="submit" value="store into Wiki">
   <br /><br />
  <input type="checkbox" name="textfile_overwrite_pages" value="1" checked="checked"> overwrite existing page
   <br />
  <input type="checkbox" name="textfile_assume_text" value="1"> assume file is text/plain,
  <input type="checkbox" name="textfile_noext_is_text" value="1" checked="checked"> if no .ext
   <br />
  <input type="checkbox" name="textfile_brute_force" value="1"> brute-force extract text data from binary file
   <br /><br />
  strip <select name="textfile_strip_ext"><option value="0">no</option><option value="1" selected="selected">last</option><option value="2">all</option></select> file name extension(s), and use the remaining string as destination page name
   <br />
  or store file as <input type="text" name="textfile_saveas" size="22"> (page name)
</form>
END
      );
   }
   else {
      $o .= ewiki_textfile_save($_FILES["upload_text_file"]);
   }

   return($o);
}


/*  This code is responsible for checking the parameters of uploaded
    $_FILES, unpacking zip archives and tarballs, and finally converting
    (from *.* into text/wiki) and storing individual files as wikipages
    into the database.
*/
function ewiki_textfile_save($file=array()) {

   #set_time_limit(+30);
   $o = "";

   #-- upload file vars
   $fn = $file["tmp_name"];
   $fn_orig = $file["name"];
   $mime = $file["type"];

   #-- pre-guess content
   if ($_REQUEST["textfile_assume_text"] && !strpos($fn_orig, ".") && ($mime=="application/octet-stream")) {
      $mime = "text/plain";
   }

   #-- is current file an archive?
   if (strpos($fn_orig,".sx")) {
      $mime = "application/vnd.sun.xml.writer";
   }
   $untar = (preg_match("#^application/(x-)?(g|us)tar$#", $mime) || preg_match("#\.tar|\.tgz#", $fn_orig)) ? EWIKI_UNTAR : "";
   $unzip = (($mime=="application/zip") || strpos($file["name"],".zip")) ? EWIKI_UNZIP : "";
   $multimime = (strstr($mime, "multipart/"));

   #-- tarball or zip archive ------------------------------------------------
   if ($untar || $unzip) {

      #-- create temporary dir
      $tmp_dir = EWIKI_TMP."/ewiki-txtupl-$untar$unzip-".time()."-".rand(0,523555).".tmp.d";
      mkdir($tmp_dir);
      $cwd = getcwd(); chdir($tmp_dir);

      #-- archive extraction
      if ($untar) {
                     { exec("$untar xzf '$fn'", $uu, $error); }
         if ($error) { exec("$untar xjf '$fn'", $uu, $error); }
         if ($error) { exec("$untar xf '$fn'", $uu, $error); }
      }
      elseif ($unzip) {
         `$unzip "$fn"`;
      }

      #-- go throgh directory
      chdir($cwd);
      $o .= ewiki_textupload_readdir($tmp_dir);

      #-- remove temporary directory
      `rm -rf "$tmp_dir"`;

   }

   #-- multipart/ mime archive -----------------------------------------------
   elseif ($multimime) {
   }

   #-- plain file --------------------------------------------------------
   else {

      #-- extract wiki content from file
      $content = ewiki_textfile_convert(
         $fn, $fn_orig, $mime,
         $_REQUEST["textfile_brute_force"],
         $_REQUEST["textfile_assume_text"],
         $_REQUEST["textfile_noext_is_text"]
      );

      #-- make short filename
      $fn_orig = strtr($fn_orig, "\\", "/");
      if ($p = strrpos($fn_orig, "/")) {
         $fn_orig = substr($fn_orig, $p+1);
      }
      if (!$fn_orig)  {
         $fn_orig = "";
      }

      #-- destination filename
      $dest_id = trim($_REQUEST["textfile_saveas"]);
      if (!$dest_id) {
         $dest_id = trim(trim($fn_orig), ".");
         if ($_REQUEST["textfile_strip_ext"] == 2) {
            $dest_id = strtok($fn_orig, ".");
         }
         if ($_REQUEST["textfile_strip_ext"] == 1) {
           if ($p = strrpos($dest_id, ".")) {
              $dest_id = substr($dest_id, 0, $p);
           }
         }
         $dest_id = trim($dest_id);
      }

      #-- reject
      if (!$dest_id) {
         return($o . "� Could not store '$fn_orig', please specify a page name to use as destination.<br />\n");
      }


      #-- store -----------------------------------------------------------
      if ($content) {
         $ahref_dest = '<a href="' . ewiki_script("",$dest_id) . '">' . $dest_id . '</a>';

         $data = ewiki_db::GET($dest_id);
         if ($data && !$_REQUEST["textfile_overwrite_pages"]) {
            $o .= "� did not overwrite existing page '$ahref_dest' with content from file '$fn_orig'<br />\n";
         }
         else {
            if (empty($data)) {
               $data = array(
                  "id" => $dest_id,
                  "version" => 0,
                  "created" => time(),
                  "meta" => "",
                  "flags" => EWIKI_DB_F_TEXT,
                  "refs" => "",
                  "hits" => 0,
               );
            }
            $data["version"]++;
            $data["lastmodified"] = time();
            $data["author"] = ewiki_author("TextUpload");
            $data["content"] = $content;
            ewiki_scan_wikiwords($data["content"], $ewiki_links, "_STRIP_EMAIL=1");
            $data["refs"] = "\n\n".implode("\n", array_keys($ewiki_links))."\n\n";

            if (ewiki_db::WRITE($data)) {
               $o .= "� extracted text from '$fn_orig' into page '$ahref_dest'<br />\n";

#<debug>#  $o .= "<br /><br /><h1>src</h1>" . ($data["content"])."<h1>page</h1>" . ewiki_format($data["content"]);
            }
            else {
               $o .= "� database error occoured, when writing to '$ahref_dest' from file '$fn_orig'<br />\n";
            }
         }
      }
      else {
         $o .= "� couldn't detect format (and text content) of '$fn_orig'<br />\n";
      }

   }

   return($o);
}



/*  reads a directory (from unpackked tarballs), and re-calls the
    _textfile_save() function for storing individual files.
*/
function ewiki_textupload_readdir($tmp_dir) {

   $o = "";

   $dh = opendir($tmp_dir);
   while ($fn = readdir($dh)) {

      if ($fn[0]==".") {
         continue;
      }
      elseif (is_dir("$tmp_dir/$fn")) {
         $o .= ewiki_textupload_readdir("$tmp_dir/$fn");
      }
      else {
         $o .= ewiki_textfile_save(array(
            "tmp_name" => "$tmp_dir/$fn",
            "name" => "$fn",
            "type" => ewiki_get_mime_type("$tmp_dir/$fn"),
         ));
      }
   }
   closedir($dh);

   return($o);
}


#==========================================================================


/*  Guesses a files mime type using magic data, or the file extension
    mapping list in /etc/mime.types
*/
function ewiki_get_mime_type($fn) {

   global $mime_ext;

   #-- default
   $mime = "application/octet-stream";

   #-- by content
   if (function_exists("mime_content_type")) {
      $mime = mime_content_type($fn);
   }

   #-- by ext
   if (($mime == "application/octet-stream") && strpos($fn, ".")) {
      if (empty($mime_ext) && ($list=file("/etc/mime.types")))
      foreach ($list as $line) {
         $line = trim($line);
         $m = strtok($line, " \t");
         if (strpos($m, "/") && $e=explode(" ", trim(strtr(strtok("\n"), ".\t", "  "))) ) {
            foreach ($e as $ext) if ($ext) {
               $mime_ext[$ext] = $m;
            }
         }
      }
      $ext = explode(".", $fn);  unset($ext[0]);
      foreach ($ext as $e) {
         if ($m = $mime_ext[$e]) {
            $mime = $m;
         }
      }
   }

   return($mime);
}



#==========================================================================


/*  This function tries to convert a uploaded plain file into a text/plain
    (we here call it text/wiki) page. It uses some filters to convert from
    file format to file format (usually converting into text/html and then
    into Wiki format).
*/
function ewiki_textfile_convert($fn, $fn_orig, $mime, $extr_bin=0, $is_text=0, $noext2text=1)
{
   global $ewiki_textfilters;

   #-- handled by ewiki_unformat()
   $html_variants = array(
      "text/html", "text/xhtml", "text/wap", "application/vnd.wap.wml",
      "text/xml+html", 
      "text/x.office.content.xml",  # an extracted OpenOffice content.xml
   );

   #-- read in complete file
   if ($f = fopen($fn, "rb")) {
      $content = fread($f, 1<<18);  #-- max 256K
      fclose($f);
   }
   else {
      return(false);
   }

   #-- get mime-type
   if ($mime == "application/octet-stream") {
      $mime = ewiki_get_mime_type($fn_orig);
   }
   if ($is_text && ($mime == "application/octet-stream")) {
      $mime = "text/plain";
   }
   if ($noext2text && !strpos($fn_orig,".") && ($mime == "application/octet-stream")) {
      $mime = "text/plain";
   }

   #-- go thru installed filters 
   foreach ($ewiki_textfilters as $filter_row) {
      list($f_from, $f_into, $f_prog) = $filter_row;
      if (($f_from==$mime) || ($f_from=="*/*")) {

         $tmpf = EWIKI_TMP."/ewiki-txtupl-filter-".time()."-".rand(0,564595).".tmp";
         if ($f = fopen($tmpf, "wb")) {
            fwrite($f, $content);
            fclose($f);
         }
         else { continue; }

         #-- replace placeholders "%f" and "%o"
         if (!strpos($f_prog, "%f")) {
            $f_prog .= " < '%f' ";
         }
         $f_prog = str_replace("%o", EWIKI_DEV_STDOUT, $f_prog);
         $f_prog = str_replace("%f", $tmpf, $f_prog);

         #-- exec, unlink temporary file
         $new_content = `$f_prog`;
         unlink($tmpf);

         #-- success?
         if ($new_content) {
            $content = $new_content;
            $mime = $f_into;
            unset($new_content);
         }
      }
   }

#
#...
#

   #-- brute force text extraction from binary files
   if ($extr_bin && (strtok($mime, "/") == "application")) {
      # ??? #
      preg_match_all("/([\n\r\t\040-\176\177-\237\260-\377]{7,})/", $content, $uu);
      if ($uu) {
         $content = implode("\n", $uu[1]);
         $mime = "text/plain";
      }
   }

   #-- HTML->Wiki-source transformation
   if (in_array($mime, $html_variants)) {
      $content = ewiki_unformat($content);
      $mime = "text/wiki";
   }

   #-- file reached text status
   if ($mime == "text/plain") {
      #- this is simple
      $mime = "text/wiki";
   }

   #-- finish
   if ($mime == "text/wiki") {
      return($content);
   }
}



#===========================================================================

/****************
#echo
 ewiki_unformat('
<html>
<body>

<h2>HeadLine</h2>

See also <a href="WikiText.html">WikiText</a> or somehwere __else__.

<span class="para">We\'ll expect some magic here?</span>

<ul>
  <li> list ==entry== 1
  <li> list entry **2**
  <ol><li> list entry 2-1</ol>
</ul>
   
</body>
</html>
');
********************/


/*  This function is believed to backconvert pages from .html into
    WikiMarkup. As it shall be used to convert any .html file (and not only
    ewiki exported ones), it cannot convert tables back (think of layout
    tables).
    It has limited support for openoffice xml (for full support this needed
    to be an xml parser).
*/
function ewiki_unformat($html) {

   $src = "";

   $tagmap = array(
      "b" => "**",
      "i" => "''",
      "strong" => "__",
      "em" => "''",
      "tt" => "==",
      "big" => "##",
      "small" => "도",
      "sup" => "^^",
      "br" => "\n%%%\n",
      "hr" => "\n\n----\n\n",
   );
   $standalone_tags = array(
      "img", "br", "hr", "input", "meta", "link",
   );
   $xml = array();
   $xml_i = false;

#-- TODO
# table
# pre
# ..



   #-- walk through all tags ----------------------------------------------
   $tag_level = 0;
   $close_css = array();
   $len = strlen($html);
   $pos = 0;
   $loop = 500;
   $in_table = 0;   // ignore such??
   $in_pre = 0;
   $in_list = 0;
   $list = "";
   while (($pos < $len) && $loop--) {

      #-- decode step by step
      list($pretext, $tagstr, $tagattr) = ewiki_htmlparser_get($html,$pos,$len,$in_pre);
      $tagname = ltrim($tagstr, "/");

      #-- add pre-text (no linebreaks in it)
      if ($pretext) {
         $src .= $pretext;
      }
      $src .= $post;
      $post = "";

      #-- handle things we have WikiMarkup for
      if ($tagstr) switch ($tagstr) {

         #-- paragraphes
         case "p":
            $src .= "\n";
            $tag_level=0; $close_css=array();
            break;
         case "/p":
            $src .= "\n\n";
            break;

         #-- headlines
         case "h1":
         case "h2":
            $src .= "\n\n!!! "; break;
         case "h3":
            $src .= "\n\n!! "; break;
         case "h4":
            $src .= "\n\n! "; break;
         case "h5":
         case "h6":
            $src .= "\n\n__"; break;

         case "/h1":
         case "/h2":
         case "/h3":
         case "/h4":
         case "/h5":
            $src .= "\n\n"; break;
         case "/h5":
         case "/h6":
            $src .= "__\n\n"; break;


         #-- lists
         case "ul":
         case "ol":
            if (!$in_list) {
               $src .= "\n\n";
            }
            $in_list++;
            $list .= ($tagstr=="ul") ? "*" : "#";
            break;

         case "/ul":
         case "/ol":
            $in_list--;
            $list = substr($list, 0, $in_list);
            if (!$in_list) {
               $src .= "\n\n";
            }
            break;

         case "li":
            $src .= "\n" . $list;


         #-- hyperlinks
         case "a":
            $name = $tagattr["name"];
            $href = $tagattr["href"];
            if ($href || $name) {
               $text = "";
               do {
                  list($t,$tagstr,$tagattr) = ewiki_htmlparser_get($html,$pos,$len);
                  $text = trim("$text$t");
               }
               while ($tagstr!="/a");

               if (empty($text)) {
                  $text = "$name$href";
               }

               #-- define anchor
               if ($name) {
                  $src .= "[#$name \"$text\"]";
               }
               #-- link to anchor
               elseif ($href[0] == "#") {
                  $src .= "[.#$href \"$text\"]";
               }
               #-- hyperlink
               else {
                  #-- check for InterWikiLink
                  foreach ($ewiki_config["interwiki"] as $abbr=>$url) {
                     $url = str_replace("%s", "", $url);
                     if (substr($href, 0, strlen($url)) === $url) {
                        $href = "?id=".$abbr.":".substr($href, strlen($url));
                     }
                  }
                  #-- binary link (should rarely happen)
                  if ($p=strpos($href, EWIKI_IDF_INTERNAL)) {
                     $href = strtok(substr($href, $p), "&");
                     $src .= "[$href \"$text\"]";
                  }
                  #-- www link
                  elseif (strpos($href, "://")) {
                     if ($href == $text) {
                        $src .= "$href";
                     } else {
                        $src .= "[$href \"$text\"]";
                     }
                  }
                  else {
                     $wikilink = "";
                     #-- ewiki URL
                     if (preg_match('#\?(?:id|page|file|name)=(.+)(&(?>!amp;)|$)#', urldecode($href), $uu)) {
                        $wikilink = $uu[1];
                     }
                     #-- ewiki .html export filenames
                     elseif (preg_match('#^([^/:]+)(\.html?)?$#', urldecode($href), $uu)) {
                        $wikilink = $uu[1];
                     }
                     #-- looks like wikilink
                     if ($wikilink) {
                        if (strpos($wikilink, "view/")===0) {
                           $wikilink = substr($wikilink, 5);
                           $src .= "[$text|$wikilink]";
                        }
                        if (($wikilink == $text) || ($wikilink == str_replace(" ", "", $text))) {
                           if (preg_match('/(['.EWIKI_CHARS_U.']+['.EWIKI_CHARS_L.']+){2}/', $wikilink)) {
                              $src .= $wikilink;
                           }
                           else {
                              $src .= "[$wikilink]";
                           }
                        }
                        else {
                           $src .= "[$wikilink \"$text\"]";
                        }
                     }
                     #-- absolute URL
                     elseif ($href[0] == "/") {
                        $src .= "[url:$href \"$text\"]";
                     }
                     #-- should eventually drop this
                     else {
                        $src .= "[$href \"$text\"]";
                     }
                  }
               }
            }
            break;


         #-- images
         case "img":
            if ($href = $tagattr["src"]) {
               ($alt = $tagattr["alt"]) or ($alt = $tagattr["title"]) or ($alt = "<img>");
               $src .= "[$alt|$src]";
            }
            break;


         #-- yet unsupported
         case "code":
            if ($end = strpos($html, '</code', $pos)) {
               $end = strpos($html, '>', $end);
               $src .= "\n\n<code>" . substr($html, $pos, $end-$pos);
               $pos = $end + 1;
            }
            break;


         #-- pre
         case "pre":
            $src .= "\n<pre>\n";
            $in_pre = 1;
            break;
         case "/pre":
            $src .= "\n</pre>\n";
            $in_pre = 0;
            break;


         #-- OpenOffice -----------------------------------------------------
         case "office:document-content":
            if ($tagattr["xmlns:office"] && $tagattr["xmlns:text"] && ($tagattr["office:class"]=="text")) {
               $xml["office"] = 1;
            }
            break;
         #-- formatting
         case "style:style":
         case "style:properties":
            if ($xml["office"]) {
               if ($uu = $tagattr["style:name"]) {
                  $xml_i = $uu;  # style selector
                  $xml[$uu] = array();
               }
               if ("bold" == $tagattr["fo:font-weight"]) {
                  $xml[$xml_i][0] .= "__";
               }
               if ("italic" == $tagattr["fo:font-style"]) {
                  $xml[$xml_i][0] .= "''";
               }
               if (strpos($tagattr["style:parent-style-name"], "eadline")) {
                  $xml[$xml_i][1] = "\n!";
               }
            }
            break;
         #-- content
         case "text:p":
         case "text:span":
            $xml_i = $tagattr["text:style-name"];
            $src .= $xml[$xml_i][1]
                  . $xml[$xml_i][0];
            break;
         case "/text:p":
         case "/text:span":
            $src .= strrev($xml[$xml_i][0]);
            $xml_i == false;
            if (!$xml["list"] && ($tagstr == "/text:p")) {
               $src .= "\n";
            }
            break;
         #-- headlines
         case "text:h":
            $level = $tagattr["text:level"];
            $src .= "\n" . str_repeat("!", 1 + ($level>4)?1:0 + ($level>6)?1:0);
            break;
         case "/text:h":
            $src .= "\n";
            break;
         #-- links
         case "text:a":
            if ($href = $tagattr["xlink:href"]) {
               $src .= "[$href \"";# . $pretext;
            }
            break;
         case "/text:a":
            $src .= "\"]";
            break;
         #-- lists
         case "text:list-item":
            $src .= "\n" . $xml["list"] . " ";
            break;
         case "text:ordered-list":
            $xml["list"] .= "#";
            break;
         case "text:unordered-list":
            $xml["list"] .= "*";
            break;
         case "/text:ordered-list":
         case "/text:unordered-list":
            $xml["list"] = substr($xml["list"], 0, -1);
            $src .= "\n";  # there aren't nested lists in OO anyhow
            break;


         #-- anything else --------------------------------------------------
         default:
            #-- one of the standard tags?
            if ($add = @$tagmap[$tagname]) {
               $src .= $add;
            }
            break;

      }#- switch(tag)


      #-- count tags
      if ($tagstr[0] == "/") {
         $tag_level--;
         if ($tag_level<0) { $tag_level=0; }
      }
      elseif (!in_array($tagname, $standalone_tags)) {
         $tag_level++;
      }

      #-- markup_css
      if (($css=@$tagattr["class"]) || ($css=strtr($tagattr["class"], " \r\t\n", "    "))) {
         $css = strtr($css, " ", "-");
         $src .= "@@$css ";
         $close_css[$tag_level-1]++;
      }
      if (($css=@$tagattr["style"]) || ($css=strtr($tagattr["style"], " \r\t\n", "    "))) {
         $css = str_replace($css, " ", "");
         $src .= "@@$css ";
         $close_css[$tag_level-1]++;
      }
      while (@$close_css[$tag_level]) {
         $src .= "@@ ";
         $close_css[$tag_level]--;
      }

      $src .= $post;
   }

   return($src);
}


/*  Fetches (step by step) next html <tag> from the input string, and
    also returns text content prepending it.
*/
function ewiki_htmlparser_get(&$html, &$pos, &$len, $pre=0) {

      $text=$tagstr=$tagattr="";

      #-- search next tag
      $l = strpos($html, "<", $pos);
      $r = strpos($html, ">", $l);
      if (($l===false) or ($r===false)) {
         #-- finish
         $text = substr($html, $pos);
         $pos = $len;
      }

      #-- text part
      if ($l >= $pos) {
         $text = substr($html, $pos, $l-$pos);
         if (!$pre) {
            $text = strtr($text, "\r\n", "  ");
         }
         $pos = $l;
      }

      #-- any tag here?
      if ($r >= $pos) {
         $pos = $r + 1;
         $tag = substr($html, $l + 1, $r - $l - 1);

         #-- split into name and attributes
         $tagstr = strtolower(rtrim(strtok($tag, " \t\n>"),"/"));
         $tagattr = array();
         if (($tattr=strtok(">")) && strpos($tattr,"=")) {
            preg_match_all('/([-:\w]+)=(\".*?\"|[^\s]+)/', $tag, $uu);
            if ($uu) {
               foreach ($uu[1] as $i=>$a) {
                  $tagattr[$uu[1][$i]] = trim($uu[2][$i], '"');
               }
            }
         }
      }#- tag

   return(   array($text, $tagstr, $tagattr)   );
}


?>