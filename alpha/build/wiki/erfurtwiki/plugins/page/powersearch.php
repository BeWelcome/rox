<?php

/*
   This plugins provides the internal page PowerSearch, which allows
   to search in page contents and/or titles (or for author names, if any),
   it tries to guess how good the database match matches the requested
   search strings and orders results.
   The top 10 results are printed more verbosely.
*/


define("EWIKI_PAGE_POWERSEARCH", "PowerSearch");
$ewiki_plugins["page"][EWIKI_PAGE_POWERSEARCH] = "ewiki_page_powersearch";
$ewiki_plugins["action"]["search"] = "ewiki_action_powersearch";


function ewiki_action_powersearch(&$id, &$data, &$action) {
    $o = ewiki_make_title(EWIKI_PAGE_POWERSEARCH, EWIKI_PAGE_POWERSEARCH, 2);        
    $o.= ewiki_powersearch($id);
    return ($o);
}


function ewiki_page_powersearch($id, &$data, $action) {
    $q = @$_REQUEST["q"];

    ($where = preg_replace('/[^a-z]/', '', @$_REQUEST["where"]))
    or ($where = "content");

    $o = ewiki_make_title($id, $id, 2);
    
    if (empty($q)) {        
        $o .= '<div class="search-form">
        <form name="powersearch" action="' . ewiki_script("", $id) . '" method="GET">
        <input type="hidden" name="id" value="'.$id.'">
        <input type="text" id="q" name="q" size="30">
        in <select name="where"><option value="content">page texts</option><option value="id">titles</option><option value="author">author names</option></select>
        <br /><br />
        <input type="submit" value=" &nbsp; &nbsp; S E A R C H &nbsp; &nbsp; ">
        </form></div>
        <script type="text/javascript"><!--
        document.powersearch.q.focus();
        //--></script>';
        
        return($o);
    }
    else { 
        $o .= ewiki_powersearch($q, $where);
        return ($o);
    }

    return('');
}


function ewiki_powersearch($q, $where='content'){
    $q = ewiki_lowercase(preg_replace('/\s*[\000-\040]+\s*/', ' ', $q));
    
    $found = array(); 
    $scored = array(); 
    
    #-- initial scan
    foreach (explode(" ", $q) as $search) {
    
     if (empty($search)) {
        continue;
     }
    
     $result = ewiki_db::SEARCH($where, $search);
    
        while ($row = $result->get()) {        
            if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
                
                $id = $row["id"];
                $content = strtolower($row[$where]);
                unset($row);
                
                #-- have a closer look
                $len1 = strlen($content) + 1;
                
                if (!isset($scored[$id])) {
                    $scored[$id] = 1;
                }
                $scored[$id] += 800 * (strlen($search) / $len1);
                $scored[$id] += 65 * (count(explode($search, $content)) - 2);
                $p = -1;
                while (($p = strpos($content, $search, $p+1)) !== false) {
                    $scored[$id] += 80 * (1 - $p / $len1);
                }
            
            }#if-TXT
        }
    }
    
    
    #-- output results
    arsort($scored);
    
    $o = "<ol>\n";
    $n = 0;
    foreach ($scored as $id => $score) {
    
     #-- refetch page for top 10 entries (still cached by OS or DB)
     $row = ($n < 10) ? ewiki_db::GET($id) : NULL;
    
     #-- check access rights in protected mode
     if (EWIKI_PROTECTED_MODE && !ewiki_auth($id, $row, "view", $ring=false, $force=0)) {
        if (EWIKI_PROTECTED_MODE_HIDING) {
            continue;
        } else {
           $row["content"] = ewiki_t("FORBIDDEN");
        }
    }   
    
     $o .= "<li>\n";
     $o .= '<div class="search-result '.($oe^=1?"odd":"even").'">'
         . '<a href="' . ewiki_script("", $id) . '">' . $id . "</a> "
    #<off>#      . "<small><small>(#$score)</small></small>"
         . "\n";
    
     #-- top 10 results are printed more verbosely
    
     if ($n++ < 10) {
    
     preg_match_all('/([_-\w]+)/', $row["content"], $uu);
        $text = htmlentities(substr(implode(" ", $uu[1]), 0, 200));
        $o .= "<br />\n<small>$text\n"
            . "<br />" . strftime(ewiki_t("LASTCHANGED"), $row["lastmodified"])
            . "<br /><br /></small>\n";
     }
    
     $o .= "</div>\n";
    
     $o .= "</li>\n";
    
    }
    
    $o .= "</ol>\n";
    return($o); 
    
}


?>