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

use App\Doctrine\GroupType;

/**
     * This page allows for administration of groups
     *
     * @package Apps
     * @subpackage Groups
     */
class GroupSettingsPage extends GroupsSubPage
{
    public function __construct($group)
    {
        parent::__construct($group);
        $this->addLateLoadScriptFile('build/roxeditor.js');
        $this->addLateLoadScriptFile('build/bsfileselect.js');
        $this->addStylesheet('build/roxeditor.css');
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

        if (!$this->canMemberAccess())
        {
            echo $words->get('GroupsNotPublic');
            return;
        }

        if (!$this->isGroupOwner() && !($this->group->Type !== GroupType::INVITE_ONLY && $this->isGroupAdmin())) {
            echo $words->get('GroupsSettingsOnlyAdmin');
            return;
        }

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
    <form method="post" action="" enctype='multipart/form-data'>
        <div class="row">
        <?=$callback_tag ?>
        <input type='hidden' name='group_id' value='<?=$this->group->getPKValue(); ?>' />

        <?php if (!empty($problems)){
            if (!empty($problems['General']) && $problems['General']){
                echo "<div class='col-12'><div class='alert alert-danger' role='alert'>" . $words->get('GroupsChangeFailed') . "</div></div>";
            }
        } else {
            if ($redirected) {
                echo "<div class='col-12'><div class='alert alert-success' role='alert'>" . $words->get('GroupsChangeSucceeded') . "</div></div>";
            }}
        ?>

        <?php if (!empty($problems['ImageUploadTooBig']) && $problems['ImageUploadTooBig']){
            echo "<div class='col-12'><div class='alert alert-danger'>" . $words->get('GroupsImageUploadTooBig') . "</div></div>";
        }
        if (!empty($problems['ImageUpload']) && $problems['ImageUpload']){
            echo "<div class='col-12'><div class='alert alert-danger'>" . $words->get('GroupsImageUploadFailed') . "</div></div>";
        }?>
            <div class="col-8">
                <h5><?= $words->get('GroupsAddImage'); ?></h5>
                <div class="float-left">
                    <img class="float-left framed mr-2 mb-2" src="group/realimg/<?= $this->group->getPKValue(); ?>" width="100px" alt="Group image">
                </div>
                <div class="custom-file">
                    <input id="group_image" name="group_image" type="file" class="custom-file-input">
                    <label for="group_image" class="custom-file-label"><?= $words->get('GroupsImage'); ?></label>
                </div>

            </div>

            <div class="col-4 mt-3">
                <a class="btn btn-block btn-secondary" role="button" href="group/<?= $this->group->id; ?>/memberadministration"><i class="fa fa-users mr-1"></i><?= $words->get('GroupsAdministrateMembers'); ?></a>
                <a class="btn btn-block btn-danger" role="button" href="group/<?= $this->group->id; ?>/delete"><i class="fa fa-trash mr-1"></i><?= $words->get('GroupsDeleteGroup'); ?></a>
            </div>

        <?= ((!empty($problems['GroupDesc_'])) ? "<div class='alert alert-danger p-2 mt-3'>" . $words->get('GroupsCreationDescriptionMissing') ."</div>" : '' ); ?>

        <div class="col-12">
            <div class="o-form-group my-3">
                <label for="description" class="h5 m-0"><?= $words->get('Description');?></label>
                <textarea  id="description" name="GroupDesc_" aria-describedby="newgroupdescription" rows="5" class="o-input editor p-2"><?=htmlspecialchars($GroupDesc_, ENT_QUOTES)?></textarea>
            </div>
        </div>

        <div class="col-12 col-lg-6">

            <?= ((!empty($problems['Type'])) ? "<div class='alert alert-danger p-2'>" . $words->get('GroupsTypeMissing') . "</div>" : '' ); ?>

            <fieldset class="o-form-group">
                <legend class="m-0">
                    <label class="m-0"><h5><?= $words->get('GroupsPublicStatusHeading'); ?></h5></label>
                </legend>

                <?php if (GroupType::INVITE_ONLY !== $Type) { ?>
                <div class="o-checkbox mb-3">
                    <input type="radio" class="o-checkbox__input" id="public" name="Type" value="Public"<?= (($Type=='Public') ? ' checked': ''); ?>>
                    <label for="public" class="o-checkbox__label">
                        <?=$words->get('GroupsJoinPublic'); ?>
                    </label>
                </div>
                <div class="o-checkbox mb-3">
                    <input type="radio" class="o-checkbox__input" id="approved" name="Type" value="NeedAcceptance"<?= (($Type=='NeedAcceptance') ? ' checked': ''); ?>>
                    <label for="approved" class="o-checkbox__label">
                        <?=$words->get('GroupsJoinApproved'); ?>
                    </label>
                </div>
                <?php } else { ?>
                    <div class="o-checkbox mb-3">
                        <input type="radio" disabled="disabled" class="o-checkbox__input" id="invitation" name="Type" value="NeedInvitation" checked="checked">
                        <label for="invitation" class="o-checkbox__label">
                            <?=$words->get('groupsjoininvited'); ?>
                        </label>
                    </div>
                <?php } ?>
            </fieldset>
        </div>

        <div class="col-12 col-lg-6">

            <?= ((!empty($problems['Visibility'])) ? "<div class='alert alert-danger p-2 mt-3'>" . $words->get('GroupsVisibilityMissing') . "</div>" : '' ); ?>
            <fieldset class="o-form-group">
                <legend class="m-0">
                    <label class="m-0"><h5><?= $words->get('GroupsVisiblePostsHeading'); ?></h5></label>
                </legend>

                <div class="o-checkbox mb-3">
                    <input type="radio" class="o-checkbox__input" id="visible" name="VisiblePosts" value="yes"<?= (($VisiblePosts=='yes') ? ' checked="checked"': ''); ?>>
                    <label for="visible" class="o-checkbox__label">
                        <?=$words->get('GroupsVisiblePosts'); ?>
                    </label>
                </div>
                <div class="o-checkbox mb-3">
                    <input type="radio" class="o-checkbox__input" id="invisible" name="VisiblePosts" value="no"<?= (($VisiblePosts=='no') ? ' checked="checked"': ''); ?>>
                    <label for="invisible" class="o-checkbox__label">
                        <?=$words->get('GroupsInvisiblePosts'); ?>
                    </label>
                </div>
            </fieldset>
        </div>

        <div class="col-12 col-sm-6 offset-sm-6 text-center">
            <input type="submit" class="btn btn-block btn-primary my-2" value="<?= $words->getSilent('GroupsUpdateGroupSettings'); ?>">
        </div>
        </div>
    </form>
    <?php
    }
}
