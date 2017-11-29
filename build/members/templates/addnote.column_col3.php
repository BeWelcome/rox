<script>
    $(function() {
        $('#ProfileNoteCategory').change(function(){
            $('.ProfileCategory').hide();
            $('#' + $(this).val()).show();
        });
    });
</script>
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
            echo '<div class="alert alert-danger">'.$words->get($error).'</div>';
        }
    }
    if (isset($vars['success'])) {
        echo '<div class="alert alert-success">' . $words->get('ProfileNoteSuccess');
        echo '<a href="mynotes">' . $words->get('ProfileNoteAllNotes') . '</a></div>';
        if ($note->Category == "") {
            $note->Category = $note->CategoryFree;
            $note->CategoryFree = "";
        }
    }

    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('MembersController', 'addnoteCallback');
    ?>
    
    <form method="post" name="addnote">
    <?=$callback_tag ?>
        <div class="row">
            <div class="col-12">
                <h3><?php
                if (!$edit_mode) {
                    echo $words->get("ProfileNoteAddNote");
                } else {
                    echo $words->get("ProfileNoteEditNote");
                }
                ?></h3>
            </div>

            <input name="IdMember" value="<?=$member->id?>" type="hidden" />

            <div class="col-4">
            <?=$words->get("Category")?>
            </div>
            <div class="col-8">
                <select name="ProfileNoteCategory" id="ProfileNoteCategory">
                    <option value="">---</option>
                            <?php foreach($categories as $category) {
                                $catoption = '<option value="' . $category . '"';
                                if ($category == $note->Category) {
                                    $catoption .= ' selected="selected"';
                                }
                                $catoption .= '>' . $category . '</option>';
                                echo $catoption . "\n";
                            }
                            ?>
                    <option value="new">-- add new category --</option>
                </select><?php echo $words->flushBuffer(); ?>
            </div>
            <div class="ProfileCategory col-8 offset-4" id="new" style="display: none;">
                <label for="ProfileNoteCategoryFree"><?=$words->get("ProfileNoteCategoryFree")?></label>
                <?php echo '<input name="ProfileNoteCategoryFree" id="ProfileNoteCategoryFree" value="';
                    if (!in_array($note->CategoryFree, $categories)) {
                        echo $note->CategoryFree;
                    };
                    echo '" />';
                ?>
            </div>
            <div class="col-4">
                <label for="ProfileNoteComment"><?php echo $words->get("ProfileNoteCommentInfo") ?></label>
            </div>
            <div class="col-8">
                <textarea name="ProfileNoteComment" id="ProfileNoteComment" rows="8" class="w-100"><?php echo $note->Comment; ?></textarea>
            </div>
            <div class="col-8 offset-4">
                <?php
                if ($edit_mode || isset($vars['success']) || isset($vars['errors'])) { ?>
                    <input type="submit" class="btn btn-primary" id="submit" name="submit" value="<?php echo $words->getBuffered("ProfileNoteButtonEdit") ?>" /><?=$words->flushBuffer();?>
                    <a href="/members/<?php echo $this->member->Username;?>/note/delete" class="btn btn-primary"><?php echo $words->getFormatted('ProfileNoteButtonDelete'); ?></a>
                <?php } else { ?>
                    <input type="submit" class="btn btn-primary" id="submit" name="submit" value="<?php echo $words->getBuffered("ProfileNoteButtonAdd") ?>" /><?=$words->flushBuffer();?>
                <?php } ?>
            </div>
        </div>
    </form>