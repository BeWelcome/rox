<?php
    // get translation module
    $layoutkit = $this->layoutkit;
    $words = $layoutkit->getWords();
    $model = $this->getModel();

    $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);

    $formkit = $layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('GroupsController', 'createGroupCallback');
    
    if ($redirected = $formkit->mem_from_redirect)
    {
        $Group_ = ((!empty($redirected->post['Group_'])) ? $redirected->post['Group_'] : '');
        $GroupDesc_ = ((!empty($redirected->post['GroupDesc_'])) ? $redirected->post['GroupDesc_'] : '');
        $Type = ((!empty($redirected->post['Type'])) ? $redirected->post['Type']: false);
        $VisiblePosts = ((!empty($redirected->post['VisiblePosts'])) ? $redirected->post['VisiblePosts']: false);
        $problems = ((is_array($redirected->problems)) ? $redirected->problems : array());
    }
    else
    {
        $Group_ = '';
        $GroupDesc_ = '';
        $Type = false;
        $VisiblePosts = false;
        $problems = array();
    }

?>
    <div id="groups">
        <form method="post" action="" enctype='multipart/form-data'>
        <?=$callback_tag ?>
        <fieldset>
            <legend><?= $words->get('GroupsNewHeading'); ?></legend>
            <?= ((!empty($problems['General'])) ? "<p class='error'>" . $words->get('GroupsCreationFailed') . "</p>" : '' ); ?>

            <p><?= ((!empty($problems['Group_'])) ? "<p class='error'>" . $words->get('GroupsNameMissing') . "</p>" : '' ); ?>
            <h3><label for="name"><?= $words->get('Name');?>:</label></h3>
            <input type="text" id="name" name="Group_" class="long" value='<?=$Group_?>' /></p>

            <p><?= ((!empty($problems['GroupDesc_'])) ? "<p class='error'>" . $words->get('GroupsDescriptionMissing') ."</p>" : '' ); ?>
            <h3><label for="description"><?= $words->get('Description');?>:</label></h3>
            <textarea  id="description" name="GroupDesc_" cols="60" rows="5" class="long" ><?=$GroupDesc_?></textarea></p>

            <p><?= ((!empty($problems['Type'])) ? "<p class='error'>" . $words->get('GroupsTypeMissing') . "</p>" : '' ); ?>
            <h3><?= $words->get('GroupsJoinHeading'); ?></h3>
            <ul>
                <li><input type="radio" id="public" name="Type" value="Public"<?= (($Type=='Public') ? ' checked': ''); ?> /><label for="public" ><?=$words->get('GroupsJoinPublic'); ?></label></li>
                <li><input type="radio" id="approved" name="Type" value="NeedAcceptance"<?= (($Type=='NeedAcceptance') ? ' checked': ''); ?> /><label for="approed" ><?=$words->get('GroupsJoinApproved'); ?></label></li>
                <li><input type="radio" id="invited" name="Type" value="NeedInvitation"<?= (($Type=='NeedInvitation') ? ' checked': ''); ?> /><label for="invited" ><?=$words->get('GroupsJoinInvited'); ?></label></li>
            </ul></p>

            <p><?= ((!empty($problems['Visibility'])) ? "<p class='error'>" . $words->get('GroupsVisibilityMissing') . "</p>" : '' ); ?>
            <h3><?= $words->get('GroupsVisiblePostsHeading'); ?></h3>
            <ul>
                <li><input type="radio" id="visible" name="VisiblePosts" value="yes"<?= (($VisiblePosts=='yes') ? ' checked="checked"': ''); ?> /><label for="visible" ><?=$words->get('GroupsVisiblePosts'); ?></label></li>
                <li><input type="radio" id="invisible" name="VisiblePosts" value="no"<?= (($VisiblePosts=='no') ? ' checked="checked"': ''); ?> /><label for="invisible" ><?=$words->get('GroupsInvisiblePosts'); ?></label></li>
            </ul></p>

                      <?php if (!empty($problems['ImageUploadTooBig']) && $problems['ImageUploadTooBig']){
                                echo "<p class='error'>" . $words->get('GroupsImageUploadTooBig') . "</p>";
                            }
                            if (!empty($problems['ImageUpload']) && $problems['ImageUpload']){
                                echo "<p class='error'>" . $words->get('GroupsImageUploadFailed') . "</p>";
                            }?>

            <h3><?= $words->get('GroupsAddImage'); ?></h3>
            <label for='group_image'><?= $words->get('GroupsImage'); ?></label><br /><input id='group_image' name='group_image' type='file' />

            </fieldset>
        </form>
    </div>
