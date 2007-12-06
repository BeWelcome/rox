<?php
#
# this is mainly a joke plugin, however it shows up some statistical
# data about the WikiPages database


$ewiki_plugins["page"]["ScanDisk"] = "ewiki_page_scandisk";


function ewiki_page_scandisk($id, $data, $action) {

   $o .= "<h2>$id</h2>\n";

   $s_fv = 0;
   $s_files = 0;
   $s_n_txt = 0;
   $s_n_bin = 0;
   $s_n_txt_r = 0;
   $s_n_bin_r = 0;
   $s_n_dis = 0;
   $s_n_err = 0;
   $s_n_htm = 0;
   $s_n_ro = 0;
   $s_n_wr = 0;
   $s_holes = 0;

   $result = ewiki_db::GETALL(array("flags", "meta", "created", "version"));

   $s_files = $result->count();

   while ($row = $result->get()) {

      $flags = $row["flags"];
      $s_n_txt += ($flags & EWIKI_DB_F_TEXT) ?1:0;
      $s_n_bin += ($flags & EWIKI_DB_F_BINARY) ?1:0;
      $s_n_txt_r += (($flags & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) ?1:0;
      $s_n_bin_r += (($flags & EWIKI_DB_F_TYPE) == EWIKI_DB_F_BINARY) ?1:0;
      $s_n_dis += ($flags & EWIKI_DB_F_DISABLED) ?1:0;
      $s_n_err += (($flags & EWIKI_DB_F_TYPE) == 0) ?1:0;
      $s_n_htm += ($flags & EWIKI_DB_F_HTML) ?1:0;
      $s_n_ro += ($flags & EWIKI_DB_F_READONLY) ?1:0;
      $s_n_wr += ($flags & EWIKI_DB_F_WRITEABLE) ?1:0;

      $s_fv += $row["version"];

      $id = $row["id"];
      for ($v=1; $v<=$row["version"]; $v++) {
         $r = ewiki_db::GET($id, $v);
         if (empty($r["created"]) || empty($r["content"])) {
            $s_holes += 1;
         }
      }
   }

   $s_frag = ($s_fv ? (((int)(100*$s_holes/$s_fv))/100) ."%" : "N/A");

   $o .= '<table border="0" cellpadding="2" cellspacing="1">'
       . "<tr><td align=\"right\" bgcolor=\"#ccccee\">number of wikipages</td><td bgcolor=\"#eeeecc\">$s_files</td></tr>\n"
       . "<tr><td align=\"right\" bgcolor=\"#ccccee\">text<br />binary<br />disabled pages<br />errors</td><td bgcolor=\"#eeeecc\">$s_n_txt <small>($s_n_txt_r)</small><br />$s_n_bin <small>($s_n_bin_r)</small><br />$s_n_dis<br />$s_n_err</td></tr>\n"
       . "<tr><td align=\"right\" bgcolor=\"#ccccee\">flagged read-only<br />explicit writable<br />html enabled pages</td><td bgcolor=\"#eeeecc\">$s_n_ro<br />$s_n_wr<br />$s_n_htm</td></tr>\n"
       . "<tr><td align=\"right\" bgcolor=\"#ccccee\">absolute page number<br />version holes<br />database fragmentation</td><td bgcolor=\"#eeeecc\">$s_fv<br />$s_holes<br />$s_frag</td></tr>\n"
       . "</table>\n";

   return($o);
}


?>