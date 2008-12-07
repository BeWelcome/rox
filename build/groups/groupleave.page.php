<?php


  /* 
   * groups.pages.php is for code related to showing more groups
   * group.pages.php is for code related to showing one group */




//------------------------------------------------------------------------------------
/**
 * This page asks if the user wants to leave the group
 *
 */
class GroupLeavePage extends GroupsBasePage
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
        <h3><?= $words->get('GroupsLeaveNamedGroup', $this->getGroupTitle()); ?></h3>
        <span class="button"><a href="groups/<?=$this->group->id ?>/leave/true"><?= $words->get('GroupsYesGetMeOut');?></a></span>
        <span class="button"><a href="groups/<?=$this->group->id ?>"><?= $words->get('GroupsNoIStay');?></a></span>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'leave';
    }
}


?>
