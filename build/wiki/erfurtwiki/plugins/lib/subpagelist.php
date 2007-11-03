<?php

//General subpages function(s) originally part of the aview_subpages plugin

//Original code by AndyFundinger


function ewiki_subpage_list($id,$postfix=""){

  $_hiding = EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING;

	$result = ewiki_db::SEARCH("id", $id.$postfix);
	while ($row = $result->get()) {

            #-- retrieve and check rights if running in protected mode

            if ($_hiding){
                if(!ewiki_auth($row["id"], $uu,'view', $ring=false, $force=0)) {
                    continue;
                }
            }   
            $pages[$row["id"]] = "";
	}
	return($pages);
}




?>