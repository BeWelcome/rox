<?php

namespace App\Model;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\GroupType;
use App\Doctrine\MemberStatusType;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Language;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Notification;
use App\Entity\Privilege;
use App\Entity\PrivilegeScope;
use App\Entity\Role;
use App\Form\CustomDataClass\GroupSettingsRequest;
use App\Service\Mailer;
use App\Utilities\BewelcomeAddressTrait;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupModel
{
    use BewelcomeAddressTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly Mailer $mailer,
    ) {
    }

    public function inviteMemberToGroup(Group $group, Member $member, Member $admin): bool
    {
        $em = $this->entityManager;

        $membership = new GroupMembership();
        $membership->setGroup($group);
        $membership->setMember($member);
        // The invitee's comment until they write their own, in their language
        $membership->setComment($this->translator->trans(
            'group.got.invited.by',
            ['admin' => $admin->getUsername()],
            null,
            $member->getLocale()
        ));
        $membership->setStatus(GroupMembershipStatusType::INVITED_INTO_GROUP);
        $em->persist($membership);
        $em->flush();

        // Send email to invitee
        $params = [
            'subject' => [
                'translationId' => 'group.invitation',
                'parameters' => [
                    'username' => $member->getUsername(),
                    'group' => $group->getName(),
                ],
            ],            'receiver' => $member,
            'sender' => $admin,
            'group' => [
                'name' => $group->getName(),
                'id' => $group->getId(),
            ],
        ];
        $this->mailer->sendGroupNotificationEmail($admin, $member, 'group/invitation', $params);

        $url = $this->urlGenerator->generate('group_start', ['group_id' => $group->getId()]);
        $note = new Notification();
        $note->setMember($member);
        $note->setRelMember($admin);
        $note->setType('message');
        $note->setLink($url);
        $note->setWordCode('');
        $note->setTranslationparams(serialize(['GroupsInvitedNote', $group->getName()]));
        $em->persist($note);
        $em->flush();

        return true;
    }

    public function acceptInviteToGroup(Group $group, Member $member): bool
    {
        $success = false;
        try {
            $membership = $this->getMembership($group, $member);

            if ($membership) {
                $membership->setStatus(GroupMembershipStatusType::CURRENT_MEMBER);
                $this->entityManager->persist($membership);
                $this->entityManager->flush();
                $success = true;
            }
        } catch (Exception) {
        }

        return $success;
    }

    public function declineInviteToGroup(Group $group, Member $member): bool
    {
        try {
            $success = false;
            $membership = $this->getMembership($group, $member);

            if ($membership) {
                $this->entityManager->remove($membership);
                $this->entityManager->flush();
                $success = true;
            }
        } catch (Exception) {
            $success = false;
        }

        return $success;
    }

    public function withdrawInviteMemberToGroup(Group $group, Member $member): bool
    {
        $success = false;
        try {
            $membership = $this->getMembership($group, $member);

            if ($membership) {
                $this->entityManager->remove($membership);
                $this->entityManager->flush();
                $success = true;
            }
        } catch (Exception) {
        }

        return $success;
    }

    public function join(Group $group, Member $member, $data, $locale): bool
    {
        try {
            $reason = $data['reason'] ?? '';
            $notifications = $data['notifications'];
            $em = $this->entityManager;

            $membership = new GroupMembership();
            $membership->setGroup($group);
            $membership->setMember($member);
            $membership->setComment('' === $reason ? null : $reason);
            $membership->setNotificationsEnabled('yes' === $notifications);
            if (GroupType::NEED_ACCEPTANCE === $group->getType()) {
                $membership->setStatus(GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP);

                $params = [
                    'subject' => [
                        'translationId' => 'group.wantin',
                        'parameters' => [
                            'username' => $member->getUsername(),
                            'group' => $group->getName(),
                        ],
                    ],
                    'group' => $group,
                    'reason' => $reason,
                ];

                /** @var Member[] $admins */
                $admins = $group->getAdministrators();
                foreach ($admins as $admin) {
                    $this->mailer->sendGroupNotificationEmail($member, $admin, 'group/wantin', $params);
                }
            } else {
                $membership->setStatus(GroupMembershipStatusType::CURRENT_MEMBER);
            }

            $em->persist($membership);
            $em->flush();
            $success = true;
        } catch (Exception) {
            $success = false;
        }

        return $success;
    }

    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     * Because of the mix between old code and new code this method is way too long.
     */
    public function new($data, $locale, Member $member, $groupPicture): Group
    {
        $em = $this->entityManager;

        $languageRepository = $em->getRepository(Language::class);
        /** @var Language $language */
        $language = $languageRepository->findOneBy(['shortCode' => $locale]);

        // We create the group entity and add the first member
        $group = new Group();
        $group
            ->setName($data->name)
            ->setType($data->type)
            ->setMembersOnly('No' === $data->visiblePosts)
            ->setPicture($groupPicture ?? '')
        ;
        $em->persist($group);
        $em->flush();

        // The description is the group's own text, in the language the member writes in
        $group->setDescription($language->getShortCode(), $data->description, $member);

        $groupMembership = new GroupMembership();
        $groupMembership
            ->setStatus(GroupMembershipStatusType::CURRENT_MEMBER)
            ->setAcceptMassMail(true)
            ->setComment('Group creator')
            ->setGroup($group)
            ->setMember($member);

        $member->addGroupMembership($groupMembership);
        $group->addGroupMembership($groupMembership);

        $em->persist($group);
        $em->flush();

        $this->addAdministrator($group, $member);

        return $group;
    }

    /**
     * Deletes the group with its forum: threads, posts, their translations, subscriptions and queued notifications.
     * The BeWelcome group (the agora) can't be deleted.
     */
    public function delete(Group $group): void
    {
        if ($group->isBeWelcome()) {
            throw new LogicException('The BeWelcome group can\'t be deleted.');
        }

        $groupId = $group->getId();
        $this->entityManager->getConnection()->transactional(
            static function (Connection $connection) use ($groupId): void {
                self::deleteForum($connection, $groupId);
                $connection->executeStatement(
                    <<<'SQL'
                        DELETE scopes
                        FROM privilegescopes scopes
                        INNER JOIN roles role ON role.id = scopes.IdRole
                        WHERE role.name = ? AND scopes.IdType = ?
                        SQL,
                    [Role::GROUP_OWNER, (string) $groupId],
                );
                // group_membership and group_translation go with the group (ON DELETE CASCADE)
                $connection->executeStatement('DELETE FROM `group` WHERE id = ?', [$groupId]);
            },
        );
    }

    public function acceptJoin(Group $group, Member $member, Member $admin)
    {
        if (!$this->checkMembershipStatus($group, $member, GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP)) {
            return false;
        }

        $this->updateMembership($group, $member, GroupMembershipStatusType::CURRENT_MEMBER);
        $this->mailer->sendGroupNotificationEmail($admin, $member, 'group/join.approved', [
            'subject' => [
                'translationId' => 'group.approved.join',
                'parameters' => [
                    'username' => $member->getUsername(),
                    'group' => $group->getName(),
                ],
            ],
            'group' => $group,
            'member' => $member,
            'admin' => $admin,
        ]);

        return true;
    }

    public function declineJoin(Group $group, Member $member, Member $admin)
    {
        if (!$this->checkMembershipStatus($group, $member, GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP)) {
            return false;
        }
        $this->updateMembership($group, $member, GroupMembershipStatusType::KICKED_FROM_GROUP);
        $this->mailer->sendGroupNotificationEmail($admin, $member, 'group/join.declined', [
            'subject' => [
                'translationId' => 'group.declined.join',
                'parameters' => [
                    'username' => $member->getUsername(),
                    'group' => $group->getName(),
                ],
            ],
            'group' => $group,
            'member' => $member,
            'admin' => $admin,
        ]);

        return true;
    }

    public function sendAdminNotificationDeclined(Group $group, Member $member): void
    {
        $admins = $group->getAdministrators();
        foreach ($admins as $admin) {
            $this->mailer->sendGroupEmail($admin, 'group/declined.invite', [
                'subject' => [
                    'translationId' => 'group.invitation.declined',
                    'parameters' => [
                        'group' => $group->getName(),
                    ],
                ],
                'group' => $group,
                'invitee' => $member,
                'admin' => $admin,
            ]);
        }
    }

    public function sendAdminNotificationAccepted(Group $group, Member $member): void
    {
        $admins = $group->getAdministrators();
        foreach ($admins as $admin) {
            $this->mailer->sendGroupEmail($admin, 'group/accepted.invite', [
                'subject' => [
                    'translationId' => 'group.invitation.accepted',
                    'parameters' => [
                        'group' => $group->getName(),
                    ],
                ],
                'group' => $group,
                'invitee' => $member,
                'admin' => $admin,
            ]);
        }
    }

    /**
     * This function returns the members with the right 'Group' who are administrating all groups.
     *
     * @return array Member
     */
    public function getGroupAdministrators()
    {
        // Unfortunately we need to replicate old code here
        $admins = [];
        $entityManager = $this->entityManager;
        $roleRepo = $entityManager->getRepository(Role::class);

        $role = $roleRepo->findBy(['name' => 'GroupsAdmin']);
        $privilegeScopesRepo = $entityManager->getRepository(PrivilegeScope::class);
        $privilegeScopes = $privilegeScopesRepo->findBy(['role' => $role, 'type' => '*']);

        foreach ($privilegeScopes as $privilegeScope) {
            /** @var Member $admin */
            $admin = $privilegeScope->getMember();
            if (str_contains(MemberStatusType::ACTIVE_WITH_MESSAGES, $admin->getStatus())) {
                $admins[] = $admin;
            }
        }

        return $admins;
    }

    /**
     * Makes a current member of the group one of its admins (a GroupOwner privilege scope for the group).
     */
    public function addAdministrator(Group $group, Member $member): bool
    {
        if (!$this->isCurrentMember($group, $member)) {
            return false;
        }
        if (null !== $this->findAdministratorScope($group, $member)) {
            return true;
        }

        $privilegeScope = new PrivilegeScope()
            ->setMember($member)
            ->setRole($this->getGroupOwnerRole())
            ->setPrivilege($this->getGroupPrivilege())
            ->setType((string) $group->getId());
        $this->entityManager->persist($privilegeScope);
        $this->entityManager->flush();

        return true;
    }

    /**
     * An admin steps down; the last admin can't.
     */
    public function removeAdministrator(Group $group, Member $member): bool
    {
        $scope = $this->findAdministratorScope($group, $member);
        if (null === $scope || \count($group->getAdministrators()) < 2) {
            return false;
        }

        $this->entityManager->remove($scope);
        $this->entityManager->flush();

        return true;
    }

    public function isAdministrator(Group $group, Member $member): bool
    {
        return null !== $this->findAdministratorScope($group, $member);
    }

    /**
     * The member leaves the group. Admins step down first.
     */
    public function leave(Group $group, Member $member): bool
    {
        $membership = $this->getMembership($group, $member);
        if (null === $membership || $this->isAdministrator($group, $member)) {
            return false;
        }

        $this->entityManager->remove($membership);
        $this->entityManager->flush();

        return true;
    }

    /**
     * An admin takes a member out of the group (kick) or bans them, so they can't join again. Admins of the group and
     * BeWelcome's admins and forum moderators can't be taken out.
     */
    public function removeMember(Group $group, Member $member, bool $ban): bool
    {
        $membership = $this->getMembership($group, $member);
        if (null === $membership || $this->isAdministrator($group, $member)) {
            return false;
        }
        $roles = $member->getRoles();
        if (\in_array(Member::ROLE_ADMIN_ADMIN, $roles, true) || \in_array(Member::ROLE_ADMIN_FORUMMODERATOR, $roles, true)) {
            return false;
        }

        if ($ban) {
            $membership->setStatus(GroupMembershipStatusType::KICKED_FROM_GROUP);
        } else {
            $this->entityManager->remove($membership);
        }
        $this->entityManager->flush();

        return true;
    }

    public function updateMembershipSettings(
        GroupMembership $membership,
        ?string $comment,
        bool $acceptMassMail,
        bool $notificationsEnabled,
    ): void {
        $membership
            ->setComment('' === trim((string) $comment) ? null : $comment)
            ->setAcceptMassMail($acceptMassMail)
            ->setNotificationsEnabled($notificationsEnabled);
        $this->entityManager->flush();
    }

    /**
     * Saves the settings an admin made: the description in their language, who may join, who sees the posts, the
     * categories, the location and a new picture (already stored, by its file name).
     */
    public function updateSettings(
        Group $group,
        GroupSettingsRequest $settings,
        string $locale,
        Member $admin,
        ?string $picture,
    ): void {
        $group
            ->setDescription($locale, $settings->description, $admin)
            ->setType($settings->type)
            ->setMembersOnly('No' === $settings->visiblePosts)
            ->setLocation(null === $settings->location ? null : $this->entityManager->find(Location::class, $settings->location));
        if (null !== $picture) {
            $group->setPicture($picture);
        }
        foreach ($group->getCategories() as $category) {
            if (!\in_array($category, $settings->categories, true)) {
                $group->removeCategory($category);
            }
        }
        foreach ($settings->categories as $category) {
            $group->addCategory($category);
        }
        $this->entityManager->flush();
    }

    /**
     * Deletes the threads of a group's forum and everything that hangs on them. Threads and posts point at each
     * other (first and last post), so the foreign key checks are off while they go.
     */
    private static function deleteForum(Connection $connection, int $groupId): void
    {
        $threadIds = $connection->fetchFirstColumn('SELECT id FROM forum_thread WHERE IdGroup = ?', [$groupId]);
        if ([] === $threadIds) {
            return;
        }

        $types = [ArrayParameterType::INTEGER];
        $connection->executeStatement(
            'DELETE n FROM posts_notificationqueue n JOIN forum_post p ON p.id = n.IdPost WHERE p.threadid IN (?)',
            [$threadIds],
            $types,
        );
        $connection->executeStatement('DELETE FROM members_threads_subscribed WHERE IdThread IN (?)', [$threadIds], $types);

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        try {
            // the translations go with the posts and threads (ON DELETE CASCADE), but not while the checks are off
            $connection->executeStatement(
                'DELETE t FROM forum_post_translation t JOIN forum_post p ON p.id = t.object_id WHERE p.threadid IN (?)',
                [$threadIds],
                $types,
            );
            $connection->executeStatement('DELETE FROM forum_post WHERE threadid IN (?)', [$threadIds], $types);
            $connection->executeStatement('DELETE FROM forum_thread_translation WHERE object_id IN (?)', [$threadIds], $types);
            $connection->executeStatement('DELETE FROM forum_thread WHERE id IN (?)', [$threadIds], $types);
        } finally {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    private function isCurrentMember(Group $group, Member $member): bool
    {
        $membership = $this->getMembership($group, $member);

        return null !== $membership && GroupMembershipStatusType::CURRENT_MEMBER === $membership->getStatus();
    }

    private function findAdministratorScope(Group $group, Member $member): ?PrivilegeScope
    {
        return $this->entityManager->getRepository(PrivilegeScope::class)->findOneBy([
            'member' => $member,
            'role' => $this->getGroupOwnerRole(),
            'privilege' => $this->getGroupPrivilege(),
            'type' => (string) $group->getId(),
        ]);
    }

    private function getGroupOwnerRole(): Role
    {
        /** @var Role $role */
        $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => Role::GROUP_OWNER]);

        return $role;
    }

    private function getGroupPrivilege(): Privilege
    {
        /** @var Privilege $privilege */
        $privilege = $this->entityManager->getRepository(Privilege::class)->findOneBy(['controller' => Privilege::GROUP_CONTROLLER]);

        return $privilege;
    }

    private function getMembership($group, $member): ?GroupMembership
    {
        $membershipRepository = $this->entityManager->getRepository(GroupMembership::class);

        /** @var GroupMembership $membership */
        $membership = $membershipRepository->findOneBy([
            'group' => $group,
            'member' => $member,
        ]);

        return $membership;
    }

    private function updateMembership(Group $group, Member $member, string $status): void
    {
        $membershipRepository = $this->entityManager->getRepository(GroupMembership::class);
        $membership = $membershipRepository->findOneBy(['group' => $group, 'member' => $member]);
        $membership->setStatus($status);

        $this->entityManager->persist($membership);
        $this->entityManager->flush();
    }

    private function checkMembershipStatus(Group $group, Member $member, string $status): bool
    {
        $membershipRepository = $this->entityManager->getRepository(GroupMembership::class);
        $membership = $membershipRepository->findOneBy(['group' => $group, 'member' => $member]);

        return $status === $membership->getStatus();
    }
}
