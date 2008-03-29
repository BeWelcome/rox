<?php
$words = new MOD_words();
$Gallery = new Gallery;
$User = new APP_User;

// Show the galleries/photosets
if ($galleries) {
	static $ii = 0;
    echo '<div class="floatbox">';
    foreach ($galleries as $g) {
        $d = $Gallery->getLatestGalleryItem($g->id);
        $s = $Gallery->getGalleryItems($g->id);
        $num_rows = count($s);
        // Only show the galleries with pictures. The belonging user might see them anyway.
    	if ($d) {
            $bg = array(0 => "", 1 => "2",1 => "3",1 => "4");
            //Function to use an alternating background: url(images/misc/gallery_bg'.$bg[($ii++%2)].'.jpg) no-repeat
            echo '<div class="gallery_container float_left" style="margin: 10px; width: 150px; padding: 20px; text-align: center; background: #f5f5f5;">
                <a href="gallery/show/galleries/'.$g->id.'"><img class="framed" src="gallery/thumbimg?id='.$d.'" style="float:none;" alt="image"/></a>
                ';
            echo '<h4><a href="gallery/show/galleries/'.$g->id.'">'.$g->title.'</a></h4>
            <p class="small">';
            if ($g->text) echo $g->text.'<br />';
            echo $num_rows.' pictures
            </p>
            ';
            echo '</div>';
        } else {
            if ($User->id == $g->user_id_foreign) {
            echo '<div class="gallery_container float_left" style="margin: 10px; height: 107px; width: 150px; padding: 5px 0 0 5px;) no-repeat;">';
            echo '<h4><a href="gallery/show/galleries/'.$g->id.'">'.$g->title.'</a></h4>
            <p class="small">'.$g->text.'</p>';
            echo '</div>';
            }
        }

    }
echo '</div>';
}
?>
