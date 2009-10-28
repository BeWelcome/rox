<?php
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
if ($this->loggedInMember && $this->loggedInMember->Username == $username)
{
?>
<div style="padding-top: 20px">
    <ul>
        <li><img src="images/icons/pictures.png">  <a href="gallery/show/user/<?=$username?>/images"><?=$words->get('GalleryUserImages')?></a></li>
        <li><img src="images/icons/folder_picture.png">  <a href="gallery/show/user/<?=$username?>/sets"><?=$words->get('GalleryUserPhotosets')?></a></li>
    </ul>
</div>

<?php

}

?>
