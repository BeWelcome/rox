<?php
$User = APP_User::login();
$request = PRequest::get()->request;
$words = new MOD_words();
?>
<div id="teaser" class="clearfix">

            <?php 
    		$request = PRequest::get()->request;
            if (isset ($request[2])) {
            echo '<h1>';
            echo '<a href="gallery">'.$words->getFormatted('GalleryTitle').'</a> ';
            if (($request[2]== 'user') && preg_match(User::HANDLE_PREGEXP, $request[3])) {
                echo '<span class="small">';
                echo ' > <a href="gallery/show/user/'.$request[3].'">'.$request[3].'\'s photos</a>';
                echo '</span>';
            } elseif (($request[2]== 'sets' || $request[2]== 'galleries')&& !isset($request[3])) {
                echo '<span class="small">';
                echo ' > <a href="gallery/show/sets">'.$words->getFormatted("Photosets").'</a>';
                echo '</span>';
            } elseif (($request[2]== 'sets' || $request[2]== 'galleries') && isset($request[3])) {
                echo '<span class="small">';
                echo ' > <a href="gallery/show/sets">'.$words->getFormatted("Photosets").'</a> > <a href="gallery/show/sets/'.$request[3].'">'.$name.'</a>';
                echo '</span>';
            }
            echo '</h1>'; 
            } else {
            ?>
  
      <div id="title">
        <h1><?php echo $words->getFormatted('GalleryTitle'); ?></h1>
      </div>
        <?php }?>
</div>