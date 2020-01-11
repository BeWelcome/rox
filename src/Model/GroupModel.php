<?php

namespace App\Model;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\GroupTypeType;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\MemberTranslation;
use App\Entity\Notification;
use App\Entity\Privilege;
use App\Entity\PrivilegeScope;
use App\Entity\Role;
use App\Utilities\MailerTrait;
use App\Utilities\ManagerTrait;
use App\Utilities\MessageTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupModel
{
    use ManagerTrait;
    use MailerTrait;
    use MessageTrait;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Group  $group
     * @param Member $member
     * @param Member $admin
     *
     * @return bool
     */
    public function inviteMemberToGroup(Group $group, Member $member, Member $admin)
    {
        $em = $this->getManager();
        $membership = new GroupMembership();
        $membership->setGroup($group);
        $membership->setMember($member);
        $membership->setStatus(GroupMembershipStatusType::INVITED_INTO_GROUP);
        try {
            $em->persist($membership);
            $em->flush();

            // Send email to invitee
            $url = $this->urlGenerator->generate('group_start', ['group_id' => $group->getId()]);
            $acceptUrl = $this->urlGenerator->generate('accept_invite_to_group', [
                'groupId' => $group->getId(),
                'memberId' => $member->getId(),
            ], UrlGenerator::ABSOLUTE_URL);

            $declineUrl = $this->urlGenerator->generate('decline_invite_to_group', [
                'groupId' => $group->getId(),
                'memberId' => $member->getId(),
            ], UrlGenerator::ABSOLUTE_URL);

            $acceptTag = '<a href="' . $acceptUrl . '">';
            $declineTag = '<a href="' . $declineUrl . '">';

            $params = [
                'subject' => 'group.invitation',
                'receiver' => $member,
                'sender' => $admin,
                'group' => $group,
                'accept_start' => $acceptTag,
                'accept_end' => '</a>',
                'decline_start' => $declineTag,
                'decline_end' => '</a>',
            ];
            $this->createTemplateMessage($admin, $member, 'group', 'invitation', $params);

            $this->sendTemplateEmail($admin, $member, 'group.invitation', $params);

            $note = new Notification();
            $note->setMember($member);
            $note->setRelMember($admin);
            $note->setType('message');
            $note->setLink($url);
            $note->setWordCode('');
            $note->setTranslationparams(serialize(['GroupsInvitedNote', $group->getName()]));
            $em->persist($note);
            $em->flush();

            $success = true;
        } catch (Exception $e) {
            $success = false;
        }

        return $success;
    }

    /**
     * @param Group  $group
     * @param Member $member
     *
     * @return bool
     */
    public function acceptInviteToGroup(Group $group, Member $member)
    {
        $success = false;
        try {
            $membership = $this->getMembership($group, $member);

            if ($membership) {
                $membership->setStatus(GroupMembershipStatusType::CURRENT_MEMBER);
                $this->getManager()->persist($membership);
                $this->getManager()->flush();
                $success = true;
            }
        } catch (Exception $e) {
        }

        return $success;
    }

    /**
     * @param Group  $group
     * @param Member $member
     *
     * @return bool
     */
    public function declineInviteToGroup(Group $group, Member $member)
    {
        $success = false;
        try {
            $membership = $this->getMembership($group, $member);

            if ($membership) {
                $this->getManager()->remove($membership);
                $this->getManager()->flush();
                $success = true;
            }
        } catch (Exception $e) {
        }

        return $success;
    }

    /**
     * @param Group  $group
     * @param Member $member
     *
     * @return bool
     */
    public function withdrawInviteMemberToGroup(Group $group, Member $member)
    {
        $success = false;

        try {
            $membership = $this->getMembership($group, $member);

            if ($membership) {
                $this->getManager()->remove($membership);
                $this->getManager()->flush();
                $success = true;
            }
        } catch (Exception $e) {
        }

        return $success;
    }

    public function join(Group $group, Member $member, $data, $locale)
    {
        $success = false;

        try {
            $reason = $data['reason'] ?? '';
            $notifications = $data['notifications'];
            $em = $this->getManager();
            $languageRepository = $em->getRepository(Language::class);
            /** @var Language $language */
            $language = $languageRepository->findOneBy(['shortcode' => $locale]);

            $comment = new MemberTranslation();
            $comment->setLanguage($language);
            $comment->setSentence($reason);
            $comment->setOwner($member);
            $comment->setIdtranslator($member->getId());

            $em->persist($comment);
            $em->flush();

            $membership = new GroupMembership();
            $membership->setGroup($group);
            $membership->setMember($member);
            $membership->addComment($comment);
            $membership->setNotificationsenabled('yes' === $notifications ? true : false);
            if (GroupTypeType::NEED_ACCEPTANCE === $group->getType()) {
                $membership->setStatus(GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP);

                $acceptUrl = $this->urlGenerator->generate('group_accept_join', [
                    'groupId' => $group->getId(),
                    'memberId' => $member->getId(),
                ], UrlGenerator::ABSOLUTE_URL);

                $declineUrl = $this->urlGenerator->generate('group_decline_join', [
                    'groupId' => $group->getId(),
                    'memberId' => $member->getId(),
                ], UrlGenerator::ABSOLUTE_URL);

                $acceptTag = '<a href="' . $acceptUrl . '">';
                $declineTag = '<a href="' . $declineUrl . '">';

                /** @var Member[] $admins */
                $params = [
                    'subject' => 'group.wantin',
                    'accept_start' => $acceptTag,
                    'decline_start' => $declineTag,
                    'accept_end' => '</a>',
                    'decline_end' => '</a>',
                    'group' => $group,
                    'template' => 'wantin',
                ];
                $admins = $group->getAdmins();
                foreach ($admins as $admin) {
                    $this->createTemplateMessage($member, $admin, 'group', 'wantin', $params);
                    $this->sendTemplateEmail($member, $admin, 'group', $params);
                }
            } else {
                $membership->setStatus(GroupMembershipStatusType::CURRENT_MEMBER);
            }

            $em->persist($membership);
            $em->flush();
            $success = true;
        } catch (OptimisticLockException $e) {
        } catch (ORMException $e) {
        }

        return $success;
    }

    /**
     * @param $data
     * @param $locale
     * @param Member $member
     * @param $groupPicture
     *
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return Group
     */
    public function new($data, $locale, Member $member, $groupPicture)
    {
        $em = $this->getManager();

        // \todo: This is convoluted due to having to support the old structure! When recoding groups this should be simpler
        // We need the current locale for the MemberTranslation entity
        $languageRepository = $em->getRepository(Language::class);
        /** @var Language $language */
        $language = $languageRepository->findOneBy(['shortcode' => $locale]);
        /** @var Language $english */
        $english = $languageRepository->findOneBy(['shortcode' => 'en']);

        // We create the group entity and add the first member
        $group = new Group();
        $group
            ->setName($data->name)
            ->setType($data->type)
            ->setVisiblePosts($data->membersOnly)
            ->setPicture($groupPicture)
        ;
        $em->persist($group);
        $em->flush();

        // Create the description as a member translation using the current language
        $description = new MemberTranslation();
        $description
            ->setOwner($member->getId())
            ->setIdTranslator($member->getId())
            ->setSentence($data->description)
            ->setIdrecord($group->getId())
            ->setLanguage($language);
        $em->persist($description);
        $em->flush();

        // Add a comment for the creator of the group in English
        $groupComment = new MemberTranslation();
        $groupComment
            ->setOwner($member->getId())
            ->setIdtranslator($member->getId())
            ->setSentence('Group creator')
            ->setIdrecord($group->getId())
            ->setLanguage($english);
        $em->persist($groupComment);
        $em->flush();

        $groupMembership = new GroupMembership();
        $groupMembership
            ->setStatus(GroupMembershipStatusType::CURRENT_MEMBER)
            ->addComment($groupComment)
            ->setGroup($group)
            ->setMember($member);

        $member->addGroupMembership($groupMembership);
        $group->addGroupMembership($groupMembership);

        // Link group and description
        $group->addDescription($description);
        $em->persist($group);
        $em->flush();

        /** @var Role $groupOwner */
        $roleRepository = $em->getRepository(Role::class);
        $groupOwner = $roleRepository->findOneBy(['name' => Role::GROUP_OWNER]);

        /** @var Privilege $groupController */
        $privilegeRepository = $em->getRepository(Privilege::class);
        $groupController = $privilegeRepository->findOneBy(['controller' => Privilege::GROUP_CONTROLLER]);

        $privilegeScopeRepository = $em->getRepository(PrivilegeScope::class);
        $privilege = $privilegeScopeRepository->findOneBy(['member' => $member, 'role' => $groupOwner, 'privilege' => $groupController]);

        if (null === $privilege) {
            $privilege = new PrivilegeScope();
        }
        $privilege
            ->setMember($member)
            ->setRole($groupOwner)
            ->setPrivilege($groupController)
            ->setType($group->getId());
        $em->persist($privilege);
        $em->flush();

        return $group;
    }

    public function acceptJoin(Group $group, Member $member, Member $admin)
    {
        if (!$this->checkMembershipStatus($group, $member, GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP)) {
            return false;
        }

        $this->updateMembership($group, $member, GroupMembershipStatusType::CURRENT_MEMBER);
        $this->sendTemplateEmail($admin, $member, 'group/approve.join', [
            'subject' => 'group.approved.join',
            'group' => $group,
            'group_start' => '<a href="' . $this->urlGenerator->generate('group_start', [
                    'group_id' => $group->getId(),
                ], UrlGenerator::ABSOLUTE_URL) . '">',
            'group_end' => '</a>',
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
        $this->sendTemplateEmail($admin, $member, 'group/decline.join', [
            'subject' => 'group.approved.join',
            'group' => $group,
            'group_start' => '<a href="' . $this->urlGenerator->generate('group_start', [
                    'group_id' => $group->getId(),
                ], UrlGenerator::ABSOLUTE_URL) . '">',
            'group_end' => '</a>',
            'member' => $member,
            'admin' => $admin,
        ]);

        return true;
    }

    /**
     * @param $group
     * @param $member
     *
     * @return object|null
     */
    private function getMembership($group, $member)
    {
        $membershipRepository = $this->getManager()->getRepository(GroupMembership::class);
        $membership = $membershipRepository->findOneBy([
            'group' => $group,
            'member' => $member,
        ]);

        return $membership;
    }

    /**
     * @param Group  $group
     * @param Member $member
     * @param string $status
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function updateMembership(Group $group, Member $member, string $status)
    {
        $membershipRepository = $this->getManager()->getRepository(GroupMembership::class);
        $membership = $membershipRepository->findOneBy(['group' => $group, 'member' => $member]);

        $membership->setStatus($status);
        $this->getManager()->persist($membership);
        $this->getManager()->flush();
    }

    private function checkMembershipStatus(Group $group, Member $member, string $status)
    {
        $membershipRepository = $this->getManager()->getRepository(GroupMembership::class);
        $membership = $membershipRepository->findOneBy(['group' => $group, 'member' => $member]);

        return $status === $membership->getStatus();
    }

/*    private function informGroupAdmins(Group $group, $member)
    {
        $admins = $group->getAdmins();

        if (!empty($admins)) {
            foreach ($admins as $admin) {
                $this->sendTemplateEmail('group@bewelcome.org', $admin, 'group.approve.join', [
                    'subject' => 'group.approve.join',
                    'member' => $member,
                ]);
            }
        }
    }
*/
}
