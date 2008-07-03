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
    <h2>'.$username.'</h2><p>'.$cnt_pictures.' '.$words->getFormatted('Images').'</p>
    </div>';
    ?>
<h3 class="borderless"><?php echo $words->getFormatted('GalleryImageSetGallery'); ?></h3>


<?php
//	if ($isOwnGallery) {
?>
<script type="text/javascript">

Sortable.create('triplist', {
	onUpdate:function(){
		new Ajax.Updater('list-info', 'trip/reorder/', {
			onComplete:function(request){
				new Effect.Highlight('triplist',{});
				params = Sortable.serialize('triplist').toQueryParams();
				points = Object.values(params).toString().split(',');
				setPolyline();
				
			}, 
			parameters:Sortable.serialize('triplist'), 
			evalScripts:true, 
			asynchronous:true,
			method: 'get'
		})
	}
})</script>

<?php
//} // end if is own trip


if ($User && $User->getId() == APP_User::userId($username)) {

?>

<form method="post" action="gallery/show/image/<?=$d->id?>/edit" class="def-form">
    <fieldset id="image-edit" class="inline" style="display:none;">
    <legend><?php echo $words->getFormatted('GalleryTitleEdit'); ?></legend>
    
        <div class="row">
            <label for="image-edit-t"><?php echo $words->getFormatted('GalleryLabelTitle'); ?></label><br/>
<?php
echo '<select name="Gallery" size="1">';
    foreach ($galleries as $d) {
    	echo '<option value="'.$d->id.'">'.$d->title.'</option>';
    }
echo '</select>';
?>
	        <input type="hidden" name="<?php //echo $callbackId; ?>" value="1"/>
	        <input type="hidden" name="id" value="<?=$d->id?>"/>
            <p class="desc"><?php echo $words->getFormatted('GalleryDescTitle'); ?></p>
            <input type="submit" name="button" value="submit" id="button" />
        </div>
        <div class="row">
        </div>    
</fieldset>
</form>

<?php

}

?>
