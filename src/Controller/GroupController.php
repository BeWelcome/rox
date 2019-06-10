<?php

namespace App\Controller;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\GroupTypeType;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\MemberTranslation;
use App\Entity\Preference;
use App\Entity\Wiki;
use App\Form\CustomDataClass\GroupRequest;
use App\Form\GroupType;
use App\Form\JoinGroupType;
use App\Logger\Logger;
use App\Model\GroupModel;
use App\Model\WikiModel;
use App\Repository\GroupRepository;
use App\Repository\WikiRepository;
use App\Utilities\MailerTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Intervention\Image\ImageManagerStatic as Image;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class GroupController.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupController extends AbstractController
{
    use TranslatorTrait, TranslatedFlashTrait;
    use MailerTrait;

    /**
     * @var GroupModel
     */
    private $groupModel;

    public function __construct(GroupModel $groupModel)
    {
        $this->groupModel = $groupModel;
    }

    /**
     * @Route("/group/{groupId}/join", name="join_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @param Request $request
     * @param Group $group
     * @return Response
     */
    public function join(Request $request, Group $group)
    {
        $member = $this->getUser();
        if ($group->isMember($member)) {
            return $this->redirectToRoute('group_membersettings', [
                'group_id' => $group->getId(),
            ]);
        }

        if (GroupTypeType::INVITE_ONLY === $group->getType()) {
            $this->addTranslatedFlash('notice', 'flash.group.need.invite');
            return $this->redirectToRoute('groups');
        }

        if (GroupTypeType::NEED_ACCEPTANCE === $group->getType()) {
            // Check if a join request is currently open
            $membership = $group->getGroupMembership($member);

            if (false !== $membership) {
                $this->addTranslatedFlash('notice', 'flash.group.already.applied');
                return $this->redirectToRoute('group_start', [ 'group_id' => $group->getId()]);
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
                    $this->informGroupAdmins($group, $member);
                } else {
                    $this->addTranslatedFlash('notice', 'flash.group.join.success');
                }
            } else {
                $this->addTranslatedFlash('notice', 'flash.group.join.failure');
            }
            return $this->redirectToRoute('group_start', [ 'group_id' => $group->getId()]);
        }

        return $this->render('group/join.html.twig', [
            'form' => $joinForm->createView(),
            'group' => $group,
            'submenu' => [
                'active' => 'join',
                'items' => $this->getSubmenuItems($member, $group),
            ]
        ]);
    }

    /**
     * @Route("/group/{groupId}/acceptjoin/{memberId}", name="group_accept_join")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @param Group $group
     * @param Member $member
     */
    public function approveJoin(Group $group, Member $member)
    {
        $admin = $this->getUser();
        if (!$group->isAdmin($admin)) {
            throw $this->createAccessDeniedException('No group admin');
        }

        $this->groupModel->acceptJoin($group, $member, $admin);

        $this->addTranslatedFlash('notice', 'flash.group.accepted.join', [
            'name' => $group->getName(),
            'username' => $member->getUsername(),
        ]);
    }

    /**
     * @Route("/group/{groupId}/declinejoin/{memberId}", name="group_accept_join")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @param Group $group
     * @param Member $member
     */
    public function declineJoin(Group $group, Member $member)
    {
        $admin = $this->getUser();
        if (!$group->isAdmin($admin)) {
            throw $this->createAccessDeniedException('No group admin');
        }

        $this->groupModel->declineJoin($group, $member, $admin);

        $this->addTranslatedFlash('notice', 'flash.group.declined.join', [
            'name' => $group->getName(),
            'username' => $member->getUsername(),
        ]);
    }

    /**
     * @Route("/group/{groupId}/invite/{memberId}", name="invite_member_to_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @param Group $group
     * @param Member $member
     *
     * @return JsonResponse
     * @throws AccessDeniedException
     */
    public function inviteMemberToGroup(Group $group, Member $member)
    {
        // Check if current user is admin of given group
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
     * @param Group $group
     * @param Member $member
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

        return $this->redirectToRoute('group_start', [ 'group_id' => $group->getId()]);
    }

    /**
     * @Route("/group/{groupId}/decline/{memberId}", name="decline_invite_to_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @param Group $group
     * @param Member $member
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

        return $this->redirectToRoute('group_start', [ 'group_id' => $group->getId()]);
    }

    /**
     * @param Group $group
     * @param Member $member
     * @param Member[] $admins
     */
    private function sendAdminNotification(Group $group, Member $member, Member ...$admins)
    {
        // \todo
        $group;
        $member;
        $admins;
    }

    /**
     * @Route("/group/{groupId}/withdraw/{memberId}", name="withdraw_member_invite_to_group")
     *
     * @ParamConverter("group", class="App\Entity\Group", options={"id" = "groupId"})
     * @ParamConverter("member", class="App\Entity\Member", options={"id" = "memberId"})
     *
     * @param Request $request
     * @param Group $group
     * @param Member $member
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse
     */
    public function withdrawInviteMemberGroup(Request $request, Group $group, Member $member, TranslatorInterface $translator)
    {
        $success = $this->groupModel->withdrawInviteMemberToGroup($group, $member);

        if ($success) {
            $this->addFlash('notice', $translator->trans('flash.group.invite.withdrawn'));
        } else {
            $this->addFlash('error', $translator->trans('flash.group.invite.withdrawn.error'));
        }
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    /**
     * @Route("/new/group", name="new_group")
     *
     * @param Request             $request
     * @param TranslatorInterface $translator
     * @param Logger              $logger
     *
     * @throws Exception
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * Because of the mix between old code and new code this method is way too long.
     */
    public function createNewGroup(Request $request, TranslatorInterface $translator, Logger $logger)
    {
        $groupRequest = new GroupRequest();
        $form = $this->createForm(GroupType::class, $groupRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $member = $this->getUser();

            $groupPicture = $this->handleGroupPicture($data->picture);

            $group = $this->groupModel->new($data, $request->getLocale(), $member, $groupPicture);

            $this->addTranslatedFlash('notice', 'flash.group.create.successful', [
                '%name%' => $group->getName(),
            ]);

            $logger->write('Group '.$group->getName().' created by '.$member->getUsername().'.', 'Group');

            return $this->redirectToRoute('groups_overview');
        }

        return $this->render('group/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new/group/check", name="new_group_check")
     *
     * @param Request $request
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
     * @param Group $group
     *
     * @param WikiModel $wikiModel
     * @return Response
     */
    public function showGroupWikiPage(Group $group, WikiModel $wikiModel)
    {
        $member = $this->getUser();

        $pageName = $wikiModel->getPageName('Group_'.$group->getName());

        $em = $this->getDoctrine();
        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $em->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName);

        if (null === $wikiPage) {
            $output = 'No wiki found for this group.';
        } else {
            $output = $wikiModel->parseWikiMarkup($wikiPage->getContent());
        }

        return $this->render('group/wiki.html.twig', [
            'title' => 'Group '.$group->getName(),
            'submenu' => [
                'active' => 'wiki',
                'items' => $this->getSubmenuItems($member, $group),
            ],
            'wikipage' => $output,
        ]);
    }

    /**
     * @param Member $member
     * @param Group  $group
     *
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
            $fileName = $this->generateUniqueFileName().'.'.$picture->guessExtension();

            // moves the file to the directory where group images are stored
            $picture->move(
                $groupImageDir,
                $fileName
            );
            $img = Image::make($groupImageDir.'/'.$fileName);
            $img->resize(80, 80, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($groupImageDir.'/thumb'.$fileName);

            return $fileName;
        }

        return null;
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    private function informGroupAdmins(Group $group, $member)
    {
        $admins = $group->getAdmins();

        if (!empty($admins)) {
            foreach($admins as $admin)
            {
                $this->sendTemplateEmail('group@bewelcome.org', $admin, 'group.approve.join', [
                    'member' => $member
                ]);
            }
        }
    }
}
