<!-- should use build/rox/templates/personalstart.leftsidebar.php instead! -->
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
<li><a href="invite"><?php echo $words->get('InviteAFriendPage') ?></a></li>
<li><a href="bw/editmyprofile.php"><?php echo $words->get('EditMyProfile') ?></a></li>
<li><a href="bw/mycontacts.php"><?php echo $words->get('DisplayAllContacts') ?></a></li>
<li><a href="volunteer"><?php echo $words->get('VolunteerpageLink') ?></a></li>
</ul>
		   