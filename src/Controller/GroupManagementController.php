<?php

namespace App\Controller;

use App\Doctrine\GroupMembershipStatusType;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Member;
use App\Form\CustomDataClass\GroupSettingsRequest;
use App\Form\GroupMembershipSettingsType;
use App\Form\GroupSettingsType;
use App\Logger\Logger;
use App\Model\GroupModel;
use App\Repository\GroupRepository;
use App\Security\GroupVoter;
use App\Utilities\GroupSubmenuTrait;
use App\Utilities\MailLinkTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use App\Utilities\UniqueFilenameTrait;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * What members and admins do with a group: a member's settings and leaving, the admins' member administration and
 * group settings, the delete confirmation. The URLs are the ones the legacy group pages had; whatever changes
 * something is a POST with a CSRF token.
 */
class GroupManagementController extends AbstractController
{
    use GroupSubmenuTrait;
    use MailLinkTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;
    use UniqueFilenameTrait;

    private const int MEMBERS_PER_PAGE = 50;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GroupModel $groupModel,
        private readonly Logger $logger,
    ) {
    }

    #[Route(path: '/group/{group_id}/membersettings', name: 'group_membersettings', requirements: ['group_id' => '\d+'], methods: ['GET', 'POST'])]
    public function memberSettings(Request $request, #[MapEntity(id: 'group_id')] Group $group): Response
    {
        $member = $this->getMember();
        $membership = $this->findMembership($group, $member);
        if (null === $membership || GroupMembershipStatusType::CURRENT_MEMBER !== $membership->getStatus()) {
            return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
        }

        $form = $this->createForm(GroupMembershipSettingsType::class, [
            'comment' => $membership->getComment(),
            'acceptMassMail' => $membership->isAcceptMassMail(),
            'notificationsEnabled' => $membership->isNotificationsEnabled(),
            'shownOnProfile' => $membership->isShownOnProfile(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->groupModel->updateMembershipSettings(
                $membership,
                $data['comment'],
                (bool) $data['acceptMassMail'],
                (bool) $data['notificationsEnabled'],
            );
            $membership->setShownOnProfile((bool) $data['shownOnProfile']);
            $this->entityManager->flush();
            $this->addTranslatedFlash('notice', 'groupmembersettingsupdated');
            $this->logger->write("Member #{$member->getId()} changed own member settings for group #{$group->getId()}", 'Group');

            return $this->redirectToRoute('group_membersettings', ['group_id' => $group->getId()]);
        }

        return $this->render('group/membersettings.html.twig', [
            'group' => $group,
            'form' => $form,
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'membersettings'],
        ]);
    }

    #[Route(path: '/group/{group_id}/leave', name: 'group_leave', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function leave(#[MapEntity(id: 'group_id')] Group $group): Response
    {
        $member = $this->getMember();
        if ($this->groupModel->isAdministrator($group, $member)) {
            $this->addTranslatedFlash('error', 'groupleavefail_resignadminfirst');

            return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
        }

        return $this->render('group/leave.html.twig', [
            'group' => $group,
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'overview'],
        ]);
    }

    #[Route(path: '/group/{group_id}/leave/true', name: 'group_left', requirements: ['group_id' => '\d+'], methods: ['POST'])]
    public function left(Request $request, #[MapEntity(id: 'group_id')] Group $group): RedirectResponse
    {
        $this->checkCsrfToken($request, 'group-leave-' . $group->getId());
        $member = $this->getMember();
        if ($this->groupModel->leave($group, $member)) {
            $this->addTranslatedFlash('notice', 'groupsleavesuccess');
            $this->logger->write("Member #{$member->getId()} left group #{$group->getId()}", 'Group');
        } else {
            $this->addTranslatedFlash('error', 'groupsleavefail');
        }

        return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
    }

    #[Route(path: '/group/{group_id}/memberadministration', name: 'group_memberadministration', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    #[Route(
        path: '/group/{group_id}/memberadministration/page/{page_number}',
        name: 'group_memberadministration_paged',
        requirements: ['group_id' => '\d+', 'page_number' => '\d+'],
        methods: ['GET'],
    )]
    public function memberAdministration(#[MapEntity(id: 'group_id')] Group $group, int $page_number = 1): Response
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_ADMINISTER, $group);

        /** @var GroupRepository $repository */
        $repository = $this->entityManager->getRepository(Group::class);
        $members = new Pagerfanta(new QueryAdapter($repository->createMembersQueryBuilder($group), false, false));
        $members->setMaxPerPage(self::MEMBERS_PER_PAGE);
        $members->setCurrentPage(min(max(1, $page_number), max(1, $members->getNbPages())));

        $membershipRepository = $this->entityManager->getRepository(GroupMembership::class);

        return $this->render('group/memberadministration.html.twig', [
            'group' => $group,
            'members' => $members,
            'applicants' => $membershipRepository->findBy(['group' => $group, 'status' => GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP]),
            'invited' => $membershipRepository->findBy(['group' => $group, 'status' => GroupMembershipStatusType::INVITED_INTO_GROUP]),
            'administratorIds' => array_map(static fn (Member $admin): int => $admin->getId(), $group->getAdministrators()),
            'submenu' => ['items' => $this->getSubmenuItems($this->getMember(), $group), 'active' => 'memberadministration'],
        ]);
    }

    #[Route(path: '/group/{group_id}/acceptmember/{member_id}', name: 'group_acceptmember', requirements: ['group_id' => '\d+', 'member_id' => '\d+'], methods: ['POST'])]
    public function acceptMember(Request $request, #[MapEntity(id: 'group_id')] Group $group, #[MapEntity(id: 'member_id')] Member $member): RedirectResponse
    {
        return $this->administerMember($request, $group, $member, 'accept', function () use ($group, $member): bool {
            return $this->groupModel->acceptJoin($group, $member, $this->getMember());
        }, 'groupsmemberacceptsuccess', 'flash.group.membershipstatus.not.changed');
    }

    #[Route(path: '/group/{group_id}/declinemember/{member_id}', name: 'group_declinemember', requirements: ['group_id' => '\d+', 'member_id' => '\d+'], methods: ['POST'])]
    public function declineMember(Request $request, #[MapEntity(id: 'group_id')] Group $group, #[MapEntity(id: 'member_id')] Member $member): RedirectResponse
    {
        return $this->administerMember($request, $group, $member, 'decline', function () use ($group, $member): bool {
            return $this->groupModel->declineJoin($group, $member, $this->getMember());
        }, 'groupsmemberdeclinesuccess', 'flash.group.membershipstatus.not.changed');
    }

    #[Route(path: '/group/{group_id}/kickmember/{member_id}', name: 'group_kickmember', requirements: ['group_id' => '\d+', 'member_id' => '\d+'], methods: ['POST'])]
    public function kickMember(Request $request, #[MapEntity(id: 'group_id')] Group $group, #[MapEntity(id: 'member_id')] Member $member): RedirectResponse
    {
        return $this->administerMember($request, $group, $member, 'kick', function () use ($group, $member): bool {
            return $this->groupModel->removeMember($group, $member, false);
        }, 'memberkicksuccess', 'memberkickfail');
    }

    #[Route(path: '/group/{group_id}/banmember/{member_id}', name: 'group_banmember', requirements: ['group_id' => '\d+', 'member_id' => '\d+'], methods: ['POST'])]
    public function banMember(Request $request, #[MapEntity(id: 'group_id')] Group $group, #[MapEntity(id: 'member_id')] Member $member): RedirectResponse
    {
        return $this->administerMember($request, $group, $member, 'ban', function () use ($group, $member): bool {
            return $this->groupModel->removeMember($group, $member, true);
        }, 'group.member.banned', 'group.member.ban.failed');
    }

    #[Route(path: '/group/{group_id}/addAdmin/{member_id}', name: 'group_addadmin', requirements: ['group_id' => '\d+', 'member_id' => '\d+'], methods: ['POST'])]
    public function addAdministrator(Request $request, #[MapEntity(id: 'group_id')] Group $group, #[MapEntity(id: 'member_id')] Member $member): RedirectResponse
    {
        return $this->administerMember($request, $group, $member, 'admin', function () use ($group, $member): bool {
            return $this->groupModel->addAdministrator($group, $member);
        }, 'groupnewadminsuccess', 'group.admin.add.failed');
    }

    #[Route(path: '/group/{group_id}/invite', name: 'group_invite_member', requirements: ['group_id' => '\d+'], methods: ['POST'])]
    public function inviteMember(Request $request, #[MapEntity(id: 'group_id')] Group $group): RedirectResponse
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_ADMINISTER, $group);
        $this->checkCsrfToken($request, 'group-invite-' . $group->getId());

        $username = trim((string) $request->request->get('username'));
        $member = '' === $username ? null : $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        if (null !== $member && $this->groupModel->inviteMemberToGroup($group, $member, $this->getMember())) {
            $this->addTranslatedFlash('notice', 'GroupsInvitedMember');
        } else {
            $this->addTranslatedFlash('error', 'groupscouldnotinvite');
        }

        return $this->redirectToRoute('group_memberadministration', ['group_id' => $group->getId()]);
    }

    #[Route(path: '/group/{group_id}/resignAdmin', name: 'group_resignadmin', requirements: ['group_id' => '\d+'], methods: ['POST'])]
    public function resignAsAdministrator(Request $request, #[MapEntity(id: 'group_id')] Group $group): RedirectResponse
    {
        $this->checkCsrfToken($request, 'group-resign-' . $group->getId());
        $member = $this->getMember();
        if (!$this->groupModel->isAdministrator($group, $member)) {
            throw $this->createAccessDeniedException();
        }

        if ($this->groupModel->removeAdministrator($group, $member)) {
            $this->addTranslatedFlash('notice', 'groupadminresignationsuccess');
            $this->logger->write("Member #{$member->getId()} resigned as admin of group #{$group->getId()}", 'Group');
        } else {
            $this->addTranslatedFlash('error', 'groupadminresignationfailed_lastadmin');
        }

        return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
    }

    #[Route(path: '/group/{group_id}/groupsettings', name: 'group_groupsettings', requirements: ['group_id' => '\d+'], methods: ['GET', 'POST'])]
    public function groupSettings(
        Request $request,
        #[MapEntity(id: 'group_id')] Group $group,
        #[Autowire('%group_directory%')] string $groupDirectory,
    ): Response {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_ADMINISTER, $group);
        $member = $this->getMember();

        $settings = GroupSettingsRequest::fromGroup($group, $request->getLocale());
        $form = $this->createForm(GroupSettingsType::class, $settings, ['locale' => $request->getLocale()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $settings->picture instanceof UploadedFile ? $this->storePicture($settings->picture, $groupDirectory) : null;
            $this->groupModel->updateSettings($group, $settings, $request->getLocale(), $member, $picture);
            $this->addTranslatedFlash('notice', 'groupmembersettingsupdated');
            $this->logger->write("Member #{$member->getId()} changed group settings for group #{$group->getId()}", 'Group');

            return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
        }

        return $this->render('group/settings.html.twig', [
            'group' => $group,
            'form' => $form,
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'groupsettings'],
        ]);
    }

    #[Route(path: '/group/{group_id}/delete', name: 'group_delete', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function deleteConfirmation(#[MapEntity(id: 'group_id')] Group $group): Response
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_ADMINISTER, $group);
        if ($group->isBeWelcome()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('group/delete.html.twig', [
            'group' => $group,
            'submenu' => ['items' => $this->getSubmenuItems($this->getMember(), $group), 'active' => 'overview'],
        ]);
    }

    /**
     * The links of old invitation mails: they have no signature (MailLinkSigner), so they only lead to the group.
     */
    #[Route(path: '/group/{group_id}/acceptinvitation/{member_id}', name: 'group_acceptinvitation', requirements: ['group_id' => '\d+', 'member_id' => '\d+'], methods: ['GET'])]
    public function acceptInvitation(int $group_id): RedirectResponse
    {
        return $this->rejectMailLink('group_start', ['group_id' => $group_id]);
    }

    #[Route(path: '/group/{group_id}/declineinvitation/{member_id}', name: 'group_declineinvitation', requirements: ['group_id' => '\d+', 'member_id' => '\d+'], methods: ['GET'])]
    public function declineInvitation(int $group_id): RedirectResponse
    {
        return $this->rejectMailLink('group_start', ['group_id' => $group_id]);
    }

    /**
     * @param callable(): bool $action
     */
    private function administerMember(
        Request $request,
        Group $group,
        Member $member,
        string $actionName,
        callable $action,
        string $success,
        string $failure,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_ADMINISTER, $group);
        $this->checkCsrfToken($request, "group-{$actionName}-{$group->getId()}-{$member->getId()}");

        if ($action()) {
            $this->addTranslatedFlash('notice', $success);
            $this->logger->write(
                "Member #{$member->getId()}: {$actionName} in group #{$group->getId()} by member #{$this->getMember()->getId()}",
                'Group',
            );
        } else {
            $this->addTranslatedFlash('error', $failure);
        }

        return $this->redirectToRoute('group_memberadministration', ['group_id' => $group->getId()]);
    }

    private function checkCsrfToken(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function findMembership(Group $group, Member $member): ?GroupMembership
    {
        return $this->entityManager->getRepository(GroupMembership::class)->findOneBy(['group' => $group, 'member' => $member]);
    }

    /**
     * Stores an uploaded group picture and its thumbnail; returns the file name.
     */
    private function storePicture(UploadedFile $picture, string $groupDirectory): string
    {
        $fileName = $this->generateUniqueFileName() . '.' . $picture->guessExtension();
        $picture->move($groupDirectory, $fileName);
        $image = new ImageManager(new Driver())->read($groupDirectory . '/' . $fileName);
        $image->scale(width: 80);
        $image->save($groupDirectory . '/thumb' . $fileName);

        return $fileName;
    }

    private function getMember(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }
}
