<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GroupsBasePage extends PageWithActiveSkin
{

    /**
     * An array of messages that should be shown to the user
     * They are strings to be used in words->get
     *
     * @var array
     */
    protected $_messages;

    /**
     * set a message for the member to see
     *
     * @param string $message - Message to set
     * @access public
     */
    public function setMessage($message)
    {
        if (!isset($this->_messages))
        {
            $this->_messages = array();
        }

        $this->_messages[] = $message;
    }

    /**
     * get all set messages
     *
     * @access public
     * @return array
     */
    public function getMessages()
    {
        if (isset($this->_messages) && is_array($this->_messages))
        {
            return $this->_messages;
        }
        else
        {
            return array();
        }
    }


    protected function leftSidebar()
    {
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        ?>
        <h3><?= $words->get('GroupsActions'); ?></h3>
        <ul class="linklist">
            <li><a href="groups"><?= $words->get('GroupsOverview'); ?></a></li>
            <li><a href="groups/mygroups"><?= $words->get('GroupsMyGroups'); ?></a></li>
        </ul>
        <?
    }
    

    protected function getGroupTitle() {
        return $this->getWords()->getBuffered(
            'Group_'.$this->group->Name
        );
    }
    
    protected function isGroupMember() {
        if (!$this->group || !$this->member)
        {
            return false;
        }
        else
        {
            return $this->group->isMember($this->member);
        }
    }
    
    
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups"><?= $words->get('Groups');?></a> &raquo; <a href="groups/<?=$this->group->id ?>"><?=$this->group->Name ?></a></h1>
        </div>
        </div>
        <?php
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'groups';
    }
    
    protected function getSubmenuItems()
    {
        $items = array();
        

        if ($this->group)
        {
            $group_id = $this->group->id;
            $items[] = array('start', 'groups/'.$group_id, 'Overview');
            $items[] = array('forum', 'groups/'.$group_id.'/forum', 'Discussions');
            $items[] = array('wiki', 'groups/'.$group_id.'/wiki', 'Wiki');
            $items[] = array('members', 'groups/'.$group_id.'/members', 'Members');
            if ($this->isGroupMember())
            {
                $items[] = array('membersettings', 'groups/'.$group_id.'/membersettings', 'Member settings');
            }
            if ($this->member && $this->member->hasPrivilege('GroupsController', 'GroupSettings', $this->group))
            {
                $items[] = array('admin', "groups/{$this->group->getPKValue()}/groupsettings", 'Group settings');
            }

        }
        return $items;
    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/groups.css';
       $stylesheets[] = 'styles/css/minimal/screen/custom/forums.css';
       return $stylesheets;
    }
    
    protected function getStylesheetPatches() {
       $stylesheets[] = 'styles/css/minimal/screen/patches/patch_3col.css';
       return $stylesheets;
    }




}

?>
