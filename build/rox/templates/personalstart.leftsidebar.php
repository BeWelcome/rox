<?php
$userbarText = array();
$words = new MOD_words();
$LayoutBits = new MOD_layoutbits();
$ToggleDonateBar = $LayoutBits->getParams('ToggleDonateBar');

if ($ToggleDonateBar) {
    // return horizontal donation bar
    $this->donatebar();
} 
?>
<h3>Actions</h3>
<ul class="linklist">

<!-- new suggestion for left column (globetrotter_tt) -->
<li><a href="people">My Profile</a></li>
<li><a href="messages">My Messages</a></li>
<li><a href="#">My Contacts</a></li>
<li><a href="#">My Groups</a></li>
<li><a href="#">My Photos</a></li>
<li><a href="#">My Trips</a></li>
<li><a href="#">My Blog</a></li>
<li><a href="#">My Photos</a></li>
<li><a href="#">My Settings</a></li>


<!-- old col1 
<li><a href="invite"><?=$ww->InviteAFriendPage ?></a></li>
<li><a href="bw/editmyprofile.php"><?=$ww->EditMyProfile ?></a></li>
<li><a href="bw/mycontacts.php"><?=$ww->DisplayAllContacts ?></a></li>
<li><a href="volunteer"><?=$ww->VolunteerpageLink ?></a></li>
-->

</ul>
           
