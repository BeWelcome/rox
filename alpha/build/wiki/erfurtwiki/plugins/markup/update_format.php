<?php

/*
  Converts/Fixes misused markup to correct format (meaning of ! for
  titles, spaces required with lists markup), when activated on a
  page (this gets an option on the edit/ page).
  
* AndyFundinger(Andy@burgiss.com)
*/

 $ewiki_t["en"]["UPDHEADERFORMAT"] = "Swap Header Order";		
 $ewiki_t["de"]["UPDHEADERFORMAT"] = "vertausche Titel-Ordnung";
 
 $ewiki_plugins["action"]["updformatheader"] = "ewiki_header_format_swap";
 $ewiki_config["action_links"]["view"]["updformatheader"] = "UPDHEADERFORMAT";
 $ewiki_plugins["edit_form_append"][] = "ewiki_edit_form_append_updFormat";

 function ewiki_header_format_swap($id, &$data, $action){ 

    ewiki_clean_format($data["content"]);
    $data["content"]=preg_replace("/^!!! (.*)$/im",'!x $1',$data["content"]);
    $data["content"]=preg_replace("/^! (.*)$/im",'!!! $1',$data["content"]);
    $data["content"]=preg_replace("/^!x (.*)$/im",'! $1',$data["content"]);
    
    //echo $data["content"];
    
    return(ewiki_page_edit($id,$data,$action));
 }
 
 function ewiki_clean_format(&$wikiText){ 
    $wikiText=preg_replace("/^([;:#\*!-]+) *(.*)$/im",'$1 $2',$wikiText);
 }

function ewiki_edit_form_append_updFormat($id, $data, $action) {
    global $ewiki_ring;
        
    if (!ewiki_auth_perm_liveuser($id, $data, 'manage', $ewiki_ring, 0)) {    
        return '';
    }
    
    return(' &nbsp; <a href="'. ewiki_script('updformatheader', $id) . '">' . ewiki_t("UPDHEADERFORMAT") . '</a>');
}

?>