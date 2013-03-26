<div id="profile">
    <div id="profile_notes" class="floatbox box">
    <?php // display my notes, if there are any
    echo "<h3>" . $words->get('ProfileMyNotes') . "</h3>";
    if (!empty($mynotes)) {
        $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();
        echo $this->pager->render(); 
        $left = "";
        $right = "";
        $ii = 0;
        foreach ($this->pager->getActiveSubset($mynotes) as $note) {
            $m = $this->model->getMemberWithId($note->IdContact);?>
            <div class="subcolumns">
                <div class="c38l">
                    <div class="subcl">
                        <?php echo $layoutbits->PIC_50_50($m->Username,'',$style='float_left framed')?>
                        <div class="userinfo">
                        <a href="members/<?php echo $m->Username ?>" class="username"><?php echo $m->Username ?></a><br>
                        <p class="small"><?php echo date($words->getSilent('DateFormatShort'),strtotime($note->updated)); ?></p>
                        <p><a class="button" href="members/<?php echo $m->Username ?>/note/edit"><?php echo $words->get('Edit') ?></a>&nbsp;&nbsp; <a class="button" href="members/<?php echo $m->Username ?>/note/delete"><?php echo $words->get('Delete') ?></a></p>
                        </div>
                    </div>
                </div>
                <div class="c62r">
                    <div class="subcr">
                       <?php if (!$note->Category){ ?>
                        <p>&nbsp;</p>    
                       <?php } else { ?>
                        <div class="notecategory"><b><?php echo $purifier->purify($note->Category) ?></b></div>
                        <?php } ?>
                        <div class="notecomment"><?php echo $purifier->purify($note->Comment) ?></div>

                    </div>
                </div>
            </div>
            <hr>
       <?php } 
        $this->pager->render(); 
    } else {
        echo $words->get("MyNotesNoNotes");
    }  ?>
    </div> <!-- profile_groups -->
</div> <!-- profile -->
