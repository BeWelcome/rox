<?php
$callbackTags = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'approveSuggestionCallback');
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    $errStr = '<div class="error">';
    foreach ($errors as $error) {
        $errStr .= $words->get($error) . "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</div>';
    echo $errStr;
}
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
}
include 'suggestion.php';

if ($this->hasSuggestionRight) : ?>
<div id='suggestion-form'>
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <div class="bw-row">
                    <form method="post" id="suggestion-approve-form">
                        <?php echo $callbackTags; ?>
                        <input type="hidden" id="suggestion-id"
                            name="suggestion-id" value="<?php echo $this->suggestion->id; ?>" />
                        <input type="submit" class="button" id="suggestion-approve"
                            name="suggestion-approve"
                            value="<?php echo $words->getSilent('SuggestionsSubmitApprove'); ?>"
                            class="submit" /><?php echo $words->flushBuffer(); ?>
                        <input type="submit" class="button" id="suggestion-duplicate"
                            name="suggestion-duplicate"
                            value="<?php echo $words->getSilent('SuggestionsSubmitDuplicate'); ?>"
                            class="submit" /><?php echo $words->flushBuffer(); ?>
                    </form>
                </div>
            </div>
        </div><!-- subcl -->
    </div><!-- c62l -->
    <div class="c38r">
        <div class="subcr"></div><!-- c38r -->
    </div><!-- subcolums -->
</div><!-- suggestion-form -->
<?php endif; ?>