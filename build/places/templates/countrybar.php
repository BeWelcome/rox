<?php
$userbarText = array();
$words = new MOD_words();
?>

<?php /*  local volunteers disabled for now */
/*
<h3><?php echo $words->get('localvolunteers'); ?></h3>

<?php
require 'localvolunteerslist.php';
if ((MOD_right::get()->HasRight('ContactLocation',$countryinfo->IdCountry)) or (MOD_right::get()->HasRight('ContactLocation','All'))) {
?>
   <p><a href="contactlocal/preparenewmessage/<?php echo $countryinfo->IdCountry ?>" title="<?php echo $words->get('PrepareLocalMessageTitle'); ?>"> <?php echo $words->get('PrepareLocalMessage'); ?></a></p>
<?php
}
?>
*/ ?>

<h3><?php echo $words->get('PlacesSidebarHeader'); ?></h3>
<p><?php echo $words->get('PlacesSidebarText'); ?></p>


