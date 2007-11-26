<?php
$volunteerbarText = array();
//$words = new MOD_words();
?>

           <h3>Volunteers Actions</h3>
           <ul class="linklist">
	
<?php 
	MOD_old_bw_func::get(); // Just to have the rox mecanism to include the needed functions

	if (HasRight("Words")) {
	   echo		"<li> <a href=\"".bwlink("admin/adminwords.php")."\" title=\"Words management\">AdminWord</a></li>\n";
	}
	if (HasRight("Accepter")) {
	   echo		"<li> <a href=\"".bwlink("admin/adminaccepter.php")."\" title=\"Accepting memberst\">Accepting members</a></li>\n";
	}
?>					
           </ul>
		   