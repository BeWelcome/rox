<?php
$class = "c62l";
if ($this->viewOnly & !$this->hasSuggestionRight) :
    $class = "c99l";
endif; ?>
<div id="suggestion">
<h2><?php echo $this->purifier->purify($this->suggestion->summary); ?></h2>
    <div class="subcolumns bw_row">
        <div class="<?php echo $class; ?>">
            <div class="subcl">
                <div class="bw-row">
                    <?php echo $this->purifier->purify($this->suggestion->description); ?>
                </div>
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        <? if (!$this->viewOnly | $this->hasSuggestionRight) : // this information is members only ?>
        <div class="c38r">
            <div class="subcr">
               <?php if ($this->hasSuggestionRight  && $this->suggestion->state != SuggestionsModel::SUGGESTIONS_IMPLEMENTED) :
                    $callbackStatus = $this->layoutkit->formkit->setPostCallback('SuggestionsController', 'changeStateCallback'); ?>
                <form method="post"><?php echo $callbackStatus;
                    echo $this->getStateSelect($this->suggestion->state); ?>
                    <input type="hidden" id="suggestion-id" name="suggestion-id" value="<?php echo $this->suggestion->id;?>" />
                    <input type="submit" class="button" id="suggestions-submit-status" name="suggestions-submit-status" value="change" />
                </form>
               <?php endif;?>
               <div class="subcolumns">
               <div class="c50l">
               <div class="subcl">
               <h3><?php echo $words->get('SuggestionCreatedBy');?></h3>
                    <?php echo MOD_layoutbits::PIC_30_30($this->suggestion->creator->Username,'',$style='framed float_left'); ?>
                   <a class="username" href="members/<?php echo $this->suggestion->creator->Username; ?>">
                   <?php echo $this->suggestion->creator->Username; ?></a><br />
                   <span class="small"><?php echo $this->suggestion->created; ?></span><br />
                   <?php if (($this->member && $this->member->id == $this->suggestion->createdby) || ($this->hasSuggestionRight)) :?>
                   <span class="small"><a href="suggestions/<?php echo $this->suggestion->id;?>/edit"><?php echo $words->get('SuggestionsEdit'); ?></a></span>
                   <?php endif;?>
               </div>
               </div>
               <div class="c50r">
               <div class="subcr">
               <?php if ($this->suggestion->modifier) : ?>
                   <h3><?php echo $words->get('SuggestionModifiedBy'); ?></h3>
                   <?php echo MOD_layoutbits::PIC_30_30($this->suggestion->modifier->Username,'',$style='framed float_left'); ?>
                       <a class="username" href="members/<?php echo $this->suggestion->modifier->Username; ?>">
                       <?php echo $this->suggestion->modifier->Username; ?></a><br />
                       <span class="small"><?php echo $this->suggestion->modified; ?></span>
               <?php endif; ?>
               </div>
               </div>
               </div>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
        <?php endif; // view only ?>
    </div> <!-- subcolums -->
</div>
<hr class="suggestion" />