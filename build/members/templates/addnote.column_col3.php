<div>
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
        $edit_mode = isset($note);
        if (!$edit_mode) {
            $note = new StdClass;
            $note->Category = "";
            $note->CategoryFree = "";
            $note->Comment = "";
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
    if (isset($vars['success'])) {
        echo '<div class="success">' . $words->get('ProfileNoteSuccess') . '<br />';
        echo '<a href="mynotes">' . $words->get('ProfileNoteAllNotes') . '</a></div>';
        if ($note->Category == "") {
            $note->Category = $note->CategoryFree;
            $note->CategoryFree = "";
        }
    }

    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('MembersController', 'addnoteCallback');
    ?>
    
    <form method="post" name="addnote" class="yform full">
    <?=$callback_tag ?>
    <div class="type-text">
    <h3><?php 
    if (!$edit_mode) {
        echo $words->get("ProfileNoteAddNote");
    } else {
        echo $words->get("ProfileNoteEditNote");
    }
    ?></h3>
    </div>
        <input name="IdMember" value="<?=$member->id?>" type="hidden" />
        <div class="type-select">
        <label><?=$words->get("ProfileNoteCategory")?></label>
            <select name="ProfileNoteCategory" id="ProfileNoteCategory">
                        <option value="">-<?php echo $words->getBuffered('ProfileNoteCategory');?>-</option>
                        <?php foreach($categories as $category) {
                            $catoption = '<option value="' . $category . '"';
                            if ($category == $note->Category) {
                                $catoption .= ' selected="selected"';
                            }
                            $catoption .= '>' . $category . '</option>';
                            echo $catoption . "\n";
                        } 
                        ?>
            </select><?php $words->flushBuffer(); ?>
            
        </div>
        <div class="type-text">
            <label for="ProfileNoteCategoryFree"><?=$words->get("ProfileNoteCategoryFree")?></label>
            <?php echo '<input name="ProfileNoteCategoryFree" id="ProfileNoteCategoryFree" value="';
                if (!in_array($note->CategoryFree, $categories)) { 
                    echo $note->CategoryFree;
                };
                echo '" />';
            ?>
        </div>
        <div class="type-text">
        <label for="ProfileNoteComment"><?php echo $words->get("ProfileNoteCommentInfo") ?></label>
        <textarea name="ProfileNoteComment" id="ProfileNoteComment" cols="40" rows="8"><?php echo $note->Comment; ?></textarea>
        </div>
        <div class="type-button">
        <?php 
        if ($edit_mode || isset($vars['success']) || isset($vars['errors'])) { ?>
            <input type="submit" id="submit" name="submit" value="<?php echo $words->getBuffered("ProfileNoteButtonEdit") ?>" /><?=$words->flushBuffer();?>
            <a href="/members/<?php echo $this->member->Username;?>/note/delete" class="button"><?php echo $words->getFormatted('ProfileNoteButtonDelete'); ?></a>
  <?php } else { ?>
            <input type="submit" id="submit" name="submit" value="<?php echo $words->getBuffered("ProfileNoteButtonAdd") ?>" /><?=$words->flushBuffer();?>
        <?php } ?>
        </div>
    </form>
</div>
