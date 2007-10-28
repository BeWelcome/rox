<?php

/*

  This authentication plugin maps wiki actions and/or page names to the ring
  level model:
  ring 0 is for admin functionality (superuser)
  ring 1 for advanced / privileged functions (moderators)
  ring 2 are all standard/default things (editors)
  ring 3 only allows access to a small subset of the wiki (browsing only)
    
*/


$ewiki_perm_rings = array_merge(
   array(
	"view"		=> 3,
	"info"		=> 3,
	"links"		=> 3,
	"edit"		=> 2,
	"calendar"	=> 2,
	"upload"	=> 2,
	"view/SecretPage" => 1,
	"delete"	=> 1,
	"control"	=> 0,
	"admin"		=> 0,
	"*"		=> 2,	#- anything else requires this ring level
   ),
   (array)@$ewiki_perm_rings
);



$ewiki_plugins["auth_perm"][0] = "ewiki_auth_handler_ring_permissions";


function ewiki_auth_handler_ring_permissions($id, $data, $action, $required_ring) {

   global $ewiki_plugins, $ewiki_ring, $ewiki_perm_rings;

   if ("ALWAYS_DO_THIS" || ($required_ring===false)) {

      $id = strtolower($id);
      $action = strtolower($action);
		$ewiki_perm_rings = array_merge(
		array(
			"view"		=> 3,
			"info"		=> 3,
			"links"		=> 3,
			"edit"		=> 2,
			"calendar"	=> 2,
			"upload"	=> 2,
			"view/SecretPage" => 1,
			"delete"	=> 1,
			"control"	=> 0,
			"admin"		=> 0,
			"*"		=> 2,	#- anything else requires this ring level
		),
		(array)@$ewiki_perm_rings
		);

      foreach ($ewiki_perm_rings as $string => $ring) {

         $string = strtolower($string);

         if (($string == "*") ||
             ($string == $id) ||
             ($string == $action) ||
             ($string == "$action/$id") ||
             (strtok($string, "/") == $action)  )
         {
            $required_ring = $ring;
            break;
         }
 
     }

   }

   return(($required_ring===false) || isset($ewiki_ring) && ($ewiki_ring <= $required_ring));
}


?>