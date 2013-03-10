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
        $note = $this->loggedInMember->getNote($this->member->id);
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
    <form method="post" name="deletenote" class="yform">
    <?=$callback_tag ?>
    <div class="type-text">
    <h3><?php 
    echo $words->get("ProfileNoteDeleteNote");
    ?></h3>
    </div>
        <p><?php echo $words->get("ProfileNoteDeleteReally"); ?></p>
        <input name="IdMember" value="<?=$member->id?>" type="hidden" />
        <div class="type-select">
        <label><?=$words->get("ProfileNoteCategory")?></label>
            <select name="ProfileNoteCategory" disabled="disabled" id="ProfileNoteCategory">
                        <option value=""></option>
                        <?php foreach($categories as $category) {
                            $catoption = '<option value="' . $category . '"';
                            if ($category == $note->Category) {
                                $catoption .= ' selected="selected"';
                            }
                            $catoption .= '>' . $category . '</option>';
                            echo $catoption . "\n";
                        } 
                        ?>
            </select>
        </div>
        <div class="type-text">
        <label for="ProfileNoteComment"><?php echo $words->get("ProfileNoteCommentInfo") ?></label>
        <textarea name="ProfileNoteComment" id="ProfileNoteComment" cols="40" rows="8" readonly="readonly"><?php echo $note->Comment; ?></textarea>
        </div>
        <div class="type-button">
        <input type="submit" id="submit" name="submit" value="<?php echo $words->get("ProfileNoteButtonDelete"); ?>">
        </div>
    </form>
    <?=$words->flushBuffer();?>
</div>
