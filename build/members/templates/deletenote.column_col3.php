<div id="profile">
    <div id="profile_notes" class="clearfix box">
    <?php
        $vars = array();
        $words = $this->words;
        $layoutbits = new MOD_layoutbits();
        
        $member = $this->member;
        $Username = $member->Username;
        $edit_mode = false;
        $categories = $this->loggedInMember->getNoteCategories();
        $mem_redirect = $this->layoutkit->formkit->getMemFromRedirect();
        if (!$mem_redirect) {
            $note = $this->loggedInMember->getNote($this->member);
            if (!isset($note)) {
                $vars['errors'] = 'ProfileNoteDeleteDoesntExist';
            }
        } else {
            $vars = $mem_redirect->post;
            $note = new StdClass;
            $note->Category = $vars['ProfileNoteCategory'];
            $note->CategoryFree = $vars['ProfileNoteCategoryFree'];
            $note->Comment = $vars['ProfileNoteComment'];
        }
    
        // Display errors from last submit	
        if (isset($vars['errors']))
        {
            foreach ($vars['errors'] as $error)
            {
                echo '<div class="error">'.$words->get($error).'</div>';
            }
        }
        $formkit = $this->layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'deleteNoteCallback');
        ?>
    <form method="post" name="deletenote">
    <?=$callback_tag ?>
    <div class="type-text">
    <h3><?php 
    echo $words->get("ProfileNoteDeleteNote");
    ?></h3>
    </div>
        <p class="flash notice"><?php echo $words->get("ProfileNoteDeleteReally"); ?></p>
            <?php $m = $this->model->getMemberWithId($note->IdContact);
            $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();?>
            <input name="IdMember" value="<?=$member->id?>" type="hidden" />
            <div class="bw-row"></div>
            <div class="subcolumns">
                <div class="c33l">
                    <div class="subcl">
                        <?php echo $layoutbits->PIC_50_50($m->Username,'',$style='float_left framed')?>
                        <div class="userinfo">
                        <a href="members/<?php echo $m->Username ?>" class="username"><?php echo $m->Username ?></a><br>
                        <p class="small"><?php echo $note->updated ?></p>
                        </div>
                    </div>
                </div>
                <div class="c66r">
                    <div class="subcr">
                    <div class="notecategory"><b><?php echo $note->Category ?></b></div>
                    <div class="notecomment"><?php echo $purifier->purify($note->Comment) ?></div>

                    </div>
                </div>
            </div>
        <div class="type-button">
        <input type="submit" class="button" id="submit" name="submit" value="<?php echo $words->get("ProfileNoteButtonDelete"); ?>">
        <a href="mynotes" class="button back"><?php echo $words->getFormatted('ProfileNoteButtonBack'); ?></a>
        </div> 
    </form>
    <?=$words->flushBuffer();?>
</div>
</div>