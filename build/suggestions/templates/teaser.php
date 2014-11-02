<div id="teaser" class="floatbox">
<div class="float_left">
<h1><a href="/suggestions"><?= $words->get('Suggestions') ?></a></h1>
</div>
<?php if (!($this->hideSearch)) { ?>
<div class="float_right abitlower">
    <form id="suggestions-search-box" method="post">
    <?php echo $callbackTags; ?>
    <input type="text" name="suggestions-keyword" id="suggestions-keyword" /><input type="submit" size="60" id="suggestions-search-button" name="suggestions-search-button" value="<?php echo $words->getSilent('SuggestionsSearchButton'); ?>" /><?php echo $words->flushBuffer(); ?>
    </form>
</div>
<?php } ?>
</div>