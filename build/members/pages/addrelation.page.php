<?php


class AddRelationPage extends RelationsPage
{    
    protected function getSubmenuActiveItem()
    {
        return 'relationsadd';
    }

    protected function column_col3()
    {
        $words = new MOD_words();
        $member = $this->member;
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'RelationCallback');
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request); 
        $TabRelationsType = $member->get_TabRelationsType();
        $relation = $this->model->get_relation_between_members($member->id);
            if (isset($relation['member']->Confirmed) && $relation['member']->Confirmed == 'No') {
               $action = 'confirm';
            } elseif (isset($relation['myself']->id)) {
               $action = 'update';
            } else {
               $action = 'add';
            }        ?>

<div class="row">
            <div class="col-12">
         <? if ($action == 'update' && isset($relation['member']->Confirmed)) : ?>
            <div class="alert alert-success"><?=$words->get('RelationIsConfirmed',$member->Username)?></div>
         <? elseif ($action == 'update') : ?>
            <div class="alert alert-info"><?=$words->get('RelationWaitConfirmed',$member->Username)?></div>
         <? endif ?>
        <form method="post" action="<?=$page_url?>" name="relation" id="relation" enctype="multipart/form-data">
        <fieldset>
            <input type="hidden"  name="IdRelation"  value="<?=$member->id?>" />
            <input type="hidden"  name="IdOwner"  value="<?=$this->_session->get('IdMember')?>" />
            <?=$callback_tag?>
            <legend><?=$words->get($action.'Relation')?></legend>
            <p class="small"><?=$words->get('MyRelationListExplanation',$member->Username,$member->Username)?></p>
            <? if (count($relation['member']) <= 0) : ?>
            <div>
            <label class="grey"><?=$words->get('RelationListCategory')?></label><br />
            <?php
                $tt=$TabRelationsType;
                $max=count($tt);
                for ($ii = 0; $ii < $max; $ii++) {
                    echo "<input type=checkbox name=\"Type_" . $tt[$ii] . "\"";
                    if (count($relation['myself']) > 0 && strpos(" ".$relation['myself']->Type,$tt[$ii] )!=0)
                    echo " checked ";
                    echo "> ".$words->get("Relation_Type_" . $tt[$ii])."<br />";
                }
            ?>
            <p class="mt-3"><?=$words->get('RelationListExplanation')?></p>
            </div>
            <? else : ?>
            <div>
            <?=$words->get('RelationType')?>: <strong><?=$words->get("Relation_Type_" . $relation['member']->Type)?></strong>
            </div>
            <? endif ?>
            <div>
                <label class="grey"><?=$words->get("RelationText",$member->Username)?>:</label><br />
                <textarea rows="4" class="w-100" name="Comment"><?php
                    if (isset($relation['myself']->Comment)) {
                        $lang = $this->model->get_profile_language();
                        $comment = $words->mInTrad($relation['myself']->IdTradComment, $lang->id);

                        // Hack to filter out accidental '0' or '123456' comments that were saved
                        // by users while relation comment update form was buggy (see #1580)
                        if (is_numeric($comment)) {
                            $comment = '';
                        }

                        echo $comment;
                    }
                ?></textarea>
            </div>
            <?php
            if ($action == 'confirm') {
               echo '<input type="hidden" name="Type" value="'.$relation['member']->Type.'">';
               echo '<input type="hidden" name="RelationId" value="'.$relation['member']->id.'">';
               echo '<input type="hidden" name="action" value="confirm">';
            } elseif ($action == 'update') {
               echo '<input type="hidden" name="RelationId" value="'.$relation['myself']->id.'">';
               echo '<input type="hidden" name="action" value="update">';
            }
            else {
               echo '<input type="hidden" name="action" value="add">';
            }
            ?>
            <input type="submit" class="btn btn-primary" name="submit" value="<?=$words->getSilent($action.'Relation')?>" /><?php echo $words->flushBuffer(); ?>
            <br />
        </fieldset>
        </form>
            </div>
</div>
        <?php
    }
}
?>