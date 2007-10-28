<?php
#
#  OpenSearch backend for ErfurtWiki with MySQL database, but should also
#  work for PhpWiki 1.2 installs (but you need to glue it yourself then).
#


#-- plugin registration
$wikiapi["wiki.search"] = "ewiki_opensearch_api";


#-- the interface
function ewiki_opensearch_api($query, $params) {

   #-- database connection
   // should already be established
   
   #-- check request params
   $params = (array)$params
   + array(
      "n_hit" => 1000,
      "m_title" => 3.0,
      "q_pos" => 2.0,
      "q_not" => 0.0,
      "limit" => 5000,
   );
   $n_hit = $params["n_hit"];
   $q_pos = $params["q_pos"];
   $q_not = $params["q_not"];
   $calc = array();

   #-- search target field mapped to internal SQL row names
   $fieldnames = array(
      "title" => "title",
      "text" => "text",
      "" => "content",
      "author" => "author",
      "links" => "links",
      "meta" => "meta",
      "filename" => "title",
   );

   
   #-- sort request terms into MUST and NOT ------------------------ SQL ---
   $sql = array(
     "MUST" => array(),
     "NOT" => array(),
   );
   foreach ($query as $i=>$qterm) {
      list($pattern, $dep, $field, $flags) = $qterm;
      $f_regex = strpos($flags, "REGEX") !== false;
      $f_nocase = strpos($flags, "NOCASE") !== false;
      $m_field = isset($params["m_$field"]) ? $params["m_$field"] : 0;
      
      if ($field = $fieldnames[$field]) {

         $pattern = mysql_escape_string($pattern);

         if ($f_regex) {
            $s = "$field REGEXP '$pattern'";
         }
         elseif ($f_nocase) {
            $pattern = strtolower($pattern);
            $s = "LOCATE('$pattern', LCASE($field)) > 0";
         }
         else {
            $s = "LOCATE('$pattern', $field) > 0";
         }

         #-- add
         $sql[$dep][] = "($s)";
      }

      $calc[] = array($pattern, $dep, $field, $f_regex, $f_nocase, $m_field);

   }
   if ($q_not) {
      $sql_MUST = implode(" OR ", $sql["MUST"]);
      $sql_NOT = "";
   }
   else {
      $sql_MUST = implode(" AND ", $sql["MUST"]);
      $sql_NOT = implode(" OR ", $sql["NOT"]);
      if ($sql_NOT) { $sql_NOT = " AND NOT ($sql_NOT)"; }
   }

   #-- last validity checks
   if (!$sql_MUST) {
      // no way to send an error with ED xmlrpclib ?
      return("551: at least one MUST search term must be given");
   }
   

   #-- get hit list
   $sql_DB_F_TEXT = "((flags & 1) > 0) AND";
   $sql_query_result = mysql_query($sql="
      SELECT
         pagename AS id, version AS version,
         pagename AS title,
         content AS text,
         CONCAT(pagename, content) AS content,
         meta,
         author,
         refs AS links,
         lastmodified,
         created,
         flags
      FROM
         ewiki
      WHERE
         $sql_DB_F_TEXT
         $sql_MUST
         $sql_NOT
      GROUP BY id ORDER BY version
   ");

   #-- start hit score calculation ---------------------------------- CALC ---
   $result_list = array();
   $ordered_list = array();
   if ($sql_query_result) while ($row = mysql_fetch_array($sql_query_result)) {

      $score = 0.0;   // final score
      $m_fin = 1.0;   // to eventually decrease it later

      #-- score each search term for hit
      foreach ($calc as $qterm) {
         list($pattern, $dep, $field, $f_regex, $f_nocase, $m_field) = $qterm;
         
         #-- minscore
         $add = $n_hit;
         if ($m_field) {
            $add *= $m_field;
         }
         
         #-- regex hit
         if ($f_regex) {
            $ok = preg_match($pattern, $row["field"]);
            if ($dep=="MUST") {
               $score += $add * ($ok ? 1.0 : $q_not);
            }
            elseif ($dep=="NOT") {
               if ($ok) {
                  $m_fin *= $q_not;
               }
            }
         }
         #-- strsearch hit
         else {
            $text = $f_nocase ? strtolower($row[$field]) : $row[$field];
            $len = strlen($text) + 1;

            #-- add points for each hit, weighted by match position
            if ($dep=="MUST") {
               $p = -1;
               while (false !== ($p = strpos($text, $pattern, $p+1))) {
                  $score += $add * ($q_pos - ($q_pos - 1) * ($p / $len));
               }
            }
            #-- NOT search term
            elseif (strpos($text, $pattern) !== false) {
               $m_fin *= $q_not;
            }
         }
         
      }
      
      #-- add to list
      if ($score *= $m_fin) {
         $result_list[] = array(
            "title" => $row["title"],
            "url" => ewiki_script_url("", $row["title"]),
            "excerpt" => substr(strtr($row["text"], "\r\n\t\f", "    "), 0, 300),
            "score" => $score,
            "lastmodified" => $row["lastmodified"],
         );
         $ordered_list[] = $score;
      }
   }
   
   #-- sort result?
   arsort($ordered_list);
   foreach ($ordered_list as $i=>$uu) {
      $ordered_list[$i] = $result_list[$i];
      unset($result_list[$i]);
   }
   $result_list = & $ordered_list;


   #-- done
   return($ordered_list);
}


?>