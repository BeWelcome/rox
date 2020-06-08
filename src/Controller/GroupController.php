<?php

namespace App\Controller;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\GroupType as DoctrineGroupType;
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
use App\Utilities\MailerTrait;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use App\Utilities\UniqueFilenameTrait;
use Exception;
use Intervention\Image\ImageManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class GroupController.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class GroupController extends AbstractController
{
    use MailerTrait;
    use ManagerTrait;
    use TranslatorTrait;
    use TranslatedFlashTrait;
    use UniqueFilenameTrait;

    /**
     * @var GroupModel
     */
    private $groupModel;

    public function __construct(GroupModel $groupModel)
    {
        $this->groupModel = $groupModel;
    }

    /**
     * @Route("/groups/{groupId}/{path}", name="groups_redirect_path",
     *     requirements = {"groupId": "\d+", "path":".+"})
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     *
     * @return RedirectResponse
     */
    public function groupsRedirectPath(Request $request, Group $group, string $path)
    {
        return $this->redirectGroup($request);
    }

    /**
     * @Route("/groups/{groupId}", name="groups_redirect",
     *     requirements = {"groupId": "\d+"})
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     *
     * @return RedirectResponse
     */
    public function groupsRedirect(Request $request, Group $group)
    {
        return $this->redirectGroup($request);
    }

    /**
     * @Route("/group/{groupId}/join", name="join_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function join(Request $request, Group $group)
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
     * @Route("/group/{groupId}/acceptjoin/{memberId}", name="group_accept_join")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
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
     * @Route("/group/{groupId}/declinejoin/{memberId}", name="group_decline_join")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
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
     * @Route("/group/{groupId}/invite/{memberId}", name="invite_member_to_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
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
     * @Route("/group/{groupId}/accept/{memberId}", name="accept_invite_to_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @return RedirectResponse
     */
    public function acceptInviteToGroup(Group $group, Member $member)
    {
        $success = $this->groupModel->acceptInviteToGroup($group, $member);

        if ($success) {
            $admins = $group->getAdmins();
            $this->sendAdminNotification($group, $member, $admins);
            $this->addTranslatedFlash('notice', 'flash.invite.accepted');
        } else {
            $this->addTranslatedFlash('error', 'flash.invite.accepted.error');
        }

        return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
    }

    /**
     * @Route("/group/{groupId}/decline/{memberId}", name="decline_invite_to_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @return RedirectResponse
     */
    public function declineInviteToGroup(Group $group, Member $member)
    {
        $success = $this->groupModel->declineInviteToGroup($group, $member);

        if ($success) {
            $admins = $group->getAdmins();
            $this->sendAdminNotification($group, $member, $admins);
            $this->addTranslatedFlash('notice', 'flash.invite.declined');
        } else {
            $this->addTranslatedFlash('error', 'flash.invite.declined.error');
        }

        return $this->redirectToRoute('group_start', ['group_id' => $group->getId()]);
    }

    /**
     * @Route("/group/{groupId}/withdraw/{memberId}", name="withdraw_member_invite_to_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @return RedirectResponse
     */
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

    /**
     * @Route("/new/group", name="new_group")
     *
     * @throws Exception
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * Because of the mix between old code and new code this method is way too long.
     */
    public function createNewGroup(Request $request, Logger $logger)
    {
        /** @var Member $member */
        $member = $this->getUser();
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

            return $this->redirectToRoute('groups_overview');
        }

        return $this->render('group/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new/group/check", name="new_group_check")
     *
     * @return JsonResponse
     */
    public function ajaxCheckNewGroup(Request $request)
    {
        $groupName = trim($request->request->get('name'));

        $html = '';
        if (!empty($groupName)) {
            $parts = explode(' ', $groupName);

            /** @var GroupRepository $groupRepository */
            $groupRepository = $this->getDoctrine()->getRepository(Group::class);
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

    /**
     * @Route("/group/{id}/wiki", name="group_wiki_page")
     *
     * @return Response
     */
    public function showGroupWikiPage(Group $group, WikiModel $wikiModel)
    {
        $member = $this->getUser();

        $pageName = $wikiModel->getPageName('Group_' . $group->getName());

        $em = $this->getDoctrine();
        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $em->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName);

        if (null === $wikiPage) {
            $output = null;
        } else {
            $output = $wikiModel->parseWikiMarkup($wikiPage->getContent());
        }

        return $this->render('group/wiki.html.twig', [
            'title' => 'Group ' . $group->getName(),
            'submenu' => [
                'active' => 'wiki',
                'items' => $this->getSubmenuItems($member, $group),
            ],
            'group' => $group,
            'wikipage' => $output,
        ]);
    }

    /**
     * @Route("/group/{id}/wiki/create", name="group_wiki_page_create")
     *
     * @return RedirectResponse
     */
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
     * @Route("/group/{id}/wiki/edit", name="group_wiki_page_edit")
     *
     * @return Response
     */
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
     * @param Member[] $admins
     */
    private function sendAdminNotification(Group $group, Member $member, $admins)
    {
        foreach ($admins as $admin) {
            $this->sendTemplateEmail('group@bewelcome.org', $admin, 'group/accept.invite', [
                'subject' => 'group.invitation.accepted',
                'group' => $group,
                'invitee' => $member,
                'admin' => $admin,
            ]);
        }
    }

    /**
     * @return array
     */
    private function getSubmenuItems(Member $member, Group $group)
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
     * @SuppressWarnings(PHPMD.StaticAccess)
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
            $imageManager = new ImageManager();
            $img = $imageManager->make($groupImageDir . '/' . $fileName);
            $img->resize(80, 80, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($groupImageDir . '/thumb' . $fileName);

            return $fileName;
        }

        return null;
    }
}
