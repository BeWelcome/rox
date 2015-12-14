<?php
$layoutkit = $this->layoutkit;
$formkit = $layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('SuggestionsController', 'searchSuggestionsCallback');
$words = $layoutkit->getWords();
?><div class="subcolumns bw_row">
    <div class="c66l">
        <div class="subcl">
            <form id="suggestions-search-box" method="post">
                <?php echo $callbackTags; ?>
                <input type="text" name="suggestions-keyword" id="suggestions-keyword" value="<?= $this->keyword ?>"/><input type="submit" size="60" id="suggestions-search-button" name="suggestions-search-button" value="<?php echo $words->getSilent('SuggestionsSearchButton'); ?>" /><?php echo $words->flushBuffer(); ?>
            </form>
        </div>
    </div>
    <div class="c33r">
        <div class="subcr float_right">
        </div>
    </div>
</div>
<?php
    $state = SuggestionsModel::SUGGESTIONS_VOTING;
    include 'suggestionslist.php';
?>