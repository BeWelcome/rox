<?php 
$class = "c62l";
if ($this->viewOnly & !$this->hasSuggestionRight) :
    $class = "c99l";
endif; ?>
<div id="suggestion">
<div class="floatbox">
<h2><?php echo $this->purifier->purify($this->suggestion->summary); ?></h2>
    </div>
    <div class="subcolumns">
        <div class="<?php echo $class; ?>">
            <div class="subcl">
                <div class="row">
                    <?php echo $this->purifier->purify($this->suggestion->description); ?>
                </div>
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        <? if (!$this->viewOnly | $this->hasSuggestionRight) : // this information is members only ?>
        <div class="c38r">
            <div class="subcr">
               <?php if ($this->hasSuggestionRight  && $this->suggestion->state != SuggestionsModel::SUGGESTIONS_IMPLEMENTED) :
                    $callbackStatus = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'changeStateCallback'); ?>
                <form method="POST"><?php echo $callbackStatus;
                    echo $this->getStateSelect($this->suggestion->state); ?>
                    <input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id;?>" />
                    <input type="submit" id="suggestions-submit-status" name="suggestions-submit-status" value="change" />
                </form>
               <?php endif;?>
               <h3><?php echo $words->get('SuggestionCreatedBy');?></h3>
               <div class="userinfo">
                  <div class="picbox_activities float_left">
                    <?php echo MOD_layoutbits::PIC_50_50($this->suggestion->creator->Username,'',$style='framed float_left'); ?>
                   <a class="username" href="members/<?php echo $this->suggestion->creator->Username; ?>">
                   <?php echo $this->suggestion->creator->Username; ?></a><br />
                   <span class="small"><?php echo $this->suggestion->created; ?></span><br />
                   <?php if (($this->member && $this->member->id == $this->suggestion->createdby) || ($this->hasSuggestionRight)) :?>
                   <span class="small"><a href="suggestions/<?php echo $this->suggestion->id;?>/edit"><?php echo $words->get('SuggestionsEdit'); ?></a></span>
                   <?php endif;?>
                   </div>
               </div>
               <?php if ($this->suggestion->modifier) : ?>
                   <h3><?php echo $words->get('SuggestionModifiedBy'); ?></h3>
                   <div class="userinfo">
                   <div class="picbox_activities float_left">
                   <?php echo MOD_layoutbits::PIC_50_50($this->suggestion->modifier->Username,'',$style='framed float_left'); ?>
                       <a class="username" href="members/<?php echo $this->suggestion->modifier->Username; ?>">
                       <?php echo $this->suggestion->modifier->Username; ?></a><br />
                       <span class="small"><?php echo $this->suggestion->modified; ?></span>
                   </div>
                   </div>
               <?php endif; ?>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
        <?php endif; // view only ?>
    </div> <!-- subcolums -->
</div>