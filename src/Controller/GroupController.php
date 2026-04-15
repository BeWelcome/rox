<?php

namespace App\Controller;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\GroupType as DoctrineGroupType;
use App\Doctrine\MemberStatusType;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Member;
use App\Entity\Wiki;
use App\Form\CustomDataClass\GroupRequest;
use App\Form\GroupType;
use App\Form\JoinGroupType;
use App\Form\WikiCreateForm;
use App\Logger\Logger;
use App\Model\GroupModel;
use App\Model\WikiModel;
use App\Repository\GroupRepository;
use App\Repository\WikiRepository;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use App\Utilities\UniqueFilenameTrait;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class GroupController.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 *
 * \todo Move membership handling into own controller.
 */
class GroupController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;
    use UniqueFilenameTrait;

    public function __construct(
        private GroupModel $groupModel,
    ) {
    }

    /**
     * @return RedirectResponse
     */
    #[Route(path: '/groups/', name: 'groups_redirect', priority: 10)]
    public function redirectToOverviewPage()
    {
        /** @var Member|null $member */
        $member = $this->getUser();
        if ($member !== null && $member->getGroups()) {
            return $this->redirectToRoute('groups_mygroups');
        }

        return $this->redirectToRoute('groups_search');
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     *
     * @return RedirectResponse
     */
    #[Route(path: '/groups/{groupId:group}/{path}', name: 'groups_redirect_path', requirements: ['groupId' => '\d+', 'path' => '.+'])]
    public function groupsRedirectPath(Request $request)
    {
        // We only need the request
        return $this->redirectGroup($request);
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     *
     * @return RedirectResponse
     */
    #[Route(path: '/groups/{groupId:group}', name: 'groups_redirect_group', requirements: ['groupId' => '\d+'])]
    public function groupsRedirect(Request $request)
    {
        return $this->redirectGroup($request);
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     *
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     * @SuppressWarnings("PHPMD.NPathComplexity")
     */
    #[Route(path: '/group/{groupId:group}/join', name: 'join_group')]
    public function join(Request $request, Group $group): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        if ($group->isMember($member)) {
            return $this->redirectToRoute('group_membersettings', [
                'group_id' => $group->getId(),
            ]);
        }

        if (DoctrineGroupType::INVITE_ONLY === $group->getType()) {
            $this->addTranslatedFlash('notice', 'flash.group.need.invite');

            return $this->redirectToRoute('groups');
        }

        /** @var GroupMembership $membership */
        $membership = $group->getGroupMembership($member);
        if (DoctrineGroupType::NEED_ACCEPTANCE === $group->getType()) {
            // Check if a join request is currently open

            if (false !== $membership) {
                $this->addTranslatedFlash('notice', 'flash.group.already.applied');

                return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
            }
        }

        if (DoctrineGroupType::PUBLIC === $group->getType()) {
            if (false !== $membership && GroupMembershipStatusType::INVITED_INTO_GROUP === $membership->getStatus()) {
                // Accept the invitation
                return $this->acceptInviteToGroup($group, $member);
            }
        }

        $joinForm = $this->createForm(JoinGroupType::class);
        $joinForm->handleRequest($request);

        if ($joinForm->isSubmitted() && $joinForm->isValid()) {
            $data = $joinForm->getData();
            $success = $this->groupModel->join($group, $member, $data, $request->getLocale());
            if ($success) {
                if (!$group->isPublic()) {
                    $this->addTranslatedFlash('notice', 'flash.group.join.await.acceptance');
                } else {
                    $this->addTranslatedFlash('notice', 'flash.group.join.success');
                }
            } else {
                $this->addTranslatedFlash('notice', 'flash.group.join.failure');
            }

            return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
        }

        return $this->render('group/join.html.twig', [
            'form' => $joinForm->createView(),
            'group' => $group,
            'submenu' => [
                'active' => 'join',
                'items' => $this->getSubmenuItems($member, $group),
            ],
        ]);
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    #[Route(path: '/group/{groupId}/acceptjoin/{memberId}', name: 'group_accept_join')]
    public function approveJoin(Group $group, Member $member)
    {
        /** @var Member $admin */
        $admin = $this->getUser();
        if (!$group->isAdmin($admin)) {
            throw $this->createAccessDeniedException('No group admin');
        }

        if ($this->groupModel->acceptJoin($group, $member, $admin)) {
            $this->addTranslatedFlash('notice', 'flash.group.accepted.join', [
                'name' => $group->getName(),
                'username' => $member->getUsername(),
            ]);
        } else {
            $this->addTranslatedFlash('notice', 'flash.group.membershipstatus.not.changed', [
                'name' => $group->getName(),
                'username' => $member->getUsername(),
            ]);
        }

        return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    #[Route(path: '/group/{groupId}/declinejoin/{memberId}', name: 'group_decline_join')]
    public function declineJoin(Group $group, Member $member)
    {
        /** @var Member $admin */
        $admin = $this->getUser();
        if (!$group->isAdmin($admin)) {
            throw $this->createAccessDeniedException('No group admin');
        }

        if ($this->groupModel->declineJoin($group, $member, $admin)) {
            $this->addTranslatedFlash('notice', 'flash.group.declined.join', [
                'name' => $group->getName(),
                'username' => $member->getUsername(),
            ]);
        } else {
            $this->addTranslatedFlash('notice', 'flash.group.membershipstatus.not.changed', [
                'name' => $group->getName(),
                'username' => $member->getUsername(),
            ]);
        }

        return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    #[Route(path: '/group/{groupId}/invite/{memberId}', name: 'invite_member_to_group')]
    public function inviteMemberToGroup(Group $group, Member $member)
    {
        /** @var Member $admin */
        $admin = $this->getUser();
        if (!$group->isAdmin($admin)) {
            throw $this->createAccessDeniedException();
        }

        $success = $this->groupModel->inviteMemberToGroup($group, $member, $admin);

        return new JsonResponse([
            'success' => $success,
        ]);
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @return RedirectResponse
     */
    #[Route(path: '/group/{groupId}/accept/{memberId}', name: 'accept_invite_to_group')]
    public function acceptInviteToGroup(Group $group, Member $member)
    {
        $success = $this->groupModel->acceptInviteToGroup($group, $member);

        if ($success) {
            $this->groupModel->sendAdminNotificationAccepted($group, $member);
            $this->addTranslatedFlash('notice', 'flash.invite.accepted');
        } else {
            $this->addTranslatedFlash('error', 'flash.invite.accepted.error');
        }

        return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @return RedirectResponse
     */
    #[Route(path: '/group/{groupId}/decline/{memberId}', name: 'decline_invite_to_group')]
    public function declineInviteToGroup(Group $group, Member $member)
    {
        $success = $this->groupModel->declineInviteToGroup($group, $member);

        if ($success) {
            $this->groupModel->sendAdminNotificationDeclined($group, $member);
            $this->addTranslatedFlash('notice', 'flash.invite.declined');
        } else {
            $this->addTranslatedFlash('error', 'flash.invite.declined.error');
        }

        return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
    }

    /**
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @return RedirectResponse
     */
    #[Route(path: '/group/{groupId}/withdraw/{memberId}', name: 'withdraw_member_invite_to_group')]
    public function withdrawInviteMemberGroup(Request $request, Group $group, Member $member)
    {
        $success = $this->groupModel->withdrawInviteMemberToGroup($group, $member);

        if ($success) {
            $this->addTranslatedFlash('notice', 'flash.group.invite.withdrawn');
        } else {
            $this->addTranslatedFlash('error', 'flash.group.invite.withdrawn.error');
        }
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    #[Route(path: '/new/group', name: 'new_group')]
    public function createGroup(Request $request, Logger $logger): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        if (MemberStatusType::ACCOUNT_ACTIVATED === $member->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.group.not.confirmed');

            return $this->redirectToRoute('groups_mygroups');
        }

        $groupRequest = new GroupRequest();
        $form = $this->createForm(GroupType::class, $groupRequest, [
            'allowInvitationOnly' => $member->getLevelForRight(Member::ROLE_ADMIN_GROUP),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var Member $member */
            $member = $this->getUser();

            $groupPicture = $this->handleGroupPicture($data->picture);

            $group = $this->groupModel->new($data, $request->getLocale(), $member, $groupPicture);

            $this->addTranslatedFlash('notice', 'flash.group.create.successful', [
                '%name%' => $group->getName(),
            ]);

            $logger->write('Group ' . $group->getName() . ' created by ' . $member->getUsername() . '.', 'Group');

            return $this->redirectToRoute('groups_redirect');
        }

        return $this->render('group/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/new/group/check', name: 'new_group_check')]
    public function ajaxCheckNewGroup(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $groupName = trim($request->request->get('name'));

        $html = '';
        if (!empty($groupName)) {
            $parts = explode(' ', $groupName);

            /** @var GroupRepository $groupRepository */
            $groupRepository = $entityManager->getRepository(Group::class);
            $groups = $groupRepository->findByNameParts($parts);

            // Check if there are duplicate groups and provide a list of these

            $html = $this->renderView('group/check.html.twig', [
                'groups' => $groups,
            ]);
        }

        return new JsonResponse([
            'html' => $html,
        ]);
    }

    #[Route(path: '/group/{id}/wiki', name: 'group_wiki_page')]
    public function showGroupWikiPage(
        Group $group,
        WikiModel $wikiModel,
        EntityManagerInterface $entityManager,
    ): Response {
        $member = $this->getUser();

        $pageName = $wikiModel->getPageName('Group_' . $group->getName());

        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $entityManager->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName, 0);
        $historyPagination = null;
        if (null === $wikiPage) {
            $output = null;
        } else {
            $output = $wikiModel->parseWikiMarkup($wikiPage->getContent());
            // Create paginator for history
            $history = $wikiModel->getHistory($wikiPage);

            $adapter = new ArrayAdapter($history);
            $historyPagination = new Pagerfanta($adapter);
            $historyPagination->setMaxPerPage(1);
            $historyPagination->setCurrentPage($historyPagination->getNbResults());
        }

        return $this->render('group/wiki.html.twig', [
            'title' => 'Group ' . $group->getName(),
            'submenu' => [
                'active' => 'wiki',
                'items' => $this->getSubmenuItems($member, $group),
            ],
            'group' => $group,
            'content' => $output,
            'wikipage' => $wikiPage,
            'history' => $historyPagination,
        ]);
    }

    /**
     * @return RedirectResponse
     */
    #[Route(path: '/group/{id}/wiki/create', name: 'group_wiki_page_create')]
    public function createGroupWikiPage(Group $group, WikiModel $wikiModel)
    {
        $pageName = $wikiModel->getPageName('Group_' . $group->getName());

        /** @var Wiki $wikiPage */
        $wikiPage = $wikiModel->getPage($pageName);

        if (null === $wikiPage) {
            $wikiModel->createWikiPage($pageName, '');
        }

        return $this->redirectToRoute('group_wiki_page_edit', ['id' => $group->getId()]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/group/{id}/wiki/edit', name: 'group_wiki_page_edit')]
    public function editGroupWikiPage(Request $request, Group $group, WikiModel $wikiModel)
    {
        /** @var Member $member */
        $member = $this->getUser();

        $pageName = $wikiModel->getPageName('Group_' . $group->getName());

        /** @var Wiki $wikiPage */
        $wikiPage = $wikiModel->getPage($pageName);

        if (null === $wikiPage) {
            return $this->redirectToRoute('group_wiki_page_create');
        }

        $form = $this->createForm(WikiCreateForm::class, ['wiki_markup' => $wikiPage->getContent()]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $wikiModel->addNewVersion($wikiPage, $data['wiki_markup']);
            $this->addTranslatedFlash('notice', 'flash.wiki.updated');

            return $this->redirectToRoute('group_wiki_page', ['id' => $group->getId()]);
        }

        return $this->render('group/wiki.edit.html.twig', [
            'title' => $group->getName(),
            'submenu' => [
                'active' => 'wiki',
                'items' => $this->getSubmenuItems($member, $group),
            ],
            'form' => $form->createView(),
        ]);
    }

    private function redirectGroup(Request $request)
    {
        $pathInfo = str_replace('/groups/', '/group/', $request->getPathInfo());

        return new RedirectResponse($pathInfo);
    }

    /**
     * @return array
     */
    private function getSubmenuItems(?Member $member, Group $group)
    {
        $groupId = $group->getId();
        $submenuItems = [
            'overview' => [
                'key' => 'GroupOverview',
                'url' => $this->generateUrl('group_start', ['group_id' => $groupId]),
            ],
            'forum' => [
                'key' => 'GroupDiscussions',
                'url' => $this->generateUrl('group_forum', ['group_id' => $groupId]),
            ],
            'wiki' => [
                'key' => 'GroupWiki',
                'url' => $this->generateUrl('group_wiki_page', ['id' => $groupId]),
            ],
            'members' => [
                'key' => 'GroupMembers',
                'url' => $this->generateUrl('group_members', ['group_id' => $groupId]),
            ],
        ];
        // \todo: Check if current user is member of this group
        if (\in_array($member, $group->getCurrentMembers(), true)) {
            $submenuItems['membersettings'] = [
                'key' => 'GroupMembersettings',
                'url' => $this->generateUrl('group_membersettings', ['group_id' => $groupId]),
            ];
            $submenuItems['relatedgroupsettings'] = [
                'key' => 'GroupRelatedGroups',
                'url' => $this->generateUrl('relatedgroup_log', ['group_id' => $groupId]),
            ];
        } else {
            $submenuItems['join'] = [
                'key' => 'group.join',
                'url' => $this->generateUrl('join_group', ['groupId' => $groupId]),
            ];
        }

        return $submenuItems;
    }

    /**
     * @param UploadedFile $picture
     *
     * @return string
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    private function handleGroupPicture($picture)
    {
        // if a file was uploaded move it into the image storage
        $groupImageDir = $this->getParameter('group_directory');
        if (null !== $picture) {
            $fileName = $this->generateUniqueFileName() . '.' . $picture->guessExtension();

            // moves the file to the directory where group images are stored
            $picture->move(
                $groupImageDir,
                $fileName
            );
            $imageManager = new ImageManager(new Driver());
            $img = $imageManager->read($groupImageDir . '/' . $fileName);
            $img->scale(width: 80);
            $img->save($groupImageDir . '/thumb' . $fileName);

            return $fileName;
        }

        return null;
    }
}
