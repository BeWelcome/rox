    <div id="profile_notes" class="row">
        <div class="col-12">
            <h3><?= $words->get('ProfileMyNotes') ?></h3>
        </div>
        <div class="col-12 mt-3">

            <table class="table table-responsive table-striped table-hover">
                <thead class="blank">
                <tr>
                    <th>Member
                    </th>
                    <th>Category</th>
                    <th>Note</th>
                </tr>
                </thead>
                <tbody>


            <?php // display my notes, if there are any

            if (!empty($mynotes)) {
                $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();
                echo $this->pager->render();
                $left = "";
                $right = "";
                $ii = 0;
                ?>

            <?php
                foreach ($this->pager->getActiveSubset($mynotes) as $note) {
                    $m = $this->model->getMemberWithId($note->IdContact);?>

                    <tr>
                        <td>
                            <?php echo $layoutbits->PIC_50_50($m->Username,'',$style='float_left framed')?><br>
                            <a href="members/<?php echo $m->Username ?>" class="small"><?php echo $m->Username ?></a>
                        </td>
                        <td>
                            <?php if ($note->Category){
                                echo $purifier->purify($note->Category);
                            } else {
                                echo "No Category";
                            }
                                ?>
                        </td>
                        <td>
                            <?php echo $purifier->purify($note->Comment) ?>
                            <p class="small"><?php echo date($words->getSilent('DateFormatShort'),strtotime($note->updated)); ?></p>
                            <a class="btn btn-primary pull-left" role="button" href="members/<?php echo $m->Username ?>/note/edit"><?php echo $words->get('Edit') ?></a>
                            <a class="btn btn-primary pull-right" role="button" href="members/<?php echo $m->Username ?>/note/delete"><?php echo $words->get('Delete') ?></a>
                        </td>
                    </tr>

                <?php } ?>
                </tbody>
            </table>
            <?
                $this->pager->render();
            } else {
                echo $words->get("MyNotesNoNotes");
            }  ?>
        </div>
    </div>