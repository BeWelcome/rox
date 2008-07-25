<?php
$User = APP_User::login();
$Gallery = new Gallery;
//$callbackId = $Gallery->editGalleryProcess($image);
$i18n = new MOD_i18n('date.php');
$format = $i18n->getText('format');
$words = new MOD_words();

?>

<?php
echo '
    <div class="floatbox">
        '.MOD_layoutbits::PIC_50_50($username,'',$style='float_left framed').'
        <h2>'.$username.'</h2>
        <p>'.$cnt_pictures.' '.$words->getFormatted('Images').'</p>
    </div>';
?>

<?php    
if ($User && $User->getId() == APP_User::userId($username)) {
?>
<div style="padding-top: 20px">
    <ul>
        <li><img src="images/icons/pictures.png">  <a href="gallery/show/user/<?=$username?>/images">Manage images</a></li>
        <li><img src="images/icons/folder_picture.png">  <a href="gallery/show/user/<?=$username?>/galleries">Manage galleries</a></li>
    </ul>
</div>

<?php

}

?>
