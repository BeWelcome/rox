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
        echo '<div class="row"><div class="col-12">';

        $formkit = $this->layoutkit->formkit;
        $callbacktag = $formkit->setPostCallback('GroupsController', 'changeMemberSettings');
        $words = $this->getWords();

        $resultmsg = $problemmsg = '';

        $redirected = $formkit->mem_from_redirect;
        if (is_object($redirected))
        {
            $resultmsg = (($redirected->result) ? "<p class=\"alert-success p-2\">{$words->get('GroupMemberSettingsUpdated')}</p>" : "<p class=\"alert-warning p-2\">{$words->get('GroupMemberSettingsNotUpdated')}</p>");
            $problemmsg = (($redirected->problems) ? "<p class=\"alert-danger p-2\">{$words->get('GroupMemberSettingsProblems')}</p>" : '');
        }

        $membershipinfo = $this->member->getGroupMembership($this->group);
        ?>
        <?= $resultmsg; ?>
        <?= $problemmsg; ?>
        <form action="" method="post">
        <?= $callbacktag; ?>
            <h2><?= $words->get('GroupsMemberSettings') ;?><?= htmlspecialchars($this->group->Name, ENT_QUOTES) ?></h2>
            <input type='hidden' name='member_id' value='<?= $this->member->id ;?>' />
            <input type='hidden' name='group_id' value='<?= $membershipinfo->IdGroup ;?>' />
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group"><label for="comment"><?= $words->get('GroupsMemberComments') ;?></label><br>
                    <textarea class="form-control" id="comment" name="membershipinfo_comment" cols="30" rows="3"><?= (($membershipinfo->Comment != '' ) ? htmlspecialchars($words->mTrad($membershipinfo->Comment)) : '' ); ?></textarea>
                    </div>
                </div> <!-- row -->
                <div class="col-12 col-md-6">
                    <label><?= $words->get('GroupsMemberAcceptMail') ;?>:  </label><br>

                    <div class="btn-group w-100" data-toggle="buttons">
                        <label class="btn btn-primary btn-radio <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'yes') ? 'active' : '' ); ?>" for="yes_option">
                            <input id='yes_option' class="noradio" autocomplete="off" type="radio" value="yes" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'yes') ? 'checked="checked" ' : '' ); ?>>Yes
                        </label>
                        <label for="no_option" class="btn btn-primary btn-radio <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'no') ? 'active' : '' ); ?>">
                            <input id='no_option' class="noradio" autocomplete="off" type="radio" value="no" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'no' || !$membershipinfo->IacceptMassMailFromThisGroup) ? 'checked="checked" ' : '' ); ?>>No
                        </label>
                    </div>
                </div>
            </div>
            <?php if ($membershipinfo->IdMember < 0) { ?>
                <p><?= $words->get('GroupMemberSettingsDisabledInfo') ?></p>
            <?php } ?>
            <div class="col-12">
            <input type="submit" class="btn btn-primary pull-right" value="<?= $words->getBuffered('GroupsUpdateMemberSettings') ;?>"><?=$words->flushBuffer();?>
            </div>
        </form>
        </div></div>
        <?php
    }

    protected function getSubmenuActiveItem() {
        return 'membersettings';
    }
}


