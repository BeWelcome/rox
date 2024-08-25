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

use App\Doctrine\AccommodationType;
use App\Doctrine\MemberStatusType;

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
    protected $message = 0;

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
        $member = $this->member;
        $username = $member->Username;
        $accommodation = $member->Accomodation;
        $lang = $this->model->get_profile_language();
        $profile_language_code = $lang->ShortCode;
        $words = $this->getWords();
        $rights = MOD_Right::get();

        $ww = $this->ww;
        $conversations_with_count = 0;
        $comments_count = $member->count_comments();
        $relations_count = $member->count_relations();
        $logged_user = $this->model->getLoggedInMember();
        if ($logged_user)
        {
            $TCom = $member->get_comments_commenter($logged_user->id);
            $note = $logged_user->getNote($member);
            $relation = $logged_user->getRelation($member);
            $conversations_with_count = $member->count_conversations_with($logged_user);
        }

        $galleryItemsCount = $member->getGalleryItemsCount();
        $viewForumPostsTranslation = $words->get("ViewForumPosts", "");
        $viewForumPosts = $viewForumPostsTranslation . $this->getBadge($member->forums_posts_count());

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
        $tt = [];
        $mynotes_count = $member->count_mynotes();
        if ($this->myself) {
            $tt=array(
                array('editmyprofile', 'editmyprofile/' . $profile_language_code, '<i class="fa fa-fw fa-edit"></i> ' . $ww->EditMyProfile, 'editmyprofile'),
                array('profile.preferences.menu', "/members/$username/preferences", '<i class="fa fa-fw fa-cogs"></i> ' . $ww->MyPreferences, 'mypreferences'),
                array('mydata', 'mydata', '<i class="fa fa-fw fa-database"></i> ' . $ww->MyData, 'mydata'),
                array('mynotes', "/members/$username/notes", '<i class="fa fa-fw fa-sticky-note"></i> ' . $words->get('MyNotes') . $this->getBadge($mynotes_count), 'mynotes'),
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
                $tt[] = array('myvisitors', "members/" . $username . "/visitors", '<i class="fa fa-fw fa-comments invisible"></i> ' . $ww->MyVisitors, 'myvisitors');
            }
            $tt[] = array('separator-1', '', '', 'space');

            $tt[] = array('profile', "members/$username", '<i class="fa fa-fw fa-user"></i> ' . $ww->MemberPage);
            $tt[] = array('comments', "members/$username/comments", '<i class="fa fa-fw fa-comments"></i> ' . $ww->ViewComments.' '. $this->getBadge($comments_count['all']));
            $tt[] = array('relations', "members/$username/relations", '<i class="fa fa-fw fa-users"></i> ' . $words->get('profile.relations').' ' . $this->getBadge($relations_count));
            if ($this->myself) {
                $tt[] = array('gallery', "gallery/manage", '<i class="fa fa-fw fa-image"></i> ' . $ww->Gallery . ' ' . $this->getBadge($galleryItemsCount));
            } else {
                $tt[] = array('gallery', "gallery/show/user/$username/pictures", '<i class="fa fa-fw fa-image"></i> ' . $ww->Gallery . ' ' . $this->getBadge($galleryItemsCount));
            }
            $tt[] = array('forum', "members/$username/posts", '<i class="far fa-fw fa-comment"></i> ' . $viewForumPosts);
        } else {
            if (isset($note)) {
                $mynotewordsname=$words->get('NoteEditMyNotesOfMember') ;
                $mynotelinkname= "members/$username/note/edit" ;
            }
            else {
                $mynotewordsname=$words->get('NoteAddToMyNotes') ;
                $mynotelinkname= "members/$username/note/add" ;
            }
            if (MemberStatusType::PASSED_AWAY !== $member->Status) {
                $tt= [
                    array('messagesadd', "new/message/$username", '<i class="fa fa-fw fa-envelope"></i> ' . $ww->ContactMember, 'messagesadd'),
                ];
            }
            if (0 < $conversations_with_count) {
                $tt = array_merge($tt, [['allmessages', "conversations/with/$username", '<i class="fas fa-fw fa-mail-bulk"></i> ' . $words->getSilent('profile.all.messages.with') .
                    $this->getBadge($conversations_with_count), 'allmessages']]);
            }
            $feedbackUrl = "/feedback?IdCategory=2&username=" . $username;
            if ($this->message !== 0) {
                $feedbackUrl .= "&messageId=" . $this->message;
            }
            if (isset($TCom[0])) {
                if ($TCom[0]->AllowEdit) {
                    $tt = array_merge($tt, [
                        [ 'commmentsadd', "members/$username/comment/edit", '<i class="fa fa-fw fa-comment"></i> ' . $ww->EditComments, 'commentsadd']
                    ]);
                }
            } else {
                $tt = array_merge($tt, [['commmentsadd', "members/$username/comment/add", '<i class="fa fa-fw fa-comment"></i> ' . $ww->AddComments, 'commentsadd']]);
            }
            $tt = array_merge($tt, [
/*                (null === $relation)
                    ? array('relationsadd', "members/$username/relation/add", '<i class="fa fa-fw fa-handshake"></i> ' . $words->get('profile.relation.add'), 'relationsadd')
                    : array('relationsadd', "members/$username/relation/edit", '<i class="fa fa-fw fa-handshake"></i> ' . $words->get('profile.relation.edit'), 'relationsadd'),                array('notes', $mynotelinkname, '<i class="fa fa-fw fa-pencil-alt"></i> ' . $mynotewordsname, 'mynotes'),
                array('report', $feedbackUrl, '<i class="fas fa-fw fa-flag"></i> ' . $words->getSilent('profile.report')),
                array('separator-1', '', '', 'space'),
                array('profile', "members/$username", '<i class="fa fa-fw fa-user"></i> '  . $ww->MemberPage),
                array('comments', "members/$username/comments", '<i class="fa fa-fw fa-comments"></i> ' . $ww->ViewComments.' ' . $this->getBadge($comments_count['all'])),
                array('relations', "members/$username/relations", '<i class="fa fa-fw fa-users"></i> ' . $words->get('profile.relations').' ' . $this->getBadge($relations_count)),
                array('gallery', "gallery/show/user/$username/pictures", '<i class="fa fa-fw fa-image"></i> ' . $ww->Gallery . ' ' . $this->getBadge($galleryItemsCount)),
            ]);
            if (MemberStatusType::PASSED_AWAY !== $member->Status) {
                if ($this->leg) {
                    array_unshift($tt, array('sendinvite', "new/invitation/$this->leg", '<i class="fa fa-fw fa-bed"></i> ' . $words->get('profile.invite.guest'), 'sendinvite'));
                } else if ($accommodation != AccommodationType::NO)
                {
                    array_unshift($tt, array('sendrequest', "new/request/$username", '<i class="fa fa-fw fa-bed"></i> ' . $words->get('profile.request.hosting'), 'sendrequest'));
                }
            }
            if ($linkMembersForumPosts) {
                $tt[] = array('forum', "members/$username/posts", '<i class="far fa-fw fa-comment"></i> ' . $viewForumPosts);
            }
        }
        $tt[] = array('separator-2', '', '', 'space');
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
        <div class="list-group u-rounded-8 mb-2">
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
                    'profile.change.avatar.fail.file.too.big' => $words->get('profile.change.avatar.fail.file.too.big'),
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
            <div id="react_mount" data-globals="<?=htmlspecialchars($globalsJs)?>"></div>
        </div>
        <div class="list-group u-rounded-8 mt-2">
            <?php

            $active_menu_item = $this->getSubmenuActiveItem();
            foreach ($this->getSubmenuItems() as $index => $item) {
                $name = $item[0];
                if (false !== strpos($name, 'separator'))
                {
                    // Brutal hack to separate the two blocks in the menu visually
                    ?>
                    </div>
                    <div class="list-group u-rounded-8 mt-2">
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
        return $protocol . "://" . $_SERVER['HTTP_HOST'] . '/';
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

    public function lastLoginDate($lastLoginDate)
    {
        $lastLogin = '';
        $logged_member = $this->model->getLoggedInMember();
        if ($logged_member
            && $logged_member->hasOldRight(
                array('SafetyTeam' => '')
            )
        ) {
            $lastLogin = ' ('.$lastLoginDate.')';
        }

        return $lastLogin;
    }

    private function getBadge($count, $active = false): string
    {
        $badge = '<span class="badge ';
        if ($active) {
            $badge .= 'badge-white text-primary';
        } else {
            $badge .= 'badge-primary text-white';
        }
        $badge .= ' text-white u-rounded-8 u-min-w-20 u-px-8 u-h-20 u-inline-flex u-items-center u-justify-center pull-right">';
        $badge .= $count . '<span>';

        return $badge;
    }
}
