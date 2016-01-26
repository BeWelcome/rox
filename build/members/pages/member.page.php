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
        $lang = $this->model->get_profile_language();
        $profile_language_code = $lang->ShortCode;
        $words = $this->getWords();
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;
        $comments_count = $member->count_comments();
        $logged_user = $this->model->getLoggedInMember();
        if ($logged_user)
        {
            $TCom = $member->get_comments_commenter($logged_user->id);
            $note = $logged_user->getNote($member);
        }

        $galleryItemsCount = $member->getGalleryItemsCount();

        $viewForumPosts = $words->get("ViewForumPosts",'<span class="label label-primary pull-xs-right">' . $member->forums_posts_count() . '</span>');
        $membersForumPostsPagePublic = $member->getPreference("MyForumPostsPagePublic", $default = "No");
        $linkMembersForumPosts = false;
        if ($membersForumPostsPagePublic == "Yes") {
            $linkMembersForumPosts = true;
        }
        if ($logged_user && $logged_user->getPKValue() == $member->getPKValue()) {
            $linkMembersForumPosts = true;
        }
        if (MOD_right::get()->HasRight('SafetyTeam') || MOD_right::get()->HasRight('Admin') || MOD_right::get()->HasRight('ForumModerator')) {
            $linkMembersForumPosts = true;
        }

        $mynotes_count = $member->count_mynotes();
        if ($this->myself) {
            $tt=array(
                array('editmyprofile', 'editmyprofile/' . $profile_language_code, $ww->EditMyProfile, 'editmyprofile'),
                array('mypreferences', 'mypreferences', $ww->MyPreferences, 'mypreferences'),
                array('mynotes', 'mynotes', $words->get('MyNotes', '<span class="label label-primary pull-xs-right">' . $mynotes_count . '</span>'), 'mynotes')
                );

            if ($this instanceof EditMyProfilePage)
            {
                $tt[] = array('deleteprofile', 'deleteprofile', $ww->DeleteProfile, 'deleteprofile');
                if ($member->Status <> 'ChoiceInactive') {
                    $tt[] = array('setprofileinactive', 'setprofileinactive', $ww->SetProfileInactive, 'setprofileinactive');
                } else {
                    $tt[] = array('setprofileactive', 'setprofileactive', $ww->SetProfileActive);
                }
            }

            $showVisitors = $member->getPreference('PreferenceShowProfileVisits',
                'Yes');
            if ($showVisitors == 'Yes') {
                $tt[] = array('myvisitors', "myvisitors", $ww->MyVisitors, 'myvisitors');
            }
            $tt[] = array('space', '', '', 'space');

            $tt[] = array('profile', "members/$username", $ww->MemberPage);
            $tt[] = array('comments', "members/$username/comments", $ww->ViewComments.' <span class="label label-primary pull-xs-right">'.$comments_count['all'].'</span>');
            $tt[] = array('gallery', "gallery/show/user/$username/pictures", $ww->Gallery . ' <span class="label label-primary pull-xs-right">' . $galleryItemsCount . '</span>');
            $tt[] = array('blogs', "blog/$username", $ww->Blog);
            $tt[] = array('trips', "trip/show/$username", $ww->Trips);
            $tt[] = array('forum', "forums/member/$username", $viewForumPosts);
        } else {
            if (isset($note)) {
                $mynotewordsname=$words->get('NoteEditMyNotesOfMember') ;
                $mynotelinkname= "members/$username/note/edit" ;
            }
            else {
                $mynotewordsname=$words->get('NoteAddToMyNotes') ;
                $mynotelinkname= "members/$username/note/add" ;
            }
            $tt= array(
                array('messagesadd', "messages/compose/$username", $ww->ContactMember, 'messagesadd'),
                (isset($TCom[0])) ? array('commmentsadd', "members/$username/comments/edit", $ww->EditComments, 'commentsadd') : array('commmentsadd', "members/$username/comments/add", $ww->AddComments, 'commentsadd'),
                array('relationsadd', "members/$username/relations/add", $ww->addRelation, 'relationsadd'),
                array('notes', $mynotelinkname, $mynotewordsname, 'mynotes'),
                // Verification link hidden in accordance with trac ticket 1992 until bugs which limit the validity of verification system are resolved:
                /**array('verificationadd', "verification/$username", $ww->addVerification, 'verificationadd'),*/
                array('space', '', '', 'space'),
                array('profile', "members/$username", $ww->MemberPage),
                array('comments', "members/$username/comments", $ww->ViewComments.' <span class="label label-primary pull-xs-right">'.$comments_count['all'].'</span>'),
                array('gallery', "gallery/show/user/$username/pictures", $ww->Gallery . ' <span class="label label-primary pull-xs-right">' . $galleryItemsCount . '</span>'),
                array('blogs', "blog/$username", $ww->Blog),
                array('trips', "trip/show/$username", $ww->Trips)
            );
            if ($linkMembersForumPosts) {
                $tt[] = array('forum', "forums/member/$username", $viewForumPosts);
            }
        }
        if (MOD_right::get()->HasRight('SafetyTeam') || MOD_right::get()->HasRight('Admin'))
        {
            $tt[] = array('adminedit',"members/{$username}/adminedit",'Admin: Edit Profile');
        }
        if (MOD_right::get()->HasRight('Rights')) {
            array_push($tt,array('adminrights','admin/rights/list/members/'.$username,$ww->AdminRights) ) ;
        }
        if (MOD_right::get()->HasRight('Flags')) {
            array_push($tt,array('adminflags', 'admin/flags/list/members/'. $username, $ww->AdminFlags) ) ;
        }
        if (MOD_right::get()->HasRight('Logs')) {
            array_push($tt,array('admin','bw/admin/adminlogs.php?Username='.$username,$ww->AdminLogs) ) ;
        }
        return($tt) ;
    }
        protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col1_left', 'col3_right');
    }
    protected function columnsArea()
    {
        ?>
        <div class="bw_row">
          <div id="col1_left">
            <div id="col1_content">
              <? $name = 'column_col1';?>
              <?php $this->$name() ?>
            </div>
          </div> 
          <div id="col3_right">
            <div id="col3_content">
              <?php $this->teaserReplacement(); ?>
              <? $name = 'column_col3';?>
                <?php $this->$name() ?>
              <?php $this->$name ?>
            </div> 
          </div>
        </div>
        <?php
    }

    protected function submenu() {
    }

    protected function teaserReplacement() {
        $this->__call('teaserContent', array());
        //parent::submenu();
    }

    protected function leftsidebar() {
        // TODO: move HTML to a template
        $member = $this->member;
        $words = $this->getWords();
        $thumbnail_url = 'members/avatar/'.$member->Username.'?150';
        $picture_url = 'members/avatar/'.$member->Username.'?500';
        ?>

        <div id="profile_pic" >
                <a href="<?=$picture_url?>" id="profile_image"><img src="<?=$thumbnail_url?>" alt="Picture of <?=$member->Username?>" class="framed" height="150" width="150"/></a>
                <div id="profile_image_zoom_content" class="hidden">
                  <img src="<?=$picture_url?>" alt="Picture of <?=$member->Username?>" />
                </div>
                <script type="text/javascript">
                    // Activate FancyZoom for profile picture
                    // (not for IE, which don't like FancyZoom)
                    if (typeof FancyZoom == "function" && is_ie === false) {
                      new FancyZoom('profile_image');
                    }
                </script>
        </div> <!-- profile_pic -->

            <div class="list-group m-t-1">
              <?php

        $active_menu_item = $this->getSubmenuActiveItem();
        foreach ($this->getSubmenuItems() as $index => $item) {
            $name = $item[0];
            $url = $item[1];
            $label = $item[2];
            $class = isset($item[3]) ? $item[3] : 'leftpadding';
            if ($name === $active_menu_item) {
                $attributes = ' class="list-group-item active '.$class.'"';
                $around = '';
            } else {
                $attributes = ' class="list-group-item '.$class.'"';
                $around = '';
            }

            ?>
              <?=$around?><a <?=$attributes ?> href="<?=$url ?>"><?=$label ?></a><?=$around?>
              <?=$words->flushBuffer(); ?>
            <?php

        }

            ?></div>
<?php
    }


    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        // $stylesheets[] = 'styles/css/minimal/screen/custom/profile.css?2';
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

    /*
     * @return HTML snippet with a form to select the status of a user
     */
    public function statusForm($member)
    {
        $form = '';
        if ($this->statuses) {
            $layoutkit = $this->layoutkit;
            $formkit = $layoutkit->formkit;
            $callbackTags = $formkit->setPostCallback('MembersController', 'setStatusCallback');
            $logged_member = $this->model->getLoggedInMember();
            if ($logged_member && $logged_member->hasOldRight(array('Admin' => '', 'SafetyTeam' => '', 'Accepter' => '', 'Profile' => ''))) {
                $form .= '<div><form method="post" name="member-status" id="member-status">' . $callbackTags;
                $form .= '<input type="hidden" name="member-id" value="' . $member->id . '">';
                $form .= '<select name="new-status">';
                foreach ($this->statuses as $status) {
                    $form .= '<option value="' . $status . '"';
                    if ($status == $member->Status) {
                        $form .= ' selected="selected"';
                    }
                    $form .= '>' . $this->words->getSilent('MemberStatus' .
                            $status) . '</option>';
                }
                $form .= '</select>&nbsp;&nbsp;<input type="submit" value="Submit"/>';
                $form .= '</form>' . $this->words->FlushBuffer() . '</div>';
            }
        }
        return $form;
    }

    public function memberSinceDate($member)
    {
        $dateSince = '';
        $logged_member = $this->model->getLoggedInMember();
        if ($logged_member
            && $logged_member->hasOldRight(
                array('SafetyTeam' => '')
            )
        ) {
            $dateSince = ' ('.$member->created.')';
        }

        return $dateSince;
    }
}
