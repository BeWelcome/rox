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

<div class="col-12 col-lg-5">
    <form method="post" action="" enctype='multipart/form-data'>
        <?=$callback_tag ?>
    <?= ((!empty($problems['General'])) ? "<p class='error'>" . $words->get('GroupsCreationFailed') . "</p>" : '' ); ?>

        <?php if (!empty($problems['ImageUploadTooBig']) && $problems['ImageUploadTooBig']){
            echo "<p class='alert-danger p-2'>" . $words->get('GroupsImageUploadTooBig') . "</p>";
        }
        if (!empty($problems['ImageUpload']) && $problems['ImageUpload']){
            echo "<p class='alert-danger p-2'>" . $words->get('GroupsImageUploadFailed') . "</p>";
        }?>
        <h3><?= $words->get('GroupsAddImage'); ?></h3>
        <label for='group_image'><?= $words->get('GroupsImage'); ?></label><br /><input id='group_image' name='group_image' type='file' />

        <?= ((!empty($problems['Group_'])) ? "<p class='alert-danger p-2 mt-3'>" . $words->get('GroupsNameMissing') . "</p>" : '' ); ?>
        <label for="name" class="sr-only"><?= $words->get('Name');?></label>
        <div class="input-group mt-3">
            <div class="input-group-prepend font-weight-bold" id="newgroupname">
                <span class="input-group-text"><?= $words->get('Name');?></span>
            </div>
            <input class="form-control w-100" maxlength="200" id="name" name="Group_" value="<?=$Group_?>" aria-describedby="newgroupname" type="text">
        </div>


        <?= ((!empty($problems['GroupDesc_'])) ? "<p class='alert-danger p-2 mt-3'>" . $words->get('GroupsDescriptionMissing') ."</p>" : '' ); ?>
        <label for="description" class="sr-only"><?= $words->get('Description');?></label>
        <div class="input-group my-3">
            <div class="input-group-prepend font-weight-bold" style="white-space: normal;" id="newgroupdescription">
                <span class="input-group-text"><?= $words->get('Description');?></span>
            </div>
            <textarea  id="description" name="GroupDesc_" aria-describedby="newgroupdescription" rows="5" class="w-100" ><?=$GroupDesc_?></textarea>
        </div>

</div>

<div class="col-12 col-lg-7">

    <?= ((!empty($problems['Type'])) ? "<p class='alert-danger p-2'>" . $words->get('GroupsTypeMissing') . "</p>" : '' ); ?>

    <fieldset class="form-group">
        <legend class="m-0">
            <label class="m-0"><h3><?= $words->get('GroupsJoinHeading'); ?></h3></label>
        </legend>

        <div class="form-check-inline">
            <label>
                <input type="radio" id="public" name="Type" value="Public"<?= (($Type=='Public') ? ' checked': ''); ?>>
                <?=$words->get('GroupsJoinPublic'); ?>
            </label>
        </div>
        <div class="form-check-inline">
            <label>
                <input type="radio" id="approved" name="Type" value="NeedAcceptance"<?= (($Type=='NeedAcceptance') ? ' checked': ''); ?>>
                <?=$words->get('GroupsJoinApproved'); ?>
            </label>
        </div>
        <div class="form-check-inline">
            <label>
                <input type="radio" id="invited" name="Type" value="NeedInvitation"<?= (($Type=='NeedInvitation') ? ' checked': ''); ?>>
                <?=$words->get('GroupsJoinInvited'); ?>
            </label>
        </div>
    </fieldset>

    <?= ((!empty($problems['Visibility'])) ? "<p class='alert-danger p-2 mt-3'>" . $words->get('GroupsVisibilityMissing') . "</p>" : '' ); ?>
    <fieldset class="form-group my-3">
        <legend class="m-0">
            <label class="m-0"><h3><?= $words->get('GroupsVisiblePostsHeading'); ?></h3></label>
        </legend>

        <div class="form-check-inline">
            <label>
                <input type="radio" id="visible" name="VisiblePosts" value="yes"<?= (($VisiblePosts=='yes') ? ' checked="checked"': ''); ?>>
                <?=$words->get('GroupsVisiblePosts'); ?>
            </label>
        </div>
        <div class="form-check-inline">
            <label>
                <input type="radio" id="invisible" name="VisiblePosts" value="no"<?= (($VisiblePosts=='no') ? ' checked="checked"': ''); ?>>
                <?=$words->get('GroupsInvisiblePosts'); ?>
            </label>
        </div>
    </fieldset>
</div>

<div class="col-12 text-center">
    <input type="submit" class="btn btn-block btn-primary m-2" value="Create Group">
    </form>
</div>