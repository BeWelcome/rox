<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('SuggestionsController', 'addOptionCallback');
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$purifier = MOD_htmlpure::getSuggestionsHtmlPurifier();
include 'suggestionserrors.php';
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
	$vars['suggestion-option-summary'] = '';
    $vars['suggestion-option-desc'] = '';
}
if (!isset($this->disableTinyMCE) || ($this->disableTinyMCE == 'No')) {
    $textarea = 'suggestion-option-desc';
    require_once SCRIPT_BASE . 'htdocs/script/tinymceconfig.js';
}

// Show suggestion head (as on every page)
include 'suggestion.php'; ?>
<div id='suggestion-form'>
<?php include 'suggestionoptions.php'; ?>
<form method="POST">
<?php echo $callbackTags;?>
<div class="subcolumns row">
    <label for="suggestion-option-summary"><?php echo $words->get('suggestionAddOptionSummary'); ?>*</label><br/>
    <input type="text" id="suggestion-option-summary" name="suggestion-option-summary" maxlength="80" class="long" style="width:99%" value="<?php echo $vars['suggestion-option-summary']; ?>" />
</div>
<div class="subcolumns row">
    <label for="suggestion-option-desc"><?php echo $words->get('suggestionAddOptionDesc'); ?>*</label><br/>
    <textarea id="suggestion-option-desc" name="suggestion-option-desc" rows="10" cols="80" style="width:99%"><?php echo $vars['suggestion-option-desc']; ?></textarea>
</div>
<div class="subcolumns row">
<input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id; ?>" />
<input type="submit" id="suggestion-add-option" name="suggestion-add-option"
							value="<?php echo $words->getSilent('SuggestionsSubmitAddOption'); ?>"
							class="submit" /><?php echo $words->flushBuffer(); ?>
</div>
</form>
</div><!-- suggestion-form -->
<?php
// Now load the board and show it
$Forums = new ForumsController;
$Forums->showExternalSuggestionsThread( $this->suggestion->id, $this->model->getGroupId(), $this->suggestion->threadId);
?>
