<?php

/**
 * This page asks if the user wants to join the group
 *
 */
class GroupJoinPage extends GroupBasePage
{
    protected function column_col3()
    {
        if (!APP_user::isBWLoggedIn('NeedMore,Pending')) {
            $widg = $this->createWidget('LoginFormWidget');
            $widg->render();
        } else {
        ?><h3>Join the group "<?=$this->getGroupTitle() ?>" ?</h3>
        Your choice.<br>
        <span class="button"><a href="groups/<?=$this->getGroupId() ?>/join/yes">Join</a></span>
        <span class="button"><a href="groups/<?=$this->getGroupId() ?>/join/no">Cancel</a></span>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'join';
    }
}

?>