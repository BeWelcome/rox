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
            <legend><?= $words->get('GroupsMemberSettings') ;?><?= $this->group->Name ?></legend>
            <input type='hidden' name='member_id' value='<?= $this->member->id ;?>' />
            <input type='hidden' name='group_id' value='<?= $membershipinfo->IdGroup ;?>' />
            <div class="row">
                <label for="comment"><?= $words->get('GroupsMemberComments') ;?></label><br />
                <textarea id="comment" name="membershipinfo_comment" cols="60" rows="5" class="long" ><?= (($membershipinfo->Comment != '' ) ? htmlspecialchars($words->mTrad($membershipinfo->Comment)) : '' ); ?></textarea>
            </div> <!-- row -->
            <div class="row">
                <label><?= $words->get('GroupsMemberAcceptMail') ;?>:  </label>
                <input id='no_option' type="radio" value="no" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'no') ? 'checked="checked" ' : '' ); ?>/>
                <label for="no_option"><?= $words->get('no') ;?></label>
                <input id='yes_option' type="radio" value="yes" name="membershipinfo_acceptgroupmail" <?= (($membershipinfo->IacceptMassMailFromThisGroup == 'yes') ? 'checked="checked" ' : '' ); ?>/>
                <label for="yes_option"><?= $words->get('yes') ;?></label>
            </div> <!-- row -->
            <p style="padding-top: 2em"><input type='submit' value='<?= $words->get('GroupsUpdateMemberSettings') ;?>' /></p>
            </fieldset>
        </form>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'membersettings';
    }
}


