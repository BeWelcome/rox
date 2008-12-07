<?php

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
        ?>
        <h3><?= $words->get('GroupsJoinNamedGroup', $this->getGroupTitle()); ?></h3>
        <span class="button"><a href="groups/<?=$this->group->id ?>/join/true"><?= $words->get('GroupsGetMeIn'); ?></a></span>
        <span class="button"><a href="groups/<?=$this->group->id ?>"><?= $words->get('GroupsDontGetMeIn'); ?></a></span>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'join';
    }
}

?>
