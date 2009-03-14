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

<h3><?=$words->get('Actions')?></h3>
<ul class="linklist">
<?php
if (isset($_SESSION['MemberHasNoPicture'])) {
	echo '<li><a href="bw/myphotos.php?cid='.$_SESSION['IdMember'].'">',$words->get('AddMyPicture'),'</a></li>' ;
}
else {
	echo '<li><a href="invite">',$words->get('InviteAFriendPage'),'</a></li>' ;
}
?>
<li><a href="bw/editmyprofile.php"><?php echo $words->get('EditMyProfile') ?></a></li>
<li><img src="images/icons/page_white_star.png" alt="" /> <a href="blog/create"><?=$words->get('Blog_CreateEntry')?></a></li>
<li><a href="bw/mycontacts.php"><?=$words->get('DisplayAllContacts')?></a></li>
<li><a href="volunteer"><?=$words->get('VolunteerpageLink')?></a></li>
</ul>
