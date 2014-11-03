<?php
$ranks = array(
        4 => $words->getSilent('SuggestionsExcellent'),
        3 => $words->getSilent('SuggestionsGood'),
        2 => $words->getSilent('SuggestionsFair'),
        1 => $words->getSilent('SuggestionsPoor')
    );
$callbackTags = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'voteSuggestionCallback');
$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');

if (empty($vars)) {
    $vars['suggestion-id'] = $this->suggestion->id;
    $vars['suggestion-summary'] = $this->suggestion->summary;
    $vars['suggestion-description'] = $this->suggestion->description;
    if (count($this->suggestion->memberVotes) == 0) {
        $votes = array();
        foreach($this->suggestion->options as $option) {
            $vars['option' . $option->id . 'rank'] = 0;
            $vote = new StdClass;
            $vote->rank = 0;
            $votes[$option->id] = $vote;
        }
        $this->suggestion->memberVotes = $votes;
    } else {
        // sort options in the order of the votes (excellent, good, acceptable, bad)
        $options = $this->suggestion->options;
        uasort($options, array($this, "compareRanks"));
        $this->suggestion->options = $options;

        foreach($this->suggestion->memberVotes as $key => $value) {
            $vars['option' . $key . 'rank'] = $value->rank;
        }
    }
} else {
}
include 'suggestionserrors.php'; ?>
<div>
<fieldset id="suggestion-vote"><legend><?php echo $words->get('SuggestionsVote'); ?></legend>
<form method="post" id="suggestion-vote-form">
<input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $vars['suggestion-id']; ?>" />
<?php echo $callbackTags; ?>
    <h3><?php echo $this->purifier->purify($this->suggestion->summary . " (" .  $words->get('SuggestionsVoteEnds', $this->suggestion->nextstatechange) .  ")"); ?></h3>
    <p><?php echo $this->purifier->purify($this->suggestion->description); ?></p>
    <?php if (!$this->viewOnly) : ?>
        <p><?php echo $words->get('SuggestionsVoteDiscussion', '<a href="/groups/' . SuggestionsModel::getGroupId() . '/forum/s' . $this->suggestion->threadId . '">', '</a>'); ?></p>
    <?php endif; ?>
    <hr class="suggestion" />
    <?php foreach($this->suggestion->options as $option) : ?><div class="option clearfix">
    <div class="clearfix float_left">
        <p><strong><?php echo $this->purifier->purify($option->summary); ?></strong></p>
        <p><?php echo $this->purifier->purify($option->description); ?></p></div>
        <?php if (!$this->viewOnly) : ?>
            <div class="vote clearfix float_right">
            <?php foreach($ranks as $key => $rank) :
                $name = "option" . $option->id . 'rank'; $id= $name . $rank; ?>
                <input type="radio" class="toggle" <?php if ($key == $this->suggestion->memberVotes[$option->id]->rank) :
                    echo 'checked="checked"';
                    endif;?> id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo $key; ?>"/>
                <label for="<?php echo $id; ?>"><?php echo $rank; ?></label>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div><hr class="suggestion" />
    <?php endforeach;
    if (!$this->viewOnly) : ?>
    <p style="padding-top: 1em;"><?php echo $words->get('SuggestionsVoteHint', $this->suggestion->nextstatechange);?></p>
    <p><input type="submit" class="button" class="button float_right" name="suggestion-vote-submit" value="<?php echo $words->getSilent('SuggestionsVoteSubmit'); ?>" /><?php echo $words->flushBuffer(); ?></p>
    <?php endif; ?>
</form>
</fieldset>
<?php if ($this->hasSuggestionRight) :
    $callbackStatus = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'changeStateCallback'); ?>
<form method="post"><?php echo $callbackStatus;
    echo $this->getStateSelect($this->suggestion->state); ?>
    <input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id;?>" />
    <input type="submit" class="button" id="suggestions-submit-status" name="suggestions-submit-status" value="change" />
</form>
<?php endif;?>
</div>
<script type="text/javascript">
jQuery.noConflict();
jQuery('.no-checkedselector').on('change', 'input[type="radio"].toggle', function () {
    if (this.checked) {
    	jQuery('input[name="' + this.name + '"].checked').removeClass('checked');
    	jQuery(this).addClass('checked');
        // Force IE 8 to update the sibling selector immediately by
        // toggling a class on a parent element
        jQuery('.toggle-container').addClass('xyz').removeClass('xyz');
    }
});
jQuery('.no-checkedselector input[type="radio"].toggle:checked').addClass('checked');
</script>