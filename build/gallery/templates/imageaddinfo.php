<?php
$words = new MOD_words();

$d = $image;
?>

<h3 class="borderless"><?php echo $words->getFormatted('GalleryImageAdditionalInfo'); ?></h3>
<?php

echo '
<p class="small">'.$d->width.'x'.$d->height.'; '.$d->mimetype.'<br /></p>
<p class="small"><a href="gallery/img?id='.$d->id.'&amp;s=1"><img src="images/icons/disk.png" alt="'.$words->getFormatted('GalleryDownload').'" title="'.$words->getFormatted('GalleryDownload').'"/> </a> </p>';
