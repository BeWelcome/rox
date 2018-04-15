<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.
*/
    /**
     * @author Fake51
     */

    /**
     * This page allows for administration of groups
     *
     * @package Apps
     * @subpackage Groups
     */
class GroupSettingsPage extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div>
            <h1><a href="groups/mygroups"><?= $words->get('Groups');?></a> &raquo; <a href="groups/<?=$this->group->getPKValue(); ?>"><? echo htmlspecialchars($this->getGroupTitle(),ENT_QUOTES); ?></a>  &raquo;  <a href=""><?= $words->get('GroupsAdministrateGroup');?></a></h1>
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
        $group_id = $this->group->id;
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
            $GroupDesc_ = str_replace(array('<br>','<br/>', '<br />'), "\n", $this->group->getDescription());
            $Type = $this->group->Type;
            $VisiblePosts = (($this->group->VisiblePosts == 'no') ? 'no' : 'yes');
            $DisplayedOnProfile = (($this->group->DisplayedOnProfile == 'No') ? 'No' : 'Yes');
            $problems = array();
        }
?>

<div class="col-12">
    <form method="post" action="" enctype='multipart/form-data'>
        <?=$callback_tag ?>
        <input type='hidden' name='group_id' value='<?=$this->group->getPKValue(); ?>' />

        <?php if (!empty($problems)){
            if (!empty($problems['General']) && $problems['General']){
                echo "<div class='alert alert-danger w-100' role='alert'>" . $words->get('GroupsChangeFailed') . "</div>";
            }
        } else {
            if ($redirected) {
                echo "<div class='alert alert-success w-100' role='alert'>" . $words->get('GroupsChangeSucceeded') . "</div>";
            }}
        ?>

        <?php if (!empty($problems['ImageUploadTooBig']) && $problems['ImageUploadTooBig']){
            echo "<div class='alert alert-danger p-2'>" . $words->get('GroupsImageUploadTooBig') . "</div>";
        }
        if (!empty($problems['ImageUpload']) && $problems['ImageUpload']){
            echo "<div class='alert alert-danger p-2'>" . $words->get('GroupsImageUploadFailed') . "</div>";
        }?>
        <div class="row">

            <div class="col-9">
                <h5><?= $words->get('GroupsAddImage'); ?></h5>
                <div class="float-left">
                    <img class="float-left framed mr-2 mb-2" src="groups/realimg/<?= $this->group->getPKValue(); ?>" width="100px" alt="Group image">
                </div>
                <div>
                    <label for='group_image'><?= $words->get('GroupsImage'); ?></label>
                    <input id='group_image' name='group_image' type='file' />
                </div>

            </div>

            <div class="col-3">
                <a class="btn btn-block btn-outline-primary" role="button" href="groups/<?= $this->group->id; ?>/memberadministration"><?= $words->get('GroupsAdministrateMembers'); ?></a>
                <a class="btn btn-block btn-outline-primary" role="button" href="groups/<?= $this->group->id; ?>/delete"><?= $words->get('GroupsDeleteGroup'); ?></a>
            </div>
        </div>

        <?= ((!empty($problems['GroupDesc_'])) ? "<div class='alert alert-danger p-2 mt-3'>" . $words->get('GroupsCreationDescriptionMissing') ."</div>" : '' ); ?>

        <div class="input-group my-3">
            <label for="description" class="h5 m-0"><?= $words->get('Description');?></label>
            <textarea  id="description" name="GroupDesc_" aria-describedby="newgroupdescription" rows="5" class="w-100" ><?=htmlspecialchars($GroupDesc_, ENT_QUOTES)?></textarea>
        </div>

</div>

        <div class="col-12 col-lg-6">

            <?= ((!empty($problems['Type'])) ? "<div class='alert alert-danger p-2'>" . $words->get('GroupsTypeMissing') . "</div>" : '' ); ?>

            <fieldset class="form-group">
                <legend class="m-0">
                    <label class="m-0"><h5><?= $words->get('GroupsPublicStatusHeading'); ?></h5></label>
                </legend>

                <div class="form-check">
                    <label>
                        <input type="radio" id="public" name="Type" value="Public"<?= (($Type=='Public') ? ' checked': ''); ?>>
                        <?=$words->get('GroupsJoinPublic'); ?>
                    </label>
                </div>
                <div class="form-check">
                    <label>
                        <input type="radio" id="approved" name="Type" value="NeedAcceptance"<?= (($Type=='NeedAcceptance') ? ' checked': ''); ?>>
                        <?=$words->get('GroupsJoinApproved'); ?>
                    </label>
                </div>
                <div class="form-check">
                    <label>
                        <input type="radio" id="invited" name="Type" value="NeedInvitation"<?= (($Type=='NeedInvitation') ? ' checked': ''); ?>>
                        <?=$words->get('GroupsJoinInvited'); ?>
                    </label>
                </div>
            </fieldset>
        </div>

        <div class="col-12 col-lg-6">

            <?= ((!empty($problems['Visibility'])) ? "<div class='alert alert-danger p-2 mt-3'>" . $words->get('GroupsVisibilityMissing') . "</div>" : '' ); ?>
            <fieldset class="form-group">
                <legend class="m-0">
                    <label class="m-0"><h5><?= $words->get('GroupsVisiblePostsHeading'); ?></h5></label>
                </legend>

                <div class="form-check">
                    <label>
                        <input type="radio" id="visible" name="VisiblePosts" value="yes"<?= (($VisiblePosts=='yes') ? ' checked="checked"': ''); ?>>
                        <?=$words->get('GroupsVisiblePosts'); ?>
                    </label>
                </div>
                <div class="form-check">
                    <label>
                        <input type="radio" id="invisible" name="VisiblePosts" value="no"<?= (($VisiblePosts=='no') ? ' checked="checked"': ''); ?>>
                        <?=$words->get('GroupsInvisiblePosts'); ?>
                    </label>
                </div>
            </fieldset>



        </div>

        <div class="col-12 text-center">
            <input type="submit" class="btn btn-block btn-primary m-2" value="<?= $words->getSilent('GroupsUpdateGroupSettings'); ?>">
            </form>
        </div>

        <div class="col-12 col-sm-6">
            <a class="btn btn-block btn-outline-primary" role="button" href="groups/<?= $this->group->id; ?>/memberadministration"><?= $words->get('GroupsAdministrateMembers'); ?></a>
        </div>

        <div class="col-12 col-sm-6">
            <a class="btn btn-block btn-outline-primary" role="button" href="groups/<?= $this->group->id; ?>/delete"><?= $words->get('GroupsDeleteGroup'); ?></a>
        </div>
    <?php
    }
}
?>
