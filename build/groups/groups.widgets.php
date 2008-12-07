<?php


//------------------------------------------------------------------------------------
/**
 * This widget shows the forum for a group page
 *
 */
class GroupForumWidget  // extends ForumBoardWidget
{
    public function render()
    {
        echo 'group forum';
    }
    
    public function setGroup($group)
    {
        // extract information from the $group object
    }
}

//------------------------------------------------------------------------------------
/**
 * This widget shows a list of members with pictures.
 */
class GroupMemberlistWidget  // extends MemberlistWidget?
{
    private $_group;
    
    public function render()
    {
        $memberships = $this->_group->getMembers();
        for ($i = 0; $i < 10 && $i < count($memberships); $i++)
        {
            ?>
            <div style="float:left; border:1px solid #fec;">
                <?=MOD_layoutbits::linkWithPicture($memberships[$i]->Username) ?><br>
                <?=$memberships[$i]->Username ?>
            </div>
            <?php
        }
        ?>
        <div style="clear:both;"></div>
        <?php
    }
    
    public function setGroup($group)
    {
        // extract memberlist information from the $group object
        $this->_group = $group;
    }
}





?>
