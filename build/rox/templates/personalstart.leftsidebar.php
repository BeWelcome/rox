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

<li><a href="invite"><?=$ww->InviteAFriendPage ?></a></li>
<li><a href="bw/editmyprofile.php"><?=$ww->EditMyProfile ?></a></li>
<li><a href="bw/mycontacts.php"><?=$ww->DisplayAllContacts ?></a></li>
<li><a href="volunteer"><?=$ww->VolunteerpageLink ?></a></li>
</ul>
           