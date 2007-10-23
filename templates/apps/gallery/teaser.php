<?php
$User = APP_User::login();

$words = new MOD_words();
?>
<div id="teaser" class="clearfix">
  <div id="title">
    <h1><?php echo $words->getFormatted('GalleryTitle'); ?></h1>
  </div>
  <div id="gallery_introduction" class="note">
    <p><?php echo $words->getFormatted('GalleryIntroduction'); ?></p>
  </div>
</div>