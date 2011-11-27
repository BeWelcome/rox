<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/
    /** 
     * @author Micha
     * @author Globetrotter_tt
     */

    /** 
     * members base page
     * 
     * @package    Apps
     * @subpackage Members
     * @author     Micha
     * @author     Globetrotter_tt
     */
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
        if ($logged_user = $this->model->getLoggedInMember())
        {
            $TCom = $member->get_comments_commenter($logged_user->id);
        }
        $ViewForumPosts=$words->get("ViewForumPosts",$member->forums_posts_count()) ;
        if ($this->myself) {
            $tt=array(
                array('editmyprofile', 'editmyprofile', $ww->EditMyProfile, 'editmyprofile'),
                array('mypreferences', 'mypreferences', $ww->MyPreferences, 'mypreferences'),
                );

            if ($this instanceof EditMyProfilePage)
            {
                $tt[] = array('deleteprofile', 'deleteprofile', $ww->DeleteProfile, 'deleteprofile');
            }

            $tt[] = array('myvisitors', "myvisitors", $ww->MyVisitors, 'myvisitors');
            $tt[] = array('space', '', '', 'space');

            $tt[] = array('profile', "members/$username", $ww->MemberPage);
            $tt[] = array('comments', "members/$username/comments", $ww->ViewComments.'('.$comments_count['all'].')');
            $tt[] = array('trips', "trip/show/$username", $ww->Trips);
            $tt[] = array('blogs', "blog/$username", $ww->Blog);
            $tt[] = array('gallery', "gallery/show/user/$username", $ww->Gallery);
            $tt[] = array('forum', "forums/member/$username", $ViewForumPosts);
        } else {
            $mynotes_count = $member->count_mynotes(); 
            if ($mynotes_count>0) {
                $mynotelink='bw/mycontacts.php?IdContact='.$this->member->id ;
                $mynotelinkname=$words->get('ViewMyNotesForThisMember') ;
            }
            else {
                $mynotelink='bw/mycontacts.php?IdContact='.$this->member->id.'&amp;action=add' ;
                $mynotelinkname=$words->get('AddToMyNotes') ;
            }
            $tt= array(
                array('messagesadd', "messages/compose/$username", $ww->ContactMember, 'messagesadd'),
                (isset($TCom[0])) ? array('commmentsadd', "members/$username/comments/edit", $ww->EditComments, 'commentsadd') : array('commmentsadd', "members/$username/comments/add", $ww->AddComments, 'commentsadd'),
                array('relationsadd', "members/$username/relations/add", $ww->addRelation, 'relationsadd'),
                array('verificationadd', "verification/$username", $ww->addVerification, 'verificationadd'),
                array('space', '', '', 'space'),

                array('profile', "members/$username", $ww->MemberPage),
                array('comments', "members/$username/comments", $ww->ViewComments.'('.$comments_count['all'].')'),
                array('trips', "trip/show/$username", $ww->Trips),
                array('blogs', "blog/$username", $ww->Blog),
                array('gallery', "gallery/show/user/$username", $ww->Gallery),
                array('forum', "forums/member/$username", $ViewForumPosts),
                array('notes',$mynotelink,$mynotelinkname)
            );
        }
        if (MOD_right::get()->HasRight('SafetyTeam') || MOD_right::get()->HasRight('Admin'))
        {
            $tt[] = array('admin',"members/{$username}/adminedit",'Admin: Edit Profile');
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
                <a href="#medium_box" id="medium"><img src="members/avatar/<?=$member->Username?>?150" alt="Picture of <?=$member->Username?>" class="framed" height="150" width="150"/></a>
                <div id="medium_box" class="hidden">
                  <img src="members/avatar/<?=$member->Username?>?500" alt="Picture of <?=$member->Username?>" />
                </div>
                <script type="text/javascript">
                    new FancyZoom('medium');
                </script>
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
