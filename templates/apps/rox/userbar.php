<?php
$userbarText = array();
$words = new MOD_words();
?>

           <h3>Actions</h3>
           <ul class="linklist">
	
<?php 
echo		"<li><a href=\"bw/inviteafriend.php\">" . $words->get('InviteAFriendPage') . "</a></li>\n";
echo		"<li><a href=\"bw/editmyprofile.php\">" . $words->get('EditMyProfile') . "</a></li>\n";
echo		"<li><a href=\"bw/mycontacts.php\">" . $words->get('DisplayAllContacts') . "</a></li>\n" ;
echo		"<li><a href=\"volunteer\">". $words->get('VolunteerpageLink') . "</a></li>\n";
?>					
           </ul>
		   