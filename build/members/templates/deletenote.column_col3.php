
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
                echo '<div class="col-12 alert alert-danger">'.$words->get($error).'</div>';
            }
        }
        $formkit = $this->layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'deleteNoteCallback');
        ?>

    <form method="post" name="deletenote">
        <?=$callback_tag ?>

    <div class="col-12 alert alert-warning">
        <div class="my-2">
            <span><?php echo $words->get("ProfileNoteDeleteReally"); ?></span>
            <a href="mynotes" class="btn btn-sm btn-primary pull-right"><?php echo $words->getFormatted('No'); ?></a></h3>
            <input type="submit" class="btn btn-sm btn-primary pull-right" id="submit" name="submit" value="<?php echo $words->get("Yes"); ?>">
        </div>
    </div>

        <div class="col-12">
            <?php $m = $this->model->getMemberWithId($note->IdContact);
            $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();?>
            <input name="IdMember" value="<?=$member->id?>" type="hidden" />

            <table class="table table-responsive table-striped table-hover w-100">
                <thead class="blank">
                <tr>
                    <th>Member
                    </th>
                    <th>Category</th>
                    <th>Note</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php echo $layoutbits->PIC_50_50($m->Username,'',$style='float_left framed')?><br>
                        <a href="members/<?php echo $m->Username ?>" class="small"><?php echo $m->Username ?></a>
                    </td>
                    <td><?php echo $note->Category ?></td>
                    <td>
                        <?php echo $purifier->purify($note->Comment) ?>
                        <p class="small"><?php echo $note->updated ?></p><br>
                        <input type="submit" class="btn btn-sm btn-primary" id="submit" name="submit" value="<?php echo $words->get("ProfileNoteButtonDelete"); ?>">
                    </td>
                </tr>
                </tbody>
            </table>

        </div>


    </form>
    <?=$words->flushBuffer();?>