<?php

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
            $resultmsg = (($redirected->result) ? "<p>{$words->get('GroupMemberSettingsUpdated')}</p>" : "<p>{$words->get('GroupMemberSettingsNotUpdated')}</p>");
            $problemmsg = (($redirected->problems) ? "<p>{$words->get('GroupMemberSettingsProblems')}</p>" : '');
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
        <h3>Your group settings for <?= $this->group->Name ?></h3>
        <?= $resultmsg; ?>
        <?= $problemmsg; ?>
        <form action="" method="post">
        <?= $callbacktag; ?>
            <input type='hidden' name='member_id' value='<?= $this->member->id ;?>' />
            <input type='hidden' name='group_id' value='<?= $membershipinfo->IdGroup ;?>' />
            <label for="comment">Membership comment</label>
            <input id="comment" type="text" value="<?= (($membershipinfo->Comment != '' ) ? htmlspecialchars($words->mTrad($membershipinfo->Comment)) : '' ); ?>" name="membershipinfo_comment" /><br />
            <label for="acceptgroupmail">Accept mail from the group</label>
            <span id="acceptgroupmail">
                <label for="no_option">No</label>
                <input id='no_option' type="radio" value="no" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'no') ? 'checked ' : '' ); ?>/>
                <label for="yes_option">Yes</label>
                <input id='yes_option' type="radio" value="yes" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'yes') ? 'checked ' : '' ); ?>/>
            </span><br />
        
            <input type='submit' value='<?= $words->get('GroupsUpdateMemberSettings') ;?>' />
        </form>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'settings';
    }
}


