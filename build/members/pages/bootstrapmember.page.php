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
     * @author crumbking
     */

    /** 
     * bootstrap members base page
     * 
     * @package    Apps
     * @subpackage Members
     * @author     crumbking
     */
class BootstrapMemberPage extends PageWithActiveSkin
{
    protected function getPageTitle() 
    {
        $username = $this->member->Username;
        return $username . ' - BeWelcome';
    }

    protected function getTopmenuActiveItem()
    {
        return 'bootstrapprofile';
    }
    
    protected function teaserContent()
    {
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $words = $layoutkit->getWords();
        require(BUILD_DIR .'members/templates/bootstrapmembersteaser.php');
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

        $viewForumPosts = $words->get("ViewForumPosts",$member->forums_posts_count());
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
                array('mynotes', 'mynotes', $words->get('MyNotes', $mynotes_count), 'mynotes')
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

            $tt[] = array('bootstrapprofile', "members/$username", $ww->MemberPage);
            $tt[] = array('comments', "members/$username/comments", $ww->ViewComments.' ('.$comments_count['all'].')');
            $tt[] = array('gallery', "gallery/show/user/$username/pictures", $ww->Gallery . ' (' . $galleryItemsCount . ')');
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
                array('bootstrapprofile', "members/$username", $ww->MemberPage),
                array('comments', "members/$username/comments", $ww->ViewComments.' ('.$comments_count['all'].')'),
                array('gallery', "gallery/show/user/$username/pictures", $ww->Gallery . ' (' . $galleryItemsCount . ')'),
                array('blogs', "blog/$username", $ww->Blog),
                array('trips', "trip/show/$username", $ww->Trips)
            );
            if ($linkMembersForumPosts) {
                $tt[] = array('forum', "forums/member/$username", $viewForumPosts);
            }
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
        protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }

    protected function submenu() {
    }

    protected function teaserReplacement() {
        $this->__call('teaserContent', array());
        //parent::submenu();
    }

    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/profile.css?2';
       return $stylesheets;
    }
}
