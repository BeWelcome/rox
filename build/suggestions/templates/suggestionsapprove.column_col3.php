<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('SuggestionsController', 'approveSuggestionCallback');
$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$purifier = MOD_htmlpure::getSuggestionsHtmlPurifier();
$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    $errStr = '<div class="error">';
    foreach ($errors as $error) {
        $errStr .= $words->get($error) . "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</div>';
    echo $errStr;
}
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
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
                <?php if ($this->hasSuggestionsRight) : ?>
                <div class="row">
                    <form method="post" id="suggestion-approve-form">
                        <?php echo $callbackTags; ?>
                        <input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id; ?>" />
                        <input type="submit" id="suggestion-approve" name="suggestion-approve" value="<?php echo $words->getSilent('SuggestionsSubmitApprove'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
                        <input type="submit" id="suggestion-duplicate" name="suggestion-duplicate" value="<?php echo $words->getSilent('SuggestionsSubmitDuplicate'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
                    </form>
                </div>
                <?php endif; ?>
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