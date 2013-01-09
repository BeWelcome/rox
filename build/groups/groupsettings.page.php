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
        <div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups"><?= $words->get('Groups');?></a> &raquo; <a href=""><?= $words->get('GroupsAdministrateGroup');?></a></h1>
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
    <div id="groups">


    <form method="post" action="" enctype='multipart/form-data'>
    <?=$callback_tag ?>
        <fieldset>
            <legend><?= $words->get('GroupsAdminGroup'); ?></legend>
            <div class="subcolumns">
                <div class="c50l">
                    <div class="subcl">
                            <input type='hidden' name='group_id' value='<?=$this->group->getPKValue(); ?>' />
                            <?= ((!empty($problems['General'])) ? "<p class='error'>" . $words->get('GroupsChangeFailed') . "</p>" : '' ); ?>
                            <label for="description"><h3><?= $words->get('Description');?>:</label></h3><?= ((!empty($problems['GroupDesc_'])) ? "<span class='error'>" . $words->get('GroupsCreationDescriptionMissing') ."</span>" : '' ); ?>
                            <textarea  id="description" name="GroupDesc_" cols="40" rows="4" class="long" ><?=htmlspecialchars($GroupDesc_, ENT_QUOTES)?></textarea><br /><br />
                    </div><!-- subcl -->
                </div><!-- c50l -->
                <div class="c50r">
                    <div class="subcr">  
                     <h3><?= $words->get('GroupsPublicStatusHeading'); ?></h3><?= ((!empty($problems['Type'])) ? "<span class='error'>" . $words->get('GroupsCreationTypeMissing') . "</span>" : '' ); ?>
                            <ul>
                                <li><input type="radio" id="public" name="Type" value="Public"<?= (($Type=='Public') ? ' checked': ''); ?> /><label for="public" ><?=$words->get('GroupsJoinPublic'); ?></label></li>
                                <li><input type="radio" id="approved" name="Type" value="NeedAcceptance"<?= (($Type=='NeedAcceptance') ? ' checked="checked"': ''); ?> /><label for="approved" ><?=$words->get('GroupsJoinApproved'); ?></label></li>
                                <li><input type="radio" id="invited" name="Type" value="NeedInvitation"<?= (($Type=='NeedInvitation') ? ' checked="checked"': ''); ?> /><label for="invited" ><?=$words->get('GroupsJoinInvited'); ?></label></li>
                            </ul>
                    </div><!-- subcr -->
                </div><!-- c50r -->
            </div><!-- subcolumns -->             
            <div class="subcolumns">
                <div class="c50l">
                    <div class="subcl">
                          <h3><?= $words->get('GroupsVisiblePostsHeading'); ?></h3><?= ((!empty($problems['Visibility'])) ? "<span class='error'>" . $words->get('GroupsCreationVisibilityMissing') . "</span>" : '' ); ?>
                            <ul>
                                <li><input type="radio" id="visible" name="VisiblePosts" value="yes"<?= (($VisiblePosts=='yes') ? ' checked="checked"': ''); ?> /><label for="visible" ><?=$words->get('GroupsVisiblePosts'); ?></label></li>
                                <li><input type="radio" id="invisible" name="VisiblePosts" value="no"<?= (($VisiblePosts=='no') ? ' checked="checked"': ''); ?> /><label for="invisible" ><?=$words->get('GroupsInvisiblePosts'); ?></label></li>
                            </ul>
                    </div><!-- subcl -->
                </div><!-- c50l -->
                <div class="c50r">
                    <div class="subcr">  
                     <h3><?= $words->get('GroupsAddImage'); ?></h3>
                            <label for='group_image'><?= $words->get('GroupsImage'); ?></label><br /><input id='group_image' name='group_image' type='file' />
                        
                    </div><!-- subcr -->
                </div><!-- c50r -->
            </div><!-- subcolumns -->                                       
           <div style="margin-bottom:1.5em"><input type="submit" value="<?= $words->get('GroupsUpdateGroupSettings'); ?>" /></div>           
            <div class="subcolumns">
                <div class="c50l">
                    <div class="subcl">
                    <h3><?= $words->get('GroupsAdministrateMembers'); ?></h3>
                    <a class="button" href="groups/<?= $this->group->id; ?>/memberadministration"><?= $words->get('GroupsAdministrateMembers'); ?></a>
                    </div><!-- subcl -->
                </div><!-- c50l -->
                <div class="c50r">
                    <div class="subcr">  
                    <h3><?= $words->get('GroupsDeleteGroup'); ?></h3>
                    <a class="button" href="groups/<?= $this->group->id; ?>/delete"><?= $words->get('GroupsDeleteGroup'); ?></a>
                    </div><!-- subcr -->
                </div><!-- c50r -->
            </div><!-- subcolumns -->                           
        </fieldset>
    </form>
    </div><!-- groups --> 
    <?php    
    }


}

?>
