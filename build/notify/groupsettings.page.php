<?php

/**
 * This page allows to create a new group
 *
 */
class GroupSettingsPage2 extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?>
        <div id="teaser" class="page-teaser clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups">Groups</a> &raquo; <a href="">Admininstrate group</a></h1>
        </div>
        </div>
        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'admin';
    }

    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();

        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('GroupsController', 'changeGroupSettings');
        
        if ($redirected = $formkit->mem_from_redirect)
        {
            $GroupDesc_ = ((!empty($redirected->post['GroupDesc_'])) ? $redirected->post['GroupDesc_'] : '');
            $Type = ((!empty($redirected->post['Type'])) ? $redirected->post['Type']: 'Public');
            $VisiblePosts = ((!empty($redirected->post['VisiblePosts'])) ? $redirected->post['VisiblePosts'] : 'yes');
            $DisplayedOnProfile = ((!empty($redirected->post['DisplayedOnProfile'])) ? $redirected->post['DisplayedOnProfile'] : 'Yes');
            $problems = ((is_array($redirected->problems)) ? $redirected->problems : array());
        }
        else
        {
            $GroupDesc_ = (($this->group->IdDescription) ? $words->mTrad($this->group->IdDescription) : '');
            $Type = $this->group->Type;
            $VisiblePosts = (($this->group->VisiblePosts == 'no') ? 'no' : 'yes');
            $DisplayedOnProfile = (($this->group->DisplayedOnProfile == 'No') ? 'No' : 'Yes');
            $problems = array();
        }
?>
    <div id="groups">
        <div class="subcolumns">
            <div class="c62l">
                <div class="subcl">

                    <h3><?= $words->get('GroupsAdminGroup'); ?></h3>
                    <form method="post" action="" enctype='multipart/form-data'>
                    <?=$callback_tag ?>
                        <fieldset>
                            <input type='hidden' name='group_id' value='<?=$this->group->getPKValue(); ?>' />
                            <?= ((!empty($problems['General'])) ? "<p class='error'>" . $words->get('GroupsChangeFailed') . "</p>" : '' ); ?>
                            <label for="description">Description:</label><?= ((!empty($problems['GroupDesc_'])) ? "<span class='error'>" . $words->get('GroupsCreationDescriptionMissing') ."</span>" : '' ); ?><br />
                            <textarea  id="description" name="GroupDesc_" cols="60" rows="5" class="long" ><?=$GroupDesc_?></textarea><br /><br />
                        </fieldset>
                        <fieldset>
                            <h3><?= $words->get('GroupsPublicStatusHeading'); ?></h3><?= ((!empty($problems['Type'])) ? "<span class='error'>" . $words->get('GroupsCreationTypeMissing') . "</span>" : '' ); ?>
                            <ul>
                                <li><input type="radio" id="public" name="Type" value="Public"<?= (($Type=='Public') ? ' checked': ''); ?> /><label for="public" ><?=$words->get('GroupsJoinPublic'); ?></label></li>
                                <li><input type="radio" id="approved" name="Type" value="NeedAcceptance"<?= (($Type=='NeedAcceptance') ? ' checked': ''); ?> /><label for="approved" ><?=$words->get('GroupsJoinApproved'); ?></label></li>
                                <li><input type="radio" id="invited" name="Type" value="NeedInvitation"<?= (($Type=='NeedInvitation') ? ' checked': ''); ?> /><label for="invited" ><?=$words->get('GroupsJoinInvited'); ?></label></li>
                            </ul>
                        </fieldset>
                        <fieldset>
                            <h3><?= $words->get('GroupsVisiblePostsHeading'); ?></h3><?= ((!empty($problems['Visibility'])) ? "<span class='error'>" . $words->get('GroupsCreationVisibilityMissing') . "</span>" : '' ); ?>
                            <ul>
                                <li><input type="radio" id="visible" name="VisiblePosts" value="yes"<?= (($VisiblePosts=='yes') ? ' checked': ''); ?> /><label for="visible" ><?=$words->get('GroupsVisiblePosts'); ?></label></li>
                                <li><input type="radio" id="invisible" name="VisiblePosts" value="no"<?= (($VisiblePosts=='no') ? ' checked': ''); ?> /><label for="invisible" ><?=$words->get('GroupsInvisiblePosts'); ?></label></li>
                            </ul>
                        </fieldset>
                        <fieldset>
                            <h3><?= $words->get('GroupsAddImage'); ?></h3>
                            <label for='group_image'><?= $words->get('GroupsImage'); ?></label><br /><input id='group_image' name='group_image' type='file' />
                        <fieldset>
                        <p class="center"><input type="submit" class="button" value="<?= $words->get('GroupsUpdateGroupSettings'); ?>" /></p>
                    </form>
                </div>
            </div>
            <div class="c38r">
                <div class="subcr">
                    <h3><?= $words->get('GroupsAdministrateMembers'); ?></h3>
                        <p><a class="button" role="button" href="groups/<?= $this->group->id; ?>/memberadministration"><?= $words->get('GroupsAdministrateMembers'); ?></a></p>
                    <h3><?= $words->get('GroupsDeleteGroup'); ?></h3>
                        <p><a class="button" role="button" href="groups/<?= $this->group->id; ?>/delete"><?= $words->get('GroupsDeleteGroup'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
    <?php
    }


}

?>
