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
 * This page asks if the user wants to join the group
 *
 */
class GroupJoinPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        if (!APP_user::isBWLoggedIn('NeedMore,Pending'))
        {
            $widg = $this->createWidget('LoginFormWidget');
            $widg->render();
        }
        else
        {
        //crumbking: this is just a copy/paste of the membersettings layout with small changes in some words, needs a coder ;)
 
        ?>
        <form action="" method="post">
        <fieldset>
            <legend><?= $words->get('GroupsJoinTheGroup') ;?><?= $this->group->Name ?></legend>
            <input type='hidden' name='member_id' value='<?= $this->member->id ;?>' />
            <input type='hidden' name='group_id' value='<?= $membershipinfo->IdGroup ;?>' />
                <label for="comment"><?= $words->get('GroupsMemberComments') ;?></label><br />
                <textarea id="comment" name="membershipinfo_comment" cols="60" rows="5" class="long" ></textarea>
            <div class="row">
                <label><?= $words->get('GroupsMemberAcceptMail') ;?>:  </label>
                <input id='no_option' type="radio" value="no" name="membershipinfo_acceptgroupmail" />
                <label for="no_option"><?= $words->get('no') ;?></label>
                <input id='yes_option' type="radio" value="yes" name="membershipinfo_acceptgroupmail" />
                <label for="yes_option"><?= $words->get('yes') ;?></label><br /><br />
                <?= $words->get('ValuesCanBeChangedLaterInMemberSettings') ;?>
            </div> <!-- row -->
                    <h3><?= $words->get('GroupsJoinNamedGroup', $this->getGroupTitle()); ?></h3>
        <span class="button"><a href="groups/<?=$this->group->id ?>/join/true"><?= $words->get('GroupsGetMeIn'); ?></a></span>
        <span class="button"><a href="groups/<?=$this->group->id ?>"><?= $words->get('GroupsDontGetMeIn'); ?></a></span>
            </fieldset>           
        </form>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'join';
    }
}

?>
