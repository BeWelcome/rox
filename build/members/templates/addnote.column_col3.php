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
        echo "!mem";
        $note = $this->loggedInMember->getNote($this->member->id);
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
    }
    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('MembersController', 'addnoteCallback');
    ?>
    
    <form method="post" name="addnote">
    <?=$callback_tag ?>
    <fieldset>
    <legend><?php 
    if (!$edit_mode) {
        echo $words->get("ProfileNoteAddNote");
    } else {
        echo $words->get("ProfileNoteUpdateNote");
    }
    ?></legend>
        <input name="IdMember" value="<?=$member->id?>" type="hidden" />
        <table>
          <tr>
            <td colspan="2"><h3><?=$words->get("ProfileNoteCategory")?></h3>
          </tr>
          <tr>
            <td>
                <select name="ProfileNoteCategory" id="ProfileNoteCategory" style="font-size: 1.1em; width: 20em;">
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
            </td>
            <td rowspan="2" class="grey" style="vertical-align: top;">
                <?=$words->get("ProfileNoteCategoryInfo")?>
            </td>
          </tr>
        <tr>
          <td>
            <textarea name="ProfileNoteCategoryFree" id="ProfileNoteCategoryFree" cols="40" rows="1"><?php if (!in_array($note->CategoryFree, $categories)) { echo $note->CategoryFree; } ?></textarea>
          </td>
        </tr>
        <tr>
        <td>
        <textarea name="ProfileNoteComment" id="ProfileNoteComment" cols="40" rows="8"><?php echo $note->Comment; ?></textarea>
        </td>
        <td class="grey" style="vertical-align: top;">
        <?php echo $words->get("ProfileNoteCommentInfo") ?>
        </td>
    </tr>
    <tr><td colspan="2">
        <input type="submit" id="submit" name="submit" value="<?php echo $words->getFormatted('ProfileNoteSubmit'); ?>"></td>
    </tr>
    </table>
    </fieldset>
    </form>
    <?=$words->flushBuffer();?>
</div>
