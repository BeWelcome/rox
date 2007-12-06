<?php

/*/

 This plugin makes PhpWiki v1.3.x database tables accessible for ewiki.
 It is mainly used for conversion from PhpWiki to ErfurtWiki - and you
 should not rely on it for daily work, as the PhpWiki tables will
 probably always become inconsistent due to the rather simple access
 approach used herein.

 The code is mainly based upon the PhpWiki database scheme and some
 experiments with an existing setup (after five hours of PhpWikiSetup).
 You must first enable write access - working, but not well tested.

/*/

 define("PHPWIKI13_WRITEACCESS", 0);
 define("EWIKI_PAGE_INDEX", "WikiWikiWeb");
 $ewiki_plugins["database"][0] = "ewiki_database_phpwiki13";
 



class ewiki_database_phpwiki13 {


   function ewiki_database_phpwiki13() {
   }


   function GET($id, $version=false) {
      if ($version) {
         $ver_sql = " AND version=$version";
      }
      $id = mysql_escape_string($id);

      if (($result = mysql_query("SELECT *
           FROM version LEFT JOIN page USING (id)
           WHERE pagename='$id' $ver_sql ORDER BY version DESC LIMIT 1"))
      and ($row = mysql_fetch_array($result)) )
      {

         #-- decode meta data
         $dec1 = unserialize($row["pagedata"]);
         $dec2 = unserialize($row["versiondata"]);
         $dec1["markup"] = $dec2["markup"];
         $created = ($dec1["created"] ? $dec1["created"] : UNIX_MILLENNIUM);
         $r = array(
            "id" => $row["pagename"],
            "version" => $row["version"],
            "content" => $row["content"],
            "author" => $dec2["author"],
            "lastmodified" => $row["mtime"],
            "hits" => $row["hits"],
            "flags" => EWIKI_DB_F_TEXT,
            "created" => $created,
            "refs" => "\n",
            "meta" => $dec1
         );

         #-- get flags
         if ($dec1["locked"]=="yes") { 
            $r["flags"] |= EWIKI_DB_F_READONLY;
         }
         if (is_int($flags = $dec1["flags"])) {
            $r["flags"] = $flags;
         }
         unset($r["meta"]["flags"]);
         unset($r["meta"]["created"]);
         unset($r["meta"]["locked"]);
     //  $r["meta"] = serialize($r["meta"]);   // must be serialized() in inner ewiki_db:: layer

         #-- fetch $refs[]
         $num_id = $row["id"];
         if ($result = mysql_query("SELECT p2.pagename FROM link
             LEFT JOIN page p1 ON (link.linkfrom=p1.id)
             LEFT JOIN page p2 ON (p2.id=link.linkto)
             WHERE p1.id=$num_id"))
         {
            while ($row = mysql_fetch_array($result)) {
               $r["refs"] .= $row["pagename"] . "\n";
            }
         }
         return($r);
      }

   }


   #-- better don't use this!
   function WRITE($hash, $overwrite=0) {
      if (!PHPWIKI13_WRITEACCESS) {
         die("The plugins/db_phpwiki13 interface is meant for READ ONLY access to PhpWiki databases. To enable write access you first need to set the PHPWIKI13_WRITEACCESS configuration constant. But beware that this may make your database inconsistent and thus could prevent PhpWiki to reuse it afterwards.!<br>\n");
      }

      #-- make all ewiki page vars available in local/func var scope
      extract($hash);

      #-- get existing or new $num_id
      $id = addslashes($id);
      $num_id = $this->NUM_ID($id);

      #-- split data into parts
      $meta = unserialize($meta);
      ($markup = $meta["markup"]) or ($markup = 2);
      unset($meta["markup"]);
      $versiondata = array(
         "markup" => $markup,
         "author" => $author,
         "author_id" => $author,
      );
      $versiondata = addslashes(serialize($versiondata));

      $pagedata = $meta;
      $pagedata["created"] = $created;
      $pagedata["flags"] = $flags;
      if ($flags & EWIKI_DB_F_READONLY) {
         $pagedata["locked"] = "yes";
      }
      $pagedata = addslashes(serialize($pagedata));

      #-- save content
      $content = addslashes($content);
      $result =
         mysql_query("INSERT INTO version (id, version, mtime, minor_edit, content, versiondata) VALUES ($num_id, $version, $lastmodified, 0, '$content', '$versiondata')")
         &&
         mysql_query("UPDATE recent SET latestversion=$version WHERE id=$num_id")
         &&
         mysql_query("UPDATE page SET pagedata='$pagedata' WHERE id=$num_id")
         &&
         mysql_query("REPLACE INTO nonempty (id) VALUES ($num_id)");
      if (!$result) {
         return;
      }

      #-- encode $refs[] into relational database
      mysql_query("DELETE FROM link WHERE linkfrom=$num_id");
      $refs = explode("\n", trim($refs));
      foreach ($refs as $pagename) {
         $pagename = addslashes($pagename);
         $row = mysql_fetch_array(mysql_query("SELECT id FROM page WHERE pagename='$pagename'"));
         if ($to = $row["id"]) {
            mysql_query($sql="REPLACE INTO link (linkfrom, linkto) VALUES ($num_id, $to)");
         }
      }

      return(true);
   }


   function NUM_ID($id) {
      if (($result = mysql_query("SELECT id FROM page WHERE pagename='$id'"))
      and ($row = mysql_fetch_array($result)) ) {
         $num_id = $row["id"];
      }
      else {
         $result = mysql_query("SELECT id FROM page ORDER BY id DESC");
         $row = mysql_fetch_array($result);
         if ($num_id = $row["id"]) {
            $num_id++;
            if (! ($result = mysql_query("INSERT INTO page (id, pagename, hits, pagedata) VALUES ($num_id, '$id', 0, '')")) ) {
               die("db_phpwiki13: could not create new num_id for page\n");
            }
         }
         else {
            die("db_phpwiki13: could not fetch num_id for page\n");
         }
      }
      return($num_id);
   }

   function HIT($id) {
      $id = mysql_escape_string($id);
      mysql_query("UPDATE page SET hits=(hits+1) WHERE pagename='$id'");
   }


   #-- returns: array("WikiPage"=>exists)
   function FIND($list) {
      $r = array();
      $sql = "";
      foreach (array_values($list) as $id) if (strlen($id)) {
         $r[$id] = 0;
         $sql .= ($sql ? " OR " : "") .
                 "(pagename = '" . mysql_escape_string($id) .  "')";
      }
      $result = mysql_query($sql = "SELECT pagename, pagedata AS meta FROM page WHERE $sql");
      while ($result && ($row = mysql_fetch_array($result))) {
         $r[$row["pagename"]] = $row["meta"] ? unserialize($row["meta"]) : 1;
      }
      return($r);
   }


   function GETALL($fields, $mask=0, $filter=0) {
      $r = new ewiki_dbquery_result($fields);
      foreach ($this->ALLFILES() as $id) {
         $row = $this->GET($id);
         $r->add($row);
      }
      return($r);
   }


   function SEARCH($field, $content, $ci="i", $regex=0, $mask=0, $filter=0) {
      $r = new ewiki_dbquery_result(array($field));
      if ($ci && !$regex) {
         $content = strtolower($content);
      }
      foreach ($this->ALLFILES() as $id) {
         $row = $this->GET($id);
         if ($regex) {
            $match = preg_match("\007$content\007$ci", $row[$field]);
         }
         elseif ($ci) {
            $match = false !== strpos(strtolower($row[$field]), $content);
         }
         else {
            $match = false !== strpos($row[$field], $content);
         }
         if ($match) {
            $r->add($row);
         }
      }
      return($r);
   }


#    function DELETE($id, $version) {
#       die("This interface would probably garbage your PhpWiki v1.3 database, so your issued 'DELETE' action will not be executed.");
#    }

   function OTHER() {
      die("Not all features can be used with PhpWiki v1.3 databases.");
   }

   function INIT() {
      die("You cannot create a PhpWiki v1.3 database using this plugin! Please use the default database structure of ErfurtWiki, you're better off with it!");
   }
   
   
   
   #---------------------------------------------------------- internal ---
   
   function ALLFILES() {
      $r = array();
      $result = mysql_query("SELECT pagename AS id FROM nonempty
                             NATURAL LEFT JOIN page");
      if ($result) while ($row = mysql_fetch_array($result)) {
         $r[] = $row["id"];
      }
      return($r);
   }
   
   
} // end of class



?>