<?php

$i18n = new MOD_i18n('apps/forums/editcreateform.php');
$formText = $i18n->getText('editCreateText');

$request = PRequest::get()->request;
$uri = implode('/', $request);

$vars =& PPostHandler::getVars();

?>

<script type="text/javascript" src="script/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'script/tiny_mce';
tinyMCE.init({
	mode: "exact",
	elements: "topic_text",
	theme: "advanced",
	relative_urls:false,
	convert_urls:false,
	theme_advanced_buttons1: "bold,italic,underline,strikethrough,link,bullist,separator,justifyleft,justifycenter,justifyfull,bullist,numlist,forecolor,backcolor,charmap",
	theme_advanced_buttons2: "",
	theme_advanced_buttons3: "",    
	theme_advanced_toolbar_location: 'top',
	theme_advanced_statusbar_location: 'bottom',
	theme_advanced_resizing: true
});
//-->
</script>

<form method="post" action="<?php echo $uri; ?>">
<input type="hidden" name="<?php echo $callbackId; ?>" value="1" />

<?php
	if (isset($allow_title) && $allow_title) {
?>
		<div class="row">
<?php

if (isset($vars['errors'])) {
	print_r($vars['errors']);
}

?>
		<label for="topic_title"><?php echo $formText['label_topicTitle']; ?></label><br />
		<input type="text" name="topic_title" id="topic_title" />
		</div>
<?php
	}
?>

<div class="row">
<label for="topic_text"><?php echo $formText['label_text']; ?></label><br />
<textarea name="topic_text" cols="60" rows="15" id="topic_text"></textarea>
</div>

<div class="row">
<input type="submit" value="<?php echo $formText['label_submit']; ?>" />
</div>

</form>