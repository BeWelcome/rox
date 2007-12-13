<?php
	new MOD_old_bw_func(); // Just to have the rox mecanism to include the needed functions
	$VMenu=BuildVolMenu() ;
	if (count($VMenu)>0) {  // If there is a volunteer menu
		 echo "          <h3>", ww("VolunteerAction"), "</h3>\n";
     echo "          <ul class=\"linklist\">" ;
		 foreach ($VMenu as $LL) {
		 		 echo "        <li> <a href=\"".bwlink($LL->link)."\" title=\"".$LL->help."\">".$LL->text."</a></li>\n";
		 }
     echo "          </ul>" ;
	} // end if there is a volunteer menu
?> 