<?php

namespace App\Controller\Admin;

use App\Entity\Group;
use App\Entity\Member;
use App\Entity\Right;
use App\Logger\Logger;
use App\Utilities\MailerTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class GroupController.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupController extends AbstractController
{
    use MailerTrait;
    use TranslatorTrait;
    use TranslatedFlashTrait;

    /**
     * Allows to set a status for group creation requests.
     *
     * @Route("/admin/groups/approval", name="admin_groups_approval")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function approveGroups()
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_GROUP])) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        // Fetch unapproved groups and decide on their fate
        $groupsRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupsRepository->findBy([
            'approved' => [Group::NOT_APPROVED, Group::IN_DISCUSSION],
        ]);

        return $this->render('admin/groups/approve.html.twig', [
            'groups' => $groups,
        ]);
    }

    /**
     * Move a group creation requests to the discussion queue.
     *
     * @Route("/admin/groups/{id}/discuss", name="admin_groups_discuss")
     *
     * @param Request $request
     * @param Group   $group
     * @param Logger  $logger
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function discussGroup(Request $request, Group $group, Logger $logger)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_GROUP])) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $group->setApproved(Group::IN_DISCUSSION);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.group.moved.discussion', [
            '%name%' => $group->getName(),
        ]);

        $logger->write('Group '.$group->getName().' moved into discussion by '.$this->getUser()->getUsername().'.', 'Group');

        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * Dismiss a group creation requests.
     *
     * @Route("/admin/groups/{id}/dismiss", name="admin_groups_dismiss")
     *
     * @param Request $request
     * @param Group   $group
     * @param Logger  $logger
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function dismissGroup(Request $request, Group $group, Logger $logger)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_GROUP])) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $group->setApproved(Group::DISMISSED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.group.dismissed', [
            '%name%' => $group->getName(),
        ]);

        $logger->write('Group '.$group->getName().' dismissed by '.$this->getUser()->getUsername().'.', 'Group');

        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * Approve a group creation requests.
     *
     * @Route("/admin/groups/{id}/approve", name="admin_groups_approve")
     *
     * @param Request $request
     * @param Group   $group
     * @param Logger  $logger
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function approveGroup(Request $request, Group $group, Logger $logger)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_GROUP])) {
            throw $this->createAccessDeniedException('You need to have the Group right to access this.');
        }

        $group->setApproved(Group::APPROVED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.group.approved', [
            '%name%' => $group->getName(),
        ]);

        $logger->write('Group '.$group->getName().' approved by '.$this->getUser()->getUsername().'.', 'Group');

        $creator = current($group->getMembers());
        $this->sendNewGroupApprovedNotification($group, $creator);
        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    private function sendNewGroupApprovedNotification(Group $group, Member $creator)
    {
        $subject = '[New Group] '.strip_tags($group->getName()).' approved';
        $this->sendTemplateEmail('group@bewelcome.org', $creator, 'group.approved', [
            'subject' => $subject,
            'group' => $group,
            'creator' => $creator,
        ]);
    }

    private function hasGroupRightLevel(int $level)
    {
        $admin = $this->getUser();
        return ($admin->getLevelForRight(Member::ROLE_ADMIN_GROUP) == $level);
    }
}
