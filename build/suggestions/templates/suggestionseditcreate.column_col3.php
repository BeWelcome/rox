<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('SuggestionsController', 'editCreateSuggestionCallback');
if (isset($_SESSION['SuggestionStatus'])) {
    $status = $_SESSION['SuggestionStatus'];
    unset($_SESSION['SuggestionStatus']);
}
$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['suggestion-id'] = $this->suggestion->id;
    $vars['suggestion-summary'] = $this->suggestion->summary;
    $vars['suggestion-description'] = $this->suggestion->description;
}
?>
<div>
<fieldset id="suggestion-create"><legend><?php if ($vars['suggestion-id'] != 0) {
    echo $words->get('SuggestionsEditProblemDescription');
} else {
    echo $words->get('SuggestionsCreateProblemDescription');
} ?></legend>
<form method="post" id="suggestion-create-form">
<input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $vars['suggestion-id']; ?>" />
<?php echo $callbackTags;
if (isset($status)) {
    echo '<div class="notice">' . $words->get($status[0]) . '</div>';
}
if (!empty($errors)) {
    $errStr = '<div class="error">';
    foreach ($errors as $error) {
        $parts = explode("###", $error);
        if (count($parts) > 1) {
            $errStr .= $words->get($parts[0], $parts[1]);
        } else {
            $errStr .= $words->get($error);
        }
        $errStr .=  "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</div>';
    echo $errStr;
}
?>
    <div class="bw-row">
    <p><?php echo $words->get('SuggestionsCreateEditInfo');?></p>
        <label class="float_left"for="suggestion-summary"><?php echo $words->get('SuggestionSummary'); ?>*</label><span class="small float_right" style="margin-right: 0.3em;">* <?php echo $words->get('suggestionMandatoryFields'); ?></span><br />
        <input type="text" id="suggestion-summary" name="suggestion-summary" maxlength="80" class="long" style="width:99%" value="<?php echo htmlspecialchars($vars['suggestion-summary'], ENT_QUOTES); ?>" />
    </div>
    <div class="subcolumns bw_row">
        <label for="suggestion-description"><?php echo $words->get('suggestionDescription'); ?>*</label><br/>
        <textarea id="suggestion-description" name="suggestion-description" class="mce" rows="10" cols="80" style="width:99%"><?php echo $vars['suggestion-description']; ?></textarea>
    </div>
    <div class="subcolumns bw_row">
    <?php
        if ($vars['suggestion-id'] != 0) {
             $suggestionseditcreatebutton = $words->getSilent('SuggestionsEditCreateUpdate'); ?>
                <div class="float_left">
                    <input type="checkbox" name="suggestion-minor-edit" id="suggestion-minor-edit" value="1">
                    <label for="suggestion-minor-edit"><?php echo $words->get('suggestionMinorEdit'); ?></label>
                </div>
    <?php
        } else {
             $suggestionseditcreatebutton = $words->getSilent('SuggestionsSubmit');
        }
        ?>
        <input type="submit" class="button" id="suggestion-submit" name="suggestion-submit" value="<?php echo $suggestionseditcreatebutton; ?>" class="submit float_right" /><?php echo $words->flushBuffer(); ?>
    </div>
</form>
</fieldset>
</div>
