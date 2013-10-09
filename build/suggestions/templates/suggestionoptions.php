<?php foreach ( $this->suggestion->options as $option ) :
if ((!$option->deleted) | ($this->hasSuggestionRight)) : ?>
<div class="subcolumns row">
    <h3><?php echo $this->purifier->purify($option->summary);?></h3>
</div>
<div class="subcolumns">
<?php // reuse $class variable defined in suggestion.php
// as this is always called before this file
// \todo: define a cleaner setup ?>
    <div class="<?php echo $class;?>">
        <div class="subcl">
            <div class="row">
            <?php echo $this->purifier->purify($option->description);?>
            </div>
        </div>
    </div>
<?php if (!$this->viewOnly) : ?>
    <div class="c38r">
        <div class="subcl">
            <div class="row">
                <a class="button" href="/suggestions/<?php echo $this->suggestion->id ?>/addoptions/<?php echo $option->id; ?>/edit">
                <?php echo $words->getSilent('SuggestionsSubmitEditOption'); ?></a>
                <?php if ($option->deleted) :?>
                <a class="button" href="/suggestions/<?php echo $this->suggestion->id ?>/addoptions/<?php echo $option->id; ?>/restore">
                <?php echo $words->getSilent('SuggestionsSubmitRestoreOption'); ?></a><br />
                <?php else : ?>
                <a class="button" href="/suggestions/<?php echo $this->suggestion->id ?>/addoptions/<?php echo $option->id; ?>/delete">
                <?php echo $words->getSilent('SuggestionsSubmitDeleteOption'); ?></a><br />
                <?php endif;?>
                <h4><?php echo $words->get('SuggestionOptionCreatedBy');?></h4>
                <div class="userinfo">
                    <div class="picbox_activities float_left">
                    <?php echo MOD_layoutbits::PIC_50_50($option->creator->Username,'',$style='framed float_left'); ?>
                    <a class="username" href="members/<?php echo $option->creator->Username; ?>">
                    <?php echo $option->creator->Username; ?></a><br />
                    <span class="small"><b></b></span><br /> <span class="small"><?php echo $option->created; ?></span>
                    </div>
                </div>
                <?php if ($option->deleted) :?>
                    <h4><?php echo $words->get('SuggestionOptionDeletedBy'); ?></h4>
                    <div class="userinfo">
                        <div class="picbox_activities float_left">
                        <?php echo MOD_layoutbits::PIC_50_50($option->deleter->Username,'',$style='framed float_left'); ?>
                        <a class="username" href="members/<?php echo $option->deleter->Username; ?>">
                        <?php echo $option->deleter->Username; ?></a><br />
                        <span class="small"><?php echo $option->modified; ?></span>
                        </div>
                    </div>
                    <?php else : ?>
                    <?php if ($option->modifier) : ?>
                    <h4><?php echo $words->get('SuggestionOptionModifiedBy'); ?></h4>
                    <div class="userinfo">
                        <div class="picbox_activities float_left">
                        <?php echo MOD_layoutbits::PIC_50_50($option->modifier->Username,'',$style='framed float_left'); ?>
                        <a class="username" href="members/<?php echo $option->modifier->Username; ?>">
                        <?php echo $option->modifier->Username; ?></a><br />
                        <span class="small"><?php echo $option->modified; ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                </div> <!-- subcr -->
        </div>
    </div>
<?php endif; ?>
</div>
<?php
endif;
endforeach; ?>