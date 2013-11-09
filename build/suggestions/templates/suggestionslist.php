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
        switch ($suggestion->state) {
            case SuggestionsModel::SUGGESTIONS_AWAIT_APPROVAL:
                include 'approvelistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_DISCUSSION:
            case SuggestionsModel::SUGGESTIONS_ADD_OPTIONS:
            case SuggestionsModel::SUGGESTIONS_VOTING:
                include 'openlistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_RANKING:
                include 'ranklistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_DUPLICATE:
            case SuggestionsModel::SUGGESTIONS_REJECTED:
                include 'rejectedlistitem.php';
                break;
            case SuggestionsModel::SUGGESTIONS_IMPLEMENTING:
            case SuggestionsModel::SUGGESTIONS_IMPLEMENTED:
                include 'devlistitem.php';
                break;
        }
        $count++;
    }
?>
</table>
<?php $this->pager->render();
} ?>
