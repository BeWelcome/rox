<?php
$callbackAddOptionTags = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'addOptionToSuggestionCallback');
$callbackAddPostTags = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'postSuggestionCallback');
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$errors = $this->getRedirectedMem('errors');
include 'suggestionerrors.php';
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
}
?>
<div id="suggestion">
    <div class="clearfix">
        <h2><?php echo $this->suggestion->title; ?></h2>
    </div>
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <div class="bw-row">
                    <h3><?= $words->get('SuggestionDescription'); ?></h3>
                    <?php echo $purifier->purify($this->suggestion->description); ?>
                </div>
                <?php if ($this->suggestion->options) :
                    foreach($this->suggestion->options as $option) : ?>
                        <div><?php echo "Option"; ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="bw-row">
                    <form method="post" id="suggestion-addoptions-form">
                        <?php echo $callbackAddOptionsTags; ?>
                        <input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id; ?>" />
                        <input type="submit" class="button" id="suggestion-add-option" name="suggestion-add-option" value="<?php echo $words->getSilent('SuggestionsSubmitAddOption'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
                        <input type="submit" class="button" id="suggestion-cancel" name="suggestion-cancel" value="<?php echo $words->getSilent('SuggestionsSubmitCancel'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
                    </form>
                </div>
                <?php if ($this->suggestion->discussions) :
                    foreach($this->suggestion->discussions as $discussion) : ?>
                        <div><?php echo "Discussion"; ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="bw-row">
                    <form method="post" id="suggestion-post-form">
                        <?php echo $callbackTags; ?>
                        <input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id; ?>" />
                        <input type="submit" class="button" id="suggestion-approve" name="suggestion-approve" value="<?php echo $words->getSilent('SuggestionsSubmitApprove'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
                        <input type="submit" class="button" id="suggestion-duplicate" name="suggestion-duplicate" value="<?php echo $words->getSilent('SuggestionsSubmitDuplicate'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
                    </form>
                </div>
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        <div class="c38r">
            <div class="subcr">
               <h3><?php echo $words->get('SuggestionCreatedBy');?></h3>
               <div class="userinfo">
                  <div class="picbox_activities float_left">
                    <?php echo MOD_layoutbits::PIC_50_50($this->suggestion->creator->Username,'',$style='framed float_left'); ?>
                   <a class="username" href="members/<?php echo $this->suggestion->creator->Username; ?>">
                   <?php echo $this->suggestion->creator->Username; ?></a><br />
                   <span class="small"><b></b></span><br />
                   <span class="small"><?php echo $this->suggestion->created; ?></span>
                   </div>
               </div>
               <?php if ($this->suggestion->modifier) : ?>
                   <h3><?php echo $words->get('SuggestionModifiedBy'); ?></h3>
                   <div class="userinfo">
                   <div class="picbox_activities float_left">
                   <?php echo MOD_layoutbits::PIC_50_50($this->suggestion->modifier->Username,'',$style='framed float_left'); ?>
                       <a class="username" href="members/<?php echo $this->suggestion->modifier->Username; ?>">
                       <?php echo $this->suggestion->modifier->Username; ?></a><br />
                       <span class="small"><b></b></span><br />
                       <span class="small"><?php echo $this->suggestion->modified; ?></span>
                   </div>
                   </div>
               <?php endif; ?>
               <?php if ($this->suggestion->options) : ?>
                   <h3><?php echo $words->get('SuggestionNumberOfOptions', count($this->suggestion->options)); ?></h3>
               <?php endif; ?>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolums -->
</div>