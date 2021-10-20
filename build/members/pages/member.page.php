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
     * members base page
     *
     * @author Micha
     * @author Globetrotter_tt
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
        $accommodation = $this->member->Accomodation;
        $member = $this->member;
        $lang = $this->model->get_profile_language();
        $profile_language_code = $lang->ShortCode;
        $words = $this->getWords();
        $rights = MOD_Right::get();

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

        $viewForumPosts = $words->get("ViewForumPosts",'<span class="badge badge-primary u-rounded-full u-w-20 u-h-20 u-inline-flex u-items-center u-justify-center pull-right">' . $member->forums_posts_count() . '</span>');
        $membersForumPostsPagePublic = $member->getPreference("MyForumPostsPagePublic", $default = "No");
        $linkMembersForumPosts = false;
        if ($membersForumPostsPagePublic == "Yes") {
            $linkMembersForumPosts = true;
        }
        if ($logged_user && $logged_user->getPKValue() == $member->getPKValue()) {
            $linkMembersForumPosts = true;
        }
        if ($rights->HasRight('SafetyTeam') || $rights->HasRight('Admin') || $rights->HasRight('ForumModerator')) {
            $linkMembersForumPosts = true;
        }

        $mynotes_count = $member->count_mynotes();
        if ($this->myself) {
            $tt=array(
                array('editmyprofile', 'editmyprofile/' . $profile_language_code, '<i class="fa fa-fw fa-edit"></i> ' . $ww->EditMyProfile, 'editmyprofile'),
                array('mypreferences', 'mypreferences', '<i class="fa fa-fw fa-cogs"></i> ' . $ww->MyPreferences, 'mypreferences'),
                array('mydata', 'mydata', '<i class="fa fa-fw fa-database"></i> ' . $ww->MyData, 'mydata'),
                array('mynotes', 'mynotes', '<i class="fa fa-fw fa-sticky-note"></i> ' . $words->get('MyNotes', '<span class="badge badge-primary u-rounded-full u-w-20 u-h-20 u-inline-flex u-items-center u-justify-center pull-right">' . $mynotes_count . '</span>'), 'mynotes')
                );

            if ($this instanceof EditMyProfilePage)
            {
                if ($member->Status <> 'ChoiceInactive') {
                    $tt[] = array('setprofileinactive', 'setprofileinactive', '<i class="fa fa-fw fa-edit"></i> ' . $ww->SetProfileInactive, 'setprofileinactive');
                } else {
                    $tt[] = array('setprofileactive', 'setprofileactive', '<i class="fa fa-fw fa-edit"></i> ' . $ww->SetProfileActive);
                }
                $tt[] = array('deleteprofile', 'deleteprofile', '<i class="fa fa-fw fa-times"></i> ' . $ww->DeleteProfile, 'deleteprofile');
            }

            $showVisitors = $member->getPreference('PreferenceShowProfileVisits',
                'Yes');
            if ($showVisitors == 'Yes') {
                $tt[] = array('myvisitors', "myvisitors", '<i class="fa fa-fw fa-comments invisible"></i> ' . $ww->MyVisitors, 'myvisitors');
            }
            $tt[] = array('space', '', '', 'space');

            $tt[] = array('profile', "members/$username", '<i class="fa fa-fw fa-user"></i> ' . $ww->MemberPage);
            $tt[] = array('comments', "members/$username/comments", '<i class="fa fa-fw fa-comments"></i> ' . $ww->ViewComments.' <span class="badge badge-primary u-rounded-full u-w-20 u-h-20 u-inline-flex u-items-center u-justify-center pull-right">'.$comments_count['all'].'</span>');
            if ($this->myself) {
                $tt[] = array('gallery', "gallery/manage", '<i class="fa fa-fw fa-image"></i> ' . $ww->Gallery . ' <span class="badge badge-primary u-rounded-full u-w-20 u-h-20 u-inline-flex u-items-center u-justify-center pull-right">' . $galleryItemsCount . '</span>');
            } else {
                $tt[] = array('gallery', "gallery/show/user/$username/pictures", '<i class="fa fa-fw fa-image"></i> ' . $ww->Gallery . ' <span class="badge badge-primary u-rounded-full u-w-20 u-h-20 u-inline-flex u-items-center u-justify-center pull-right">' . $galleryItemsCount . '</span>');
            }
            $tt[] = array('forum', "forums/member/$username", '<i class="far fa-fw fa-comment"></i> ' . $viewForumPosts);
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
                array('messagesadd', "new/message/$username", '<i class="fa fa-fw fa-envelope"></i> ' . $ww->ContactMember, 'messagesadd'),
                array('allmessages', "all/messages/with/$username", '<i class="fas fa-fw fa-mail-bulk"></i> ' . $words->getSilent('profile.all.messages.with'), 'allmessages'),
                (isset($TCom[0])) ? array('commmentsadd', "members/$username/comments/edit", '<i class="fa fa-fw fa-comment"></i> ' . $ww->EditComments, 'commentsadd') : array('commmentsadd', "members/$username/comments/add", '<i class="fa fa-fw fa-comment"></i> ' . $ww->AddComments, 'commentsadd'),
                array('relationsadd', "members/$username/relations/add", '<i class="fa fa-fw fa-handshake"></i> ' . $ww->addRelation, 'relationsadd'),
                array('notes', $mynotelinkname, '<i class="fa fa-fw fa-pencil-alt"></i> ' . $mynotewordsname, 'mynotes'),
                array('report', "/feedback?IdCategory=2&FeedbackQuestion=" . urlencode( $words->get('profile.report.text', $username)), '<i class="fas fa-fw fa-flag"></i> ' . $words->getSilent('profile.report')),
                array('space', '', '', 'space'),
                array('profile', "members/$username", '<i class="fa fa-fw fa-user"></i> '  . $ww->MemberPage),
                array('comments', "members/$username/comments", '<i class="fa fa-fw fa-comments"></i> ' . $ww->ViewComments.' <span class="badge badge-primary u-rounded-full u-w-20 u-h-20 u-inline-flex u-items-center u-justify-center pull-right">'.$comments_count['all'].'</span>'),
                array('gallery', "gallery/show/user/$username/pictures", '<i class="fa fa-fw fa-image"></i> ' . $ww->Gallery . ' <span class="badge badge-primary u-rounded-full u-w-20 u-h-20 u-inline-flex u-items-center u-justify-center pull-right">' . $galleryItemsCount . '</span>'),
            );
            if ($accommodation != \App\Doctrine\AccommodationType::NO)
            {
                array_unshift($tt, array('sendrequest', "new/request/$username", '<i class="fa fa-fw fa-bed"></i> ' . $words->get('profile.request.hosting'), 'sendrequest'));
            }
            if ($linkMembersForumPosts) {
                $tt[] = array('forum', "forums/member/$username", '<i class="far fa-fw fa-comment"></i> ' . $viewForumPosts);
            }
        }
        if ($rights->HasRight('SafetyTeam') || $rights->HasRight('Admin'))
        {
            $tt[] = array('adminedit',"members/{$username}/adminedit", '<i class="fa fa-fw fa-bed invisible"></i> Admin: Edit Profile');
        }
        if ($rights->HasRight('Admin')) {
            $tt[] = array('mydata', 'members/'.$username.'/data', '<i class="fa fa-fw fa-database"></i> ' . $ww->PersonalData, 'personaldata');
        }
        if ($rights->HasRight('Rights')) {
            array_push($tt,array('adminrights','admin/rights/list/member/'.$username, '<i class="fa fa-fw fa-bed invisible"></i> ' .  $ww->AdminRights) ) ;
        }
        if ($rights->HasRight('Flags')) {
            array_push($tt,array('adminflags', 'admin/flags/list/member/'. $username, '<i class="fa fa-fw fa-flag"></i> ' .  $ww->AdminFlags) ) ;
        }
        if ($rights->HasRight('Logs')) {
            array_push($tt,array('admin','admin/logs?log[username='.$username.']','<i class="fa fa-fw fa-bed invisible"></i> ' .  $ww->AdminLogs) ) ;
        }
        return($tt) ;
    }

    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }

    protected function leftsidebar() {
    }

    protected function teaserContent()
    {
        $this->__call('teaserContent', array());
        //parent::submenu();
    }

    protected function submenu() {
        ?>
        <div class="col-md-3 offcanvas-collapse mb-2" id="sidebar">
            <div class="w-100 p-1 text-right d-md-none">
                <button type="button" class="btn btn-sm" aria-label="Close" data-toggle="offcanvas">
                    <i class="fa fa-lg fa-times white" aria-hidden="true"></i>
                </button>
            </div>
        <div class="list-group mb-2">
        <?php
        $member = $this->member;
        $words = $this->getWords();
        $picture_url = 'members/avatar/'.$member->Username;
        $globalsJs = json_encode([
            'baseUrl' => $this->getBaseUrl(),
            'texts' => [
                'profile.change.avatar' => $words->get('profile.change.avatar'),
                'profile.change.avatar.success' => $words->get('profile.change.avatar.success'),
                'profile.change.avatar.fail' => $words->get('profile.change.avatar.fail'),
                'profile.change.avatar.fail.file.to.big' => $words->get('profile.change.avatar.fail.file.to.big'),
                'profile.picture.title' => $words->get('profile.picture.title', $member->Username),
                'uploading' => $words->get('uploading'),
            ],
            'config' => [
                'isMyself' => $this->myself,
                'avatarUseLightbox' => $this->useLightbox,
                'avatarUrl' => $picture_url,
                'username' => $member->Username,
            ]
            ]);
        ?>

        <div id="react_mount" data-globals="<?=htmlspecialchars($globalsJs)?>" ></div>

        <div class="list-group mt-2">
            <?php

            $active_menu_item = $this->getSubmenuActiveItem();
            foreach ($this->getSubmenuItems() as $index => $item) {
                $name = $item[0];
                if ('space' === $name)
                {
                    // Brutal hack to separate the two blocks in the menu visually
                    ?>
                    </div>
                    <div class="list-group mt-2">
                    <?php
                    continue;
                }
                $url = $item[1];
                $label = $item[2];
                $attributes = '';
                if ($name === $active_menu_item) {
                    $attributes = ' active';
                }

                ?>
                  <a class="list-group-item<?=$attributes ?>" href="<?=$url ?>"><?=$label ?></a>
                  <?=$words->flushBuffer(); ?>
                <?php

            }

                ?>
        </div>
        </div>
        </div>
<?php
    }

    private function getBaseUrl()
    {
        if (isset($_SERVER['HTTPS'])) {
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }

    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'build/lightbox.css';
        return $stylesheets;
    }

    protected function getLateLoadScriptfiles()
    {
        $scripts = parent::getLateLoadScriptfiles();
        $scripts = array_merge($scripts, ['build/avatar']);
        return $scripts;
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
                $form .= '<div><form method="post" name="member-status" id="member-status" class="form-inline">' . $callbackTags;
                $form .= '<input type="hidden" name="member-id" value="' . $member->id . '">';
                $form .= '<select name="new-status" class="o-input select2-sm" data-minimum-results-for-search="-1">';
                $selected = false;
                foreach ($this->statuses as $status) {
                    $form .= '<option value="' . $status . '"';
                    if ($status === $member->Status) {
                        $form .= ' selected="selected"';
                        $selected = true;
                    }
                    $form .= '>' . $this->words->getSilent('MemberStatus' . $status) . '</option>';
                }
                if (!$selected) {
                    $form .= '<option value="' . $member->status . '" selected="selected">Old unused status. Be careful!</option>';
                }
                $form .= '</select>&nbsp;&nbsp;<input type="submit" value="Submit" class="btn btn-primary btn-sm">';
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
