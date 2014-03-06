<?php
$callbackTags = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'excludeCallback');
$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');

if (empty($vars)) {
    $vars['suggestion-id'] = $this->suggestion->id;
    $vars['suggestion-summary'] = $this->suggestion->summary;
    $vars['suggestion-description'] = $this->suggestion->description;
    foreach($this->suggestion->options as $option) {
        if ($option->mutuallyExclusive != 'All') {
            foreach($option->mutuallyExclusive as $mutuallyExclusive) {
                $vars['option' . $option->id . 'option' . $mutuallyExclusive] = $mutuallyExclusive;
            }
        } else {
            foreach($this->suggestion->options as $loopOption) {
                if ($option->id < $loopOption->id) {
                    $vars['option' . $option->id . 'option' . $loopOption->id] = $loopOption->id;
                }
            }
        }
    }
} else {
}
$options = $this->suggestion->options;
uasort($options, array($this, "compareOptionIds"));
$this->suggestion->options = $options;

include 'suggestionserrors.php'; ?>
<div>
<fieldset id="suggestion-vote"><legend><?php echo $words->get('SuggestionsSetExclusions'); ?></legend>
<form method="post" id="suggestion-exclude-form">
<input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $vars['suggestion-id']; ?>" />
<?php echo $callbackTags; ?>
    <h3><?php echo $this->purifier->purify($this->suggestion->summary); ?></h3>
    <p><?php echo $words->get('SuggestionsSetExclusionsInfo'); ?>
    <hr class="suggestion" />
    <?php foreach($this->suggestion->options as $option) : ?><div class="option clearfix">
    <div class="clearfix float_left" style="width:100%">
        <p><strong><?php echo $this->purifier->purify('Option ' . $option->id .': ' . $option->summary); ?></strong></p>
        <div class="small"><?php echo $this->purifier->purify($option->description); ?></div>
        <table style="width:100%">
        <tr>
        <?php
            $name = "option" . $option->id;
            foreach($this->suggestion->options as $loopOption) :
                $disabled = $disabledStyle = '';
                if ($option->id >= $loopOption->id) :
                    $disabled = 'disabled="disabled"';
                    $disabledStyle = ' style="color: grey"';
                endif;
                echo '<td>';
                $id= $name . 'option' . $loopOption->id; ?>
                <input type="checkbox" id="<?php echo $id; ?>" <?php echo $disabled; ?> name="<?php echo $name; ?>[]" value="<?php echo $loopOption->id; ?>"
                <?php if (isset($vars[$id])) : echo 'checked="checked"'; endif; ?> />
                <label for="<?php echo $id; ?>" <?php echo $disabledStyle; ?>>Opt. <?php echo $loopOption->id; ?></label>
            <?php endforeach; ?>
        </tr>
        </table>
    <hr class="suggestion" />
    </div>
    </div>
    <?php endforeach; ?>
    <p><input type="submit" class="button" class="button float_right" name="suggestion-exclude-submit" value="<?php echo $words->getSilent('SuggestionsExcludeSubmit'); ?>" /><?php echo $words->flushBuffer(); ?></p>
</form>
</fieldset>
</div>