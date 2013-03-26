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
     * This page handles member settings for a group
     *
     * @package Apps
     * @subpackage Groups
     */
class GroupMemberSettingsPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $formkit = $this->layoutkit->formkit;
        $callbacktag = $formkit->setPostCallback('GroupsController', 'changeMemberSettings');
        $words = $this->getWords();

        $resultmsg = $problemmsg = '';

        $redirected = $formkit->mem_from_redirect;
        if (is_object($redirected))
        {
            $resultmsg = (($redirected->result) ? "<p class=\"note\">{$words->get('GroupMemberSettingsUpdated')}</p>" : "<p class=\"note\">{$words->get('GroupMemberSettingsNotUpdated')}</p>");
            $problemmsg = (($redirected->problems) ? "<p class=\"error\">{$words->get('GroupMemberSettingsProblems')}</p>" : '');
        }

        if (!APP_user::isBWLoggedIn('NeedMore,Pending'))
        {
            $widg = $this->createWidget('LoginFormWidget');
            $widg->render();
        }
        else
        {
            $membershipinfo = $this->member->getGroupMembership($this->group);
        ?>
        <?= $resultmsg; ?>
        <?= $problemmsg; ?>
        <form action="" method="post">
        <?= $callbacktag; ?>
        <fieldset>
            <legend><?= $words->get('GroupsMemberSettings') ;?><?= htmlspecialchars($this->group->Name, ENT_QUOTES) ?></legend>
            <input type='hidden' name='member_id' value='<?= $this->member->id ;?>' />
            <input type='hidden' name='group_id' value='<?= $membershipinfo->IdGroup ;?>' />
            <div class="row">
                <label for="comment"><?= $words->get('GroupsMemberComments') ;?></label><br />
                <textarea id="comment" name="membershipinfo_comment" cols="60" rows="5" class="long" ><?= (($membershipinfo->Comment != '' ) ? htmlspecialchars($words->mTrad($membershipinfo->Comment)) : '' ); ?></textarea>
            </div> <!-- row -->
            <div class="row">
                <label><?= $words->get('GroupsMemberAcceptMail') ;?>:  </label>
                <input id='no_option' type="radio" value="no" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'no' || !$membershipinfo->IacceptMassMailFromThisGroup) ? 'checked="checked" ' : '' ); ?>/>
                <label for="no_option"><?= $words->get('no') ;?></label>
                <input id='yes_option' type="radio" value="yes" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'yes') ? 'checked="checked" ' : '' ); ?>/>
                <label for="yes_option"><?= $words->get('yes') ;?></label>
            </div> <!-- row -->
            <p style="padding-top: 2em"><input type="submit" value="<?= $words->getBuffered('GroupsUpdateMemberSettings') ;?>" /><?=$words->flushBuffer();?></p>
            </fieldset>
        </form>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'membersettings';
    }
}


