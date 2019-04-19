<div class="row">
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
            echo '<div class="col-12"><div class="alert alert-danger">'.$words->get($error).'</div></div>';
        }
    }
    if (isset($vars['success'])) {
        echo '<div class="col-12"><div class="alert alert-success">' . $words->get('ProfileNoteSuccess') . ' ';
        echo '<a href="mynotes">' . $words->get('ProfileNoteAllNotes') . '</a></div>';
        if ($note->Category == "") {
            $note->Category = $note->CategoryFree;
            $note->CategoryFree = "";
        }
    }

    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('MembersController', 'addnoteCallback');
    ?>
    
            <div class="col-12">
                <h3><?php
                if (!$edit_mode) {
                    echo $words->get("ProfileNoteAddNote");
                } else {
                    echo $words->get("ProfileNoteEditNote");
                }
                ?></h3>
            </div>

            <div class="col-12">
            <form method="post" name="addnote">
                <?=$callback_tag ?>
            <input name="IdMember" value="<?=$member->id?>" type="hidden" />

            <div class="form-group row">
                <label class="col-12 col-sm-4 col-lg-2 col-form-label" for="ProfileNoteCategory"><?=$words->get("Category")?></label>
                <select id="ProfileNoteCategory" name="ProfileNoteCategory" class="form-control select2 col-12 col-sm-8 col-lg-10 mb-2">
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
                </select><?php echo $words->flushBuffer(); ?>
            </div>
            <div class="form-group row ProfileCategory">
                <label class="col-12 col-sm-4 col-lg-2 col-form-label" for="ProfileNoteCategoryFree"><?=$words->get("ProfileNoteCategoryFree")?></label>
                <?php echo '<input name="ProfileNoteCategoryFree" id="ProfileNoteCategoryFree" class="col-12 col-sm-8 col-lg-10 form-control" value="';
                    if (!in_array($note->CategoryFree, $categories)) {
                        echo $note->CategoryFree;
                    };
                    echo '" />';
                ?>
            </div>
            <div class="form-group row">
                <label class="col-12 col-sm-4 col-lg-2 col-form-label" for="ProfileNoteComment"><?php echo $words->get("ProfileNoteCommentInfo") ?></label>
                <textarea name="ProfileNoteComment" id="ProfileNoteComment" rows="4" class="col-12 col-sm-8 col-lg-10 mb-2 form-control"><?php echo $note->Comment; ?></textarea>
            </div>
                <div class="form-group row">
                    <div class="offset-sm-4 offset-lg-2">
                    <?php
                    if ($edit_mode || isset($vars['success']) || isset($vars['errors'])) { ?>
                        <input type="submit" class="btn btn-primary" id="submit" name="submit" value="<?php echo $words->getBuffered("ProfileNoteButtonEdit") ?>" /><?=$words->flushBuffer();?>
                        <a href="/members/<?php echo $this->member->Username;?>/note/delete" class="btn btn-primary"><?php echo $words->getFormatted('ProfileNoteButtonDelete'); ?></a>
                    <?php } else { ?>
                        <input type="submit" class="btn btn-primary" id="submit" name="submit" value="<?php echo $words->getBuffered("ProfileNoteButtonAdd") ?>" /><?=$words->flushBuffer();?>
                    <?php } ?>
                    </div>
            </div>
        </div>
    </form>
</div>