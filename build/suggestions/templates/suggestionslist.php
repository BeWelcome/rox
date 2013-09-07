<?php
if (count($this->suggestions) == 0) {
    echo "<p><strong>" . $words->get($this->NoItems) . "</strong></p>";
} else {
$this->pager->render(); ?>
<table id='suggestionlist'>
<?php
    $count= 0;
    foreach($this->suggestions as $suggestion) {
        switch ($suggestion->state) {
            case SuggestionsModel::SUGGESTIONS_AWAIT_APPROVAL:
                include 'approvelistitem.php';
            break;
            case SuggestionsModel::SUGGESTIONS_DISCUSSION:
                include 'discussionlistitem.php';
            break;
            case SuggestionsModel::SUGGESTIONS_DUPLICATE:
            case SuggestionsModel::SUGGESTIONS_REJECTED:
                include 'rejectedlistitem.php';
            break;
        }
        $count++;
    }
?>
</table>
<?php $this->pager->render();
} ?>
