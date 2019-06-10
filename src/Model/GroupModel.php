<?php

namespace App\Model;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\GroupTypeType;
use App\Doctrine\InFolderType;
use App\Doctrine\SpamInfoType;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\MemberTranslation;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\Subject;
use App\Form\JoinGroupType;
use App\Utilities\MailerTrait;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use PDepend\Engine;
use Swift_Message;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\NamedAddress;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

class GroupModel
{
    use ManagerTrait;
    use MailerTrait;
    use TranslatorTrait;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;
    /**
     * @var EngineInterface
     */
    private $engine;

    public function __construct(UrlGeneratorInterface $urlGenerator, EngineInterface $engine)
    {
        $this->urlGenerator = $urlGenerator;
        $this->engine = $engine;
    }

    /**
     * @param Group $group
     * @param Member $member
     * @param Member $admin
     *
     * @return boolean
     */
    public function inviteMemberToGroup(Group $group, Member $member, Member $admin)
    {
        $membership = new GroupMembership();
        $membership->setGroup($group);
        $membership->setMember($member);
        $membership->setStatus(GroupMembershipStatusType::INVITED_INTO_GROUP);
        $success = false;
        try {
            $this->getManager()->persist($membership);
            $this->getManager()->flush();
            $success = true;

            $msg = new Message();
            $msg->setMessageType('MemberToMember');
            $msg->setParent(null);
            $msg->setReceiver($member);
            $msg->setSender($admin);
            $msg->setStatus('Sent');
            $msg->setSpamInfo(SpamInfoType::NO_SPAM);
            $url = $this->urlGenerator->generate('group_start', [ 'group_id' => $group->getId()]);
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
            $subjectText = $this->getTranslator()->trans('group.invitation');
            $subject = new Subject();
            $subject->setSubject($subjectText);
            $this->getManager()->persist($subject);
            $this->getManager()->flush();

            $this->sendTemplateEmail($admin, $member, 'group.invitation', [
                'subject' => 'group.invitation',
                'group' => $group,
                'accept_start' => $acceptTag,
                'accept_end' => '</a>',
                'decline_start' => $declineTag,
                'decline_end' => '</a>',
            ]);
            $msg->setSubject($subject);
            // \todo get content of email into this message
            $msg->setMessage('You got an email...');

            $msg->setFolder(InFolderType::NORMAL);
            $this->getManager()->persist($msg);

            $note = new Notification();
            $note->setMember($member);
            $note->setRelMember($admin);
            $note->setType('message');
            $note->setLink($url);
            $note->setWordCode('');
            $note->setTranslationparams(serialize(['GroupsInvitedNote', $group->getName()]));
            $this->getManager()->persist($note);
            $this->getManager()->flush();
        } catch (Exception $e) {
        }

        return $success;
    }

    /**
     * @param Group $group
     * @param Member $member
     *
     * @return boolean
     */
    public function acceptInviteToGroup(Group $group, Member $member)
    {
        $success = false;
        $membershipRepository = $this->getManager()->getRepository(GroupMembership::class);

        $membership = null;
        try {
            $membership = $membershipRepository->findOneBy([
                'group' => $group,
                'member' => $member,
            ]);

            if ($membership) {
                $membership->setStatus('In');
                $this->getManager()->persist($membership);
                $this->getManager()->flush();
                $success = true;
            }
        } catch (Exception $e) {
        }

        return $success;
    }

    /**
     * @param Group $group
     * @param Member $member
     *
     * @return boolean
     */
    public function withdrawInviteMemberToGroup(Group $group, Member $member)
    {
        $success = false;
        $membershipRepository = $this->getManager()->getRepository(GroupMembership::class);

        $membership = null;
        try {
            $membership = $membershipRepository->findOneBy([
                'group' => $group,
                'member' => $member,
            ]);

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
            $language = $languageRepository->findOneBy([ 'shortcode' => $locale]);

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
            $membership->setNotificationsenabled($notifications === 'yes' ? true : false);
            if (GroupTypeType::NEED_ACCEPTANCE === $group->getType()) {
                $membership->setStatus(GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP);

                // \todo send message to group admins
            } else {
                $membership->setStatus(GroupMembershipStatusType::CURRENT_MEMBER);
            }

            $this->getManager()->persist($membership);
            $this->getManager()->flush();
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
     * @return Group
     *
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
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

        // Create the description as a member trad using the current language
        $description = new MemberTranslation();
        $description
            ->setOwner($member)
            ->setIdTranslator($member->getId())
            ->setSentence($data->description)
            ->setIdrecord($group->getId())
            ->setLanguage($language);
        $em->persist($description);
        $em->flush();

        // Add a comment for the creator of the group in English
        $groupComment = new MemberTranslation();
        $groupComment
            ->setOwner($member)
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

        // Now add the current member as admin for this group
        $connection = $this->getManager()->getConnection();
        /** @var Statement $stmt */
        $stmt = $connection->prepare('
                REPLACE INTO 
                    `privilegescopes`
                SET
                    `Idmember` = :memberId,
                    `IdRole` = 2,
                    `IdPrivilege` = 3,
                    `IdType` = :groupId,
                    `updated` = :updated
            ');
        $stmt->execute([
            ':groupId' => $group->getId(),
            ':memberId' => $member->getId(),
            'updated' => (new DateTime())->format('Y-m-d'),
        ]);

        return $group;
    }

    public function acceptJoin(Group $group, Member $member, Member $admin)
    {
        $this->updateMembership($group, $member, GroupMembershipStatusType::CURRENT_MEMBER);
        $this->sendTemplateEmail($admin, $member, 'group.approved.join');
    }

    public function declineJoin(Group $group, Member $member, Member $admin)
    {
        $this->updateMembership($group, $member, GroupMembershipStatusType::KICKED_FROM_GROUP);
        $this->sendTemplateEmail($admin, $member, 'group.declined.join');
    }


    /**
     * @param Group $group
     * @param Member $member
     * @param string $CURRENT_MEMBER
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function updateMembership(Group $group, Member $member, string $CURRENT_MEMBER)
    {
        $membershipRepository = $this->getManager()->getRepository(GroupMembership::class);
        $membership = $membershipRepository->findOneBy([ 'group' => $group, 'member' => $member]);

        $membership->setStatus();
        $this->getManager()->persist($membership);
        $this->getManager()->flush();
    }
}
