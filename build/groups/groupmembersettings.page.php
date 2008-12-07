<?php

class GroupMemberSettingsPage extends GroupsBasePage
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
            $membershipinfo = $this->member->getGroupMembership($this->group);
        ?>
        <h3>Your settings for <?= $this->group->Name ?></h3>
        <form>
            <label for="comment">Membership comment</label> <input id="comment" type="text" value="<?= (($membershipinfo->Comment != '' ) ? htmlspecialchars($membershipinfo->Comment) : '' ); ?>" name="membershipinfo_comment" /><br />
            <label for="acceptgroupmail">Accept mail from the group</label>
                <input id="acceptgroupmail" type="radio" value="false" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'no') ? 'checked ' : '' ); ?>/>
                <input type="radio" value="true" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'no') ? '' : 'checked ' ); ?>/>
        

        </form>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'settings';
    }
}


