<?php


//------------------------------------------------------------------------------------
/**
 * base class for lastcomment page,
 *
 */

class LastCommentsPage extends PageWithActiveSkin {

    public function __construct($_Data) {
        $this->Data=$_Data;
    }

    protected function leftSidebar()
    {
	/*
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        ?>
        <h3><?= $words->get('GroupsActions'); ?></h3>
        <ul class="linklist">
            <li><a href="groups"><?= $words->get('GroupsOverview'); ?></a></li>
            <li><a href="groups/mygroups"><?= $words->get('GroupsMyGroups'); ?></a></li>
        </ul>
        <?
		*/
    }
    

    protected function getLastCommentsTitle() {
        return $this->getWords()->getBuffered(
            'LastCommentsTitlePage'
        );
    }

    protected function column_col3() {
		$data=$this->Data ;
		$styles = array( 'highlight', 'blank' ); // alternating background for table rows
		$words = new MOD_words();

		$iiMax = count($data) ; // This retrieve the number of comments

        require ('templates/lastcomments.php');    
		
    } // end of column_col3

    
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="lastcomments"><?= $words->get('LastCommentsTitlePage');?></a> </h1>
        </div>
        </div>
        <?php
    }
    
    protected function getTopmenuActiveItem()
    {
		return ;

		
    }
    
    protected function getSubmenuItems()
    {
        $items = array();
        
/*
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
*/
        return $items;
    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/groups.css?2';
       $stylesheets[] = 'styles/css/minimal/screen/custom/forums.css?4';
       return $stylesheets;
    }
    
    protected function getStylesheetPatches() {
       $stylesheets[] = 'styles/css/minimal/screen/patches/patch_3col.css';
       return $stylesheets;
    }

}

?>
