<?php
$callbackTags = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'editOptionCallback');
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
include 'suggestionserrors.php';
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['suggestion-option-id'] = $this->option->id;
    $vars['suggestion-option-summary'] = $this->option->summary;
    $vars['suggestion-option-desc'] = $this->option->description;
}

// Show suggestion head (as on every page)
include 'suggestion.php';
?>
<div id='suggestion-form'>
<form method="post" id="suggestion-addoptions-form">
<?php echo $callbackTags;
foreach($this->suggestion->options as $option) :
    if ($option->id <> $vars['suggestion-option-id']) : ?>
<div class="subcolumns row"><h3><?php echo $this->purifier->purify($option->summary);?></h3></div>
<div class="subcolumns">
<div class="c62l">
            <div class="subcl">
                <div class="row">
<?php echo $this->purifier->purify($option->description);?></div></div></div>
<div class="c38r">
<div class="subcl">
<div class="row">
<a class="button" href="/suggestions/<?php echo $this->suggestion->id ?>/addoptions/<?php echo $option->id; ?>/edit">
<?php echo $words->getSilent('SuggestionsSubmitEditOption'); ?></a>
<a class="button" href="/suggestions/<?php echo $this->suggestion->id ?>/addoptions/<?php echo $option->id; ?>/delete">
<?php echo $words->getSilent('SuggestionsSubmitDeleteOption'); ?></a>
</div>
</div>
</div>
</div>
<?php else : ?>
<div class="subcolumns row">
    <label for="suggestion-option-summary"><?php echo $words->get('suggestionEditOptionSummary'); ?>*</label><br/>
    <input type="text" id="suggestion-option-summary" name="suggestion-option-summary" maxlength="80" class="long" style="width:99%" value="<?php echo $vars['suggestion-option-summary']; ?>" />
</div>
<div class="subcolumns row">
    <label for="suggestion-option-desc"><?php echo $words->get('suggestionEditOptionDesc'); ?>*</label><br/>
    <textarea id="suggestion-option-desc" name="suggestion-option-desc" class="mce" rows="10" cols="80" style="width:99%"><?php echo $vars['suggestion-option-desc']; ?></textarea>
</div>
<div class="subcolumns row">
<div class="float_left">
    <input type="checkbox" name="suggestion-minor-edit" id="suggestion-minor-edit" value="1"> <label for="suggestion-minor-edit"><?php echo $words->get('suggestionMinorEdit'); ?></label>
</div>
<input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id; ?>" />
<input type="hidden" id="suggestion-option-id" name="suggestion-option-id" value="<?php echo $vars['suggestion-option-id']; ?>" />
<input type="submit" id="suggestion-edit-option" name="suggestion-edit-option"
    value="<?php echo $words->getSilent('SuggestionsSubmitEditOption'); ?>"
    class="submit float_right" /><?php echo $words->flushBuffer(); ?>
</div>
<hr class="suggestion" /><?php endif;
endforeach; ?>
</form>
</div><!-- suggestion-form -->
<?php
// Now load the board and show it
$Forums = new ForumsController;
$Forums->showExternalSuggestionsThread( $this->suggestion->id, $this->model->getGroupId(), $this->suggestion->threadId);
?>
