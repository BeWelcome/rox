<?php


class MemberPage extends PageWithActiveSkin
{
    protected function getPageTitle()
    {
        $member = $this->member;
        return $this->wwsilent->ProfilePageFor($member->Username)." - BeWelcome";
    }
    
    
    protected function getTopmenuActiveItem()
    {
        return 'profile';
    }
    
    
    protected function getSubmenuItems()
    {
        $username = $this->member->Username;
        $member = $this->member;
        
        $words = $this->getWords();
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;
        $comments_count = $member->count_comments(); 
        $ViewForumPosts=$words->get("ViewForumPosts",$member->forums_posts_count()) ;
        if ($this->myself) {
            $tt=array(
                array('editmyprofile', 'editmyprofile', $ww->EditMyProfile, 'editmyprofile'),
                array('mypreferences', 'mypreferences', $ww->MyPreferences, 'mypreferences'),
                array('myvisitors', "myvisitors", $ww->MyVisitors, 'myvisitors'),
                array('space', '', '', 'space'),

                array('profile', "members/$username", $ww->MemberPage),
                array('comments', "members/$username/comments", $ww->ViewComments.'('.$comments_count['all'].')'),
                array('trips', "trip/show/$username", $ww->Trips),
                array('blogs', "blog/$username", $ww->Blog),
                array('gallery', "gallery/show/user/$username", $ww->Gallery),
                array('forum', "forums/member/$username", $ViewForumPosts) 
            );
        } else {
            $tt= array(
                array('messagesadd', "messages/compose/$username", $ww->ContactMember, 'messagesadd'),
                array('commmentsadd', "members/$username/comments/add", $ww->addcomments, 'commentsadd'),
                array('relationsadd', "members/$username/relations/add", $ww->addRelation, 'relationsadd'),
                array('space', '', '', 'space'),

                array('profile', "members/$username", $ww->MemberPage),
                array('comments', "members/$username/comments", $ww->ViewComments.'('.$comments_count['all'].')'),
                array('trips', "trip/show/$username", $ww->Trips),
                array('blogs', "blog/$username", $ww->Blog),
                array('gallery', "gallery/show/user/$username", $ww->Gallery),
                array('forum', "forums/member/$username", $ViewForumPosts),
                array('notes','bw/mycontacts.php?IdContact='.$this->member->id,$words->get('ViewMyNotesForThisMember'))
            );
        }
        if (MOD_right::get()->HasRight('SafetyTeam') || MOD_right::get()->HasRight('Accepter','All')) {
            // array_push($tt,array('admin',"members/{$username}/adminedit",'Admin: Edit Profile') ) ;
            array_push($tt,array('admin',"bw/admin/updatemandatory.php?username={$username}",'Admin: Edit Profile') ) ;
        }
        if (MOD_right::get()->HasRight('Rights')) {
            array_push($tt,array('admin','bw/admin/adminrights.php?username='.$username,'AdminRights') ) ;
        }
        if (MOD_right::get()->HasRight('Flags')) {
            array_push($tt,array('admin','bw/admin/adminflags.php?username='.$username,'AdminFlags') ) ;
        }
        if (MOD_right::get()->HasRight('Logs')) {
            array_push($tt,array('admin','bw/admin/adminlogs.php?Username='.$username,'See Logs') ) ;
        }
        if (MOD_right::get()->HasRight('Accepter','All')) {
            array_push($tt,array('admin','bw/editmyprofile.php?cid='.$username,'BW Edit Profile #'.$this->member->id) ) ;
        }
        return($tt) ;
    }
    
    protected function columnsArea()
    {
        $side_column_names = parent::getColumnNames();
        $mid_column_name = array_pop($side_column_names);
        ?>
        <?php foreach ($side_column_names as $column_name) { ?>

          <div id="<?=$column_name ?>">
            <div id="<?=$column_name ?>_content" class="clearfix">
              <? $name = 'column_'.$column_name ?>
              <?php $this->$name() ?>
            </div> <!-- <?=$column_name ?>_content -->
          </div> <!-- <?=$column_name ?> -->

        <?php } ?>

          <div id="<?=$mid_column_name ?>">
            <div id="<?=$mid_column_name ?>_content" class="clearfix">
              <?php $this->teaserReplacement(); ?>
              <? $name = 'column_'.$mid_column_name; ?>
                <?php $this->$name() ?>
              <?php $this->$name ?>
            </div> <!-- <?=$mid_column_name ?>_content -->
            <!-- IE Column Clearing -->
            <div id="ie_clearing">&nbsp;</div>
            <!-- Ende: IE Column Clearing -->
          </div> <!-- <?=$mid_column_name ?> -->
        <?php
    }

    protected function submenu() {
    }

    protected function teaserReplacement() {
        $this->__call('teaserContent', array());
        //parent::submenu();
    }

    protected function leftsidebar() {
        $member = $this->member;
        $words = $this->getWords();
        $piclink = $this->myself ? 'editmyprofile#profilepic':'gallery/show/user/'.$member->Username;
        ?>
        <div id="profile_pic" >
                <a href="<?=$piclink?>"><img src="members/avatar/<?=$member->Username?>" alt="Picture of <?$member->Username?>" class="framed" /></a>
        </div> <!-- profile_pic -->                

            <ul class="linklist" id="profile_linklist">
              <?php
          // $this->__call('leftsidebar', array());

        $active_menu_item = $this->getSubmenuActiveItem();
        foreach ($this->getSubmenuItems() as $index => $item) {
            $name = $item[0];
            $url = $item[1];
            $label = $item[2];
            $class = isset($item[3]) ? $item[3] : '';
            if ($name === $active_menu_item) {
                $attributes = ' class="active '.$class.'"';
                $around = '';
            } else {
                $attributes = ' class="'.$class.'"';
                $around = '';
            }

            ?><li id="sub<?=$index ?>" <?=$attributes ?>>
              <?=$around?><a style="cursor:pointer;" href="<?=$url ?>"><span><?=$label ?></span></a><?=$around?>
              <?=$words->flushBuffer(); ?>
            </li>
            <?php

        }

            ?></ul>
<?php
    }


    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/profile.css';
       return $stylesheets;
    }
    
    /*
     * The idea was that stylesheetpatches was for MSIE
     */
    protected function getStylesheetPatches()
    {
        //$stylesheet_patches = parent::getStylesheetPatches();
        $stylesheet_patches[] = 'styles/css/minimal/patches/patch_2col_left.css';
        return $stylesheet_patches;
    }

    
    
    protected function teaserContent()
    {
/*        $this->__call('teaserContent', array()); */
    }
}


?>
