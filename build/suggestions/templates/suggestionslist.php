<?php
$layoutbits = new MOD_layoutbits();
if (count($this->suggestions) == 0) {
    echo "<p><strong>" . $words->get($this->NoItems) . "</strong></p>";
} else {
$this->pager->render(); ?>
<table id='suggestionslist'>
<?php
    $count= 0;
    foreach($this->suggestions as $suggestion) {
        if (is_string($suggestion) && ($suggestion == "Implementing"))
        {
            $suggestionState = SuggestionsModel::SUGGESTIONS_IMPLEMENTING;
            $optionState = SuggestionOption::IMPLENENTING;
            continue;
        }
        if (is_string($suggestion) && ($suggestion == "Implemented"))
        {
            $suggestionState = SuggestionsModel::SUGGESTIONS_IMPLEMENTED;
            $optionState = SuggestionOption::IMPLEMENTED;
            continue;
        }
        switch ($state) {
            case SuggestionsModel::SUGGESTIONS_AWAIT_APPROVAL:
                include 'approvelistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_DISCUSSION:
                include 'openlistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_ADD_OPTIONS:
                include 'addoptionslistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_VOTING:
                include 'votelistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_RANKING:
                include 'ranklistitem.php';
                break;
                case SuggestionsModel::SUGGESTIONS_DUPLICATE:
            case SuggestionsModel::SUGGESTIONS_REJECTED:
                include 'rejectedlistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_DEV:
                include 'devlistitem.php';
                break;
        }
        $count++;
    }
?>
</table>
<?php $this->pager->render();
} ?>
