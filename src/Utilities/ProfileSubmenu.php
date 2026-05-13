<?php

namespace App\Utilities;

use App\Doctrine\AccommodationType;
use App\Entity\Comment;
use App\Entity\Preference;
use App\Entity\Relation;
use App\Entity\ForumPost;
use App\Entity\GalleryImage;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\ProfileNote;
use App\Repository\CommentRepository;
use App\Repository\RelationRepository;
use App\Repository\ForumPostRepository;
use App\Repository\GalleryImageRepository;
use App\Repository\MessageRepository;
use App\Repository\ProfileNoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class ProfileSubmenu
{
    private array $submenuItems = [];
    private RouterInterface $routing;
    private EntityManagerInterface $entityManager;

    public function __construct(RouterInterface $routing, EntityManagerInterface $entityManager)
    {
        $this->routing = $routing;
        $this->entityManager = $entityManager;
    }

    public function getSubmenu(Member $member, Member $loggedInMember, array $parameters = []): array
    {
        $parameters = array_merge($parameters, $this->getMemberInfo($member, $loggedInMember));

        $this->addSubmenuItem('profile_picture', [
            'username' => $member->getUsername(),
            'is_owner' => $member === $loggedInMember,
        ]);
        $this->addSubmenuItem('separator_one', []);

        if ($member === $loggedInMember) {
            $this->addSubmenuItemsOwnProfile($member, $parameters);
        } else {
            $this->addSubmenuItemsProfile($member, $parameters);
        }
        $this->addGeneralItems($member, $loggedInMember, $parameters);
        $this->addVolunteerEntries($member, $loggedInMember);

        return [
            'active' => ($parameters['active'] ?? ''),
            'items' => $this->submenuItems,
        ];
    }

    /**
     * Gathers information about a member (like count of images uploaded to gallery,
     * posts made into the forum or in groups, comments received).
     *
     * It also adds info if items related to the logged in member exists (like exchanged messages,
     * existing comment or note).
     */
    private function getMemberInfo(Member $member, Member $loggedInMember): array
    {
        $memberInfo = [];
        $ownProfile = $member === $loggedInMember;
        $memberInfo['own_profile'] = $ownProfile;

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);

        /** @var Preference $profileVisitorsPreference */
        $profileVisitorsPreference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_PROFILE_VISITORS]);
        $showProfileVisitors = ('Yes' === $member->getMemberPreferenceValue($profileVisitorsPreference));
        $memberInfo['show_visitors'] = $showProfileVisitors && $ownProfile;

        /** @var Preference $publicForumPostsPreference */
        $publicForumPostsPreference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_FORUMS_POSTS]);
        $showForumPosts = ('Yes' === $member->getMemberPreferenceValue($publicForumPostsPreference));
        $roles = $loggedInMember->getRoles();
        $adminShowForumPosts = ($this->hasRole($roles, Member::ROLE_ADMIN_SAFETYTEAM)
            || $this->hasRole($roles, Member::ROLE_ADMIN_ADMIN)
            || $this->hasRole($roles, Member::ROLE_ADMIN_FORUMMODERATOR)
        );
        $memberInfo['show_forum_posts'] = $showForumPosts || $ownProfile || $adminShowForumPosts;

        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $memberInfo['comments_for_count'] = $commentRepository->getVisibleCommentsForMemberCount($member);

        /** @var ForumPostRepository $postsRepository */
        $postsRepository = $this->entityManager->getRepository(ForumPost::class);
        $memberInfo['posts_count'] = $postsRepository->getForumPostsByMemberCount($member);

        /** @var GalleryImageRepository $imageRepository */
        $imageRepository = $this->entityManager->getRepository(GalleryImage::class);
        $memberInfo['images_count'] = $imageRepository->getImagesByMemberCount($member);

        /** @var ProfileNoteRepository $noteRepository */
        $noteRepository = $this->entityManager->getRepository(ProfileNote::class);
        $notesCount = $noteRepository->getProfileNotesCount($loggedInMember);
        $memberInfo['notes_count'] = $notesCount;

        /** @var RelationRepository $relationRepository */
        $relationRepository = $this->entityManager->getRepository(Relation::class);
        $memberInfo['relations_count'] = $relationRepository->getRelationsCount($member);

        if (!$ownProfile) {
            $comment = $commentRepository->findOneBy(['fromMember' => $loggedInMember, 'toMember' => $member]);
            $memberInfo['comment'] = $comment;

            $note = $noteRepository->findOneBy(['owner' => $loggedInMember, 'member' => $member]);
            $memberInfo['note'] = null !== $note;

            /** @var RelationRepository $relationRepository */
            $relationRepository = $this->entityManager->getRepository(Relation::class);
            $relation = $relationRepository->findRelationBetween($loggedInMember, $member);
            $memberInfo['family_or_friend'] = null !== $relation;

            /** @var MessageRepository $messageRepository */
            $messageRepository = $this->entityManager->getRepository(Message::class);
            $messageBetweenMembersCount = $messageRepository->getMessagesBetweenCount($loggedInMember, $member);
            $memberInfo['all_messages_with'] = $messageBetweenMembersCount;
        }

        return $memberInfo;
    }

    private function addSubmenuItemsOwnProfile(Member $member, array $parameters)
    {
        $username = $member->getUsername();

        $this->addSubmenuItem('edit_profile', [
            'key' => 'editmyprofile',
            'icon' => 'edit',
            'url' => '/editmyprofile',
        ]);
        $this->addSubmenuItem('preferences', [
            'key' => 'profile.preferences.menu',
            'icon' => 'cogs',
            'url' => $this->routing->generate('preferences', ['username' => $username]),
        ]);
        $this->addSubmenuItem('mydata', [
            'key' => 'mydata',
            'icon' => 'database',
            'url' => $this->routing->generate('profile_personal_data', ['username' => $username]),
        ]);
        $this->addSubmenuItem('mynotes', [
            'key' => 'mynotes',
            'icon' => 'sticky-note',
            'count' => $parameters['notes_count'],
            'url' => $this->routing->generate('notes', ['username' => $username]),
        ]);
        if ($parameters['show_visitors']) {
            $this->addSubmenuItem('visitors', [
                'key' => 'myvisitors',
                'icon' => 'bed invisible',
                'url' => $this->routing->generate('profile_visitors', ['username' => $username]),
            ]);
        }
        $this->addSubmenuItem('separator_two', []);
        $this->addSubmenuItem('profile', [
            'key' => 'profile',
            'icon' => 'user',
            'url' => $this->routing->generate('members_profile', ['username' => $username]),
        ]);
    }

    private function addSubmenuItemsProfile(Member $member, array $parameters)
    {
        $username = $member->getUsername();

        if (isset($parameters['leg'])) {
            $this->addSubmenuItem('invitation', [
                'key' => 'profile.invite.guest',
                'icon' => 'bed',
                'url' => $this->routing->generate('hosting_invitation', ['leg' => $parameters['leg']]),
            ]);
        } elseif (AccommodationType::YES === $member->getAccommodation()) {
            $this->addSubmenuItem('request', [
                'key' => 'profile.request.hosting',
                'icon' => 'bed',
                'url' => $this->routing->generate('hosting_request', ['username' => $username]),
            ]);
        }

        $this->addSubmenuItem('message', [
            'key' => 'ContactMember',
            'icon' => 'envelope',
            'url' => $this->routing->generate('message_new', ['username' => $username]),
        ]);

        if (0 !== $parameters['all_messages_with']) {
            $this->addSubmenuItem('allmessages', [
                'key' => 'profile.all.messages.with',
                'icon' => 'mail-bulk',
                'count' => $parameters['all_messages_with'],
                'url' => $this->routing->generate('conversations_with', ['username' => $username]),
            ]);
        }

        /** @var Comment $comment */
        $comment = $parameters['comment'];
        if (null !== $comment) {
            if ($comment->getEditingAllowed()) {
                $this->addSubmenuItem('comment', [
                    'key' => 'EditComments',
                    'icon' => 'comment',
                    'url' => $this->routing->generate('edit_comment', ['username' => $username]),
                ]);
            }
        } else {
            $this->addSubmenuItem('comment', [
                'key' => 'AddComments',
                'icon' => 'comment',
                'url' => $this->routing->generate('add_comment', ['username' => $username]),
            ]);
        }

/*        if ($parameters['family_or_friend']) {

            $this->addSubmenuItem('family_or_friend', [
                'key' => 'profile.relation.edit',
                'icon' => 'handshake',
                'url' => $this->routing->generate('edit_relation', ['username' => $username]),
            ]);
        } else {
            $this->addSubmenuItem('family_or_friend', [
                'key' => 'profile.relation.add',
                'icon' => 'handshake',
                'url' => $this->routing->generate('add_relation', ['username' => $username]),
            ]);
        }
*/
        if ($parameters['note']) {
            $this->addSubmenuItem('edit_note', [
                'key' => 'NoteEditMyNotesOfMember',
                'icon' => 'pencil-alt',
                'url' => $this->routing->generate('edit_note', ['username' => $member->getUsername()]),
            ]);
        } else {
            $this->addSubmenuItem('add_note', [
                'key' => 'NoteAddToMyNotes',
                'icon' => 'pencil-alt',
                'url' => $this->routing->generate('add_note', ['username' => $member->getUsername()]),
            ]);
        }

        $feedbackUrl = "/feedback?IdCategory=2&username=" . $username;
        if (isset($parameters['message'])) {
            $feedbackUrl .= "&messageId=" . $parameters['message'];
        }
        $this->addSubmenuItem('report', [
            'key' => 'profile.report',
            'icon' => 'flag',
            'url' => $feedbackUrl,
        ]);
    }

    private function addGeneralItems(Member $member, Member $loggedInMember, array $parameters)
    {
        $username = $member->getUsername();
        $this->addSubmenuItem('separator_two', []);
        $this->addSubmenuItem('profile', [
            'key' => 'MemberPage',
            'icon' => 'user',
            'url' => $this->routing->generate('members_profile', ['username' => $username]),
        ]);
        $this->addSubmenuItem('comments', [
            'key' => 'ViewComments',
            'icon' => 'comments',
            'count' => ($parameters['comments_for_count'] ?? 0),
            'url' => $this->routing->generate('profile_comments', ['username' => $username]),
        ]);

        $this->addSubmenuItem('relations', [
            'key' => 'profile.relations',
            'icon' => 'users',
            'count' => $parameters['relations_count'],
            'url' => $this->routing->generate('relations', ['username' => $username]),
        ]);

        if ($member === $loggedInMember) {
            $this->addSubmenuItem('gallery', [
                'key' => 'Gallery',
                'icon' => 'image',
                'count' => $parameters['images_count'] ?? 0,
                'url' => '/gallery/manage',
            ]);
        } else {
            $this->addSubmenuItem('gallery', [
                'key' => 'Gallery',
                'icon' => 'image',
                'count' => $parameters['images_count'] ?? 0,
                'url' => '/gallery/show/user/' . $username . '/pictures',
            ]);
        }
        if ($parameters['show_forum_posts']) {
            $this->addSubmenuItem('forum_posts', [
                'key' => 'ViewForumPosts',
                'icon' => 'comment',
                'count' => $parameters['posts_count'] ?? 0,
                'url' => $this->routing->generate('profile_forum_posts', ['username' => $username]),
            ]);
        }
    }

    private function addVolunteerEntries(Member $member, Member $loggedInMember)
    {
        $roles = $loggedInMember->getRoles();
        $username = $member->getUsername();

        $this->addSubmenuItem('separator_three', []);
        if (
            $this->hasRole($roles, Member::ROLE_ADMIN_SAFETYTEAM)
            || $this->hasRole($roles, Member::ROLE_ADMIN_ADMIN)
        ) {
            $this->addSubmenuItem('adminedit', [
                'key' => 'Admin: Edit Profile',
                'icon' => 'bed invisible',
                'url' => 'members/' . $username . '/adminedit',
            ]);
        }
        if ($this->hasRole($roles, Member::ROLE_ADMIN_ADMIN)) {
            $this->addSubmenuItem('personaldate', [
                'key' => 'PersonalData',
                'icon' => 'database',
                'url' => 'members/' . $username . '/data',
            ]);
        }
        if ($this->hasRole($roles, Member::ROLE_ADMIN_RIGHTS)) {
            $this->addSubmenuItem('adminrights', [
                'key' => 'AdminRights',
                'icon' => 'bed invisible',
                'url' => 'admin/rights/list/member/' . $username,
            ]);
        }
        if ($this->hasRole($roles, Member::ROLE_ADMIN_FLAGS)) {
            $this->addSubmenuItem('adminflags', [
                'key' => 'AdminFlags',
                'icon' => 'flag',
                'url' => 'admin/flags/list/member/' . $username,
            ]);
        }
        if ($this->hasRole($roles, Member::ROLE_ADMIN_LOGS)) {
            $this->addSubmenuItem('adminlogs', [
                'key' => 'AdminLogs',
                'icon' => 'bed invisible',
                'url' => 'admin/logs?log[username=' . $username . ']',
            ]);
        }
    }

    private function hasRole(array $roles, string $role): bool
    {
        return in_array($role, $roles);
    }

    private function addSubmenuItem(string $key, array $value)
    {
        $this->submenuItems[$key] = $value;
    }
}
