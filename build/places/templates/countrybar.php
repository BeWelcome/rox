<?php
$userbarText = array();
$words = new MOD_words();
?>
<h3><?php echo $words->get('localvolunteers'); ?></h3>

<?php

require 'localvolunteerslist.php';

if ((MOD_right::get()->HasRight('ContactLocation',$countryinfo->IdCountry)) or (MOD_right::get()->HasRight('ContactLocation','All'))) {
	echo " <a href=\"contactlocal/preparenewmessage/".$countryinfo->IdCountry."\" title=\" prepare a local volunteer message for this country\">write a local vol message</a>" ;
}
?>

<h3>WikiWiki what?</h3>
<ul class="linklist">
<li>Our regional pages consist of a wiki. You're encouraged to modify them, add new content and enrich what's already there. Learn how to edit pages <a href="wiki/GoodStyle">here</a></li>

</ul>