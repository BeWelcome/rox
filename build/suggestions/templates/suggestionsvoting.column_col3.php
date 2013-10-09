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
    if (count($this->votes) == 0) {
        $votes = array();
        foreach($this->suggestion->options as $option) {
            $vars['option' . $option->id . 'rank'] = 0;
            $vote = new StdClass;
            $vote->rank = 0;
            $votes[$option->id] = $vote;
        }
        $this->votes = $votes;
    } else {
        foreach($this->votes as $key => $value) {
            $vars['option' . $key . 'rank'] = $value->rank;
        }
    }
} else {
}
include 'suggestionserrors.php';
?>
<div>
<fieldset id="suggestion-vote"><legend><?php echo $words->get('SuggestionsVote'); ?></legend>
<form method="post" id="suggestion-vote-form">
<input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $vars['suggestion-id']; ?>" />
<?php echo $callbackTags; 
$votingends = date('Y-m-d', strtotime($this->suggestion->laststatechanged) + SuggestionsModel::DURATION_VOTING) ?>
    <h3><?php echo $this->purifier->purify($this->suggestion->summary . " (" .  $words->get('SuggestionsVoteEnds', $votingends) .  ")"); ?></h3>
    <p><?php echo $this->purifier->purify($this->suggestion->description); ?></p>
    <hr />
    <?php foreach($this->suggestion->options as $option) : ?><div class="option floatbox">
    <div class="floatbox float_left"><p><strong><?php echo $this->purifier->purify($option->summary); ?></strong></p><p><?php echo $this->purifier->purify($option->description); ?></p></div>
    <div class="vote floatbox float_right"><?php foreach($ranks as $key => $rank) :
        $name = "option" . $option->id . 'rank'; $id= $name . $rank; ?><input type="radio" class="toggle"
        <?php if ($key == $this->votes[$option->id]->rank) { echo 'checked="checked"'; } ?> id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo $key; ?>"/><label for="<?php echo $id; ?>"><?php echo $rank; ?></label>
    <?php endforeach; ?>
        </div></div><hr />
    <?php endforeach; ?>
    <p style="padding-top: 1em;"><?php echo $words->get('SuggestionsVoteHint', date('d.m.Y', $this->suggestion->votingendts));?></p>
    <p><input type="submit" class="button float_right" name="suggestion-vote-submit" value="<?php echo $words->getSilent('SuggestionsVoteSubmit'); ?>" /><?php echo $words->flushBuffer(); ?></p>
</form>
</fieldset>
</div>
