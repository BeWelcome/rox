<?php
$formkit = $this->layoutkit->formkit;
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$purifier = MOD_htmlpure::getSuggestionsHtmlPurifier();
include 'suggestionserrors.php';
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
	$vars['suggestion-post-title'] = '';
	$vars['suggestion-post-text'] = '';
}

// Show suggestion head (as on every page)
include 'suggestion.php';

// Now load the discussion board and show it
$Forums = new ForumsController;
$Forums->showExternalSuggestionsThread( $this->suggestion->id, $this->model->getGroupId(), $this->suggestion->threadId);

?>