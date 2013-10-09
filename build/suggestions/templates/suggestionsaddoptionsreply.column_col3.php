<?php
$callbackTags = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'addOptionCallback');
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
include 'suggestionserrors.php';
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['suggestion-post-title'] = '';
    $vars['suggestion-post-text'] = '';
}

// Show suggestion head (as on every page)
include 'suggestion.php'; ?>
<div id='suggestion-form'>
<?php include 'suggestionoptions.php'; ?>
</div><!-- suggestion-form -->
<?php
// Now load the board and show it
$Forums = new ForumsController;
$Forums->showExternalSuggestionsThreadReply( $this->suggestion->id, $this->model->getGroupId(), $this->suggestion->threadId, 'addoptions');

?>