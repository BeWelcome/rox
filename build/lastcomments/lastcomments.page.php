<?php


//------------------------------------------------------------------------------------
/**
 * base class for lastcomment page,
 *
 */

class LastCommentsPage extends PageWithActiveSkin {

    /**
	Constructor
	
	@$_Data has been previously filled with the dynamy data to display
	@type is the request parameter 1 and allows to choose for a speciic tem^late
	
	**/
	public function __construct($_Data,$type="LastComments") {
        $this->BW_Right = MOD_right::get();
        $this->Data=$_Data;
        $this->Type=$type ;
    }

    protected function leftSidebar() {
        ?>
        <h3><?= $this->getWords()->get('Actions'); ?></h3>
		<?php
		if ($this->Type=="LastComments") {
			?>
			<ul class="linklist">
				<li><a href="lastcomments/commentofthemoment"><?= $this->getWords()->get('commentofthemomentTitlePage'); ?></a></li>
			</ul>
			<?php
		}
		else if ($this->Type=="commentofthemoment") {
			?>
			<ul class="linklist">
				<li><a href="lastcomments"><?= $this->getWords()->get('LastCommentsTitlePage'); ?></a></li>
			</ul>
			<?php
		}
		?>
		<ul class="linklist">
			<li><a href="bw/viewcomments.php?MyComment=1"><?= $this->getWords()->get('MyComments'); ?></a></li>
		</ul>
        <?
    }



    protected function getLastCommentsTitle() {

		if ($this->Type=="LastComments") {
			return $this->getWords()->get('LastCommentsTitlePage');
		}
		else if ($this->Type=="commentofthemoment") {
			return $this->getWords()->get('commentofthemomentTitlePage');
		}
    }

    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return  $this->getLastCommentsTitle();
    }
    
	
    protected function column_col2() {
	}
	
    protected function column_col3() {
		$data=$this->Data ;
		$styles = array( 'highlight', 'blank' ); // alternating background for table rows
		$words = new MOD_words();

		$iiMax = count($data) ; // This retrieve the number of comments
		if ($this->Type=="LastComments") {
			require ('templates/lastcomments.php');    
		}
		else if ($this->Type=="commentofthemoment") {
			require ('templates/commentofthemoment.php');    
		}
		
    } // end of column_col3

    
    protected function teaserContent() {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><?= $this->getLastCommentsTitle();?></h1>
        </div>
        </div>
        <?php
    }
    
    protected function getTopmenuActiveItem()    {
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
//       $stylesheets[] = 'styles/css/minimal/screen/custom/groups.css';
       $stylesheets[] = 'styles/css/minimal/screen/custom/forums.css';
       return $stylesheets;
    }
    
    protected function getStylesheetPatches() {
       $stylesheets[] = 'styles/css/minimal/screen/patches/patch_3col.css';
       return $stylesheets;
    }

}

?>
