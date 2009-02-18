<?php
$userbarText = array();
$words = new MOD_words();
$LayoutBits = new MOD_layoutbits();
$ToggleDonateBar = $LayoutBits->getParams('ToggleDonateBar');

if ($ToggleDonateBar) {
    // return horizontal donation bar
    require TEMPLATE_DIR.'apps/rox/userbar_donate_small.php';
} 
?>

           <h3>Actions</h3>
           <ul class="linklist">
	
<?php 
if (isset($_SESSION['thumbPathMember'])) {
	echo '<li><a href="invite">',$words->get('InviteAFriendPage'),'</a></li>' ;
}
else {
	echo '<li><a href="bw/myphotos?.php?cid='.$_SESSION['IdMember'].'">',$words->get('AddMyPicture'),'</a></li>' ;
}
echo		"<li><a href=\"bw/editmyprofile.php\">" . $words->get('EditMyProfile') . "</a></li>\n";
echo		"<li><a href=\"bw/mycontacts.php\">" . $words->get('DisplayAllContacts') . "</a></li>\n" ;
echo		"<li><a href=\"volunteer\">". $words->get('VolunteerpageLink') . "</a></li>\n";
?>					
           </ul>
		   