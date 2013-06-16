<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('SuggestionsController', 'postSuggestionCallback');
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$purifier = MOD_htmlpure::getSuggestionsHtmlPurifier();
include 'suggestionserrors.php';
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
	$vars['suggestion-post-title'] = '';
	$vars['suggestion-post-text'] = '';
}
?>
<div id="suggestion">
    <div class="floatbox">
        <h2><?php echo $this->suggestion->title; ?></h2>
    </div>
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <div class="row">
                    <h3><?= $words->get('SuggestionDescription'); ?></h3>
                    <?php echo $purifier->purify($this->suggestion->description); ?>
                </div>
                <?php if ($this->suggestion->discussions) :
                    foreach($this->suggestion->discussions as $discussion) : ?>
                        <div><?php echo "Discussion"; ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="row">
                    <form method="post" id="suggestion-post-form">
                        <?php echo $callbackTags; ?>
                        <input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id; ?>" />
						<div class="row">
							<label class="float_left"for="suggestion-post-title"><?php echo $words->get('SuggestionsPostTitle'); ?>*</label><span class="small float_right" style="margin-right: 0.3em;">* <?php echo $words->get('suggestionMandatoryFields'); ?></span><br />
							<input type="text" id="suggestion-post-title" name="suggestion-post-title" maxlength="80" class="long" style="width:99%" value="<?php echo $vars['suggestion-post-title']; ?>" />
						</div>
						<div class="subcolumns row">
							<label for="suggestion-post-text"><?php echo $words->get('suggestionDescription'); ?>*</label><br/>
							<textarea id="suggestion-post-text" name="suggestion-post-text" rows="10" cols="80" style="width:99%"><?php echo $vars['suggestion-post-text']; ?></textarea>
						</div>
						<div class="subcolumns row">
							<input type="submit" id="suggestion-post" name="suggestion-post" value="<?php echo $words->getSilent('SuggestionsSubmitApprove'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
							<input type="submit" id="suggestion-cancel" name="suggestion-cancel" value="<?php echo $words->getSilent('SuggestionsSubmitDuplicate'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
						</div>
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
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolums -->
</div>