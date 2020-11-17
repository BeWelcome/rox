<?php

namespace App\Controller\Admin;

use App\Entity\Group;
use App\Entity\Member;
use App\Logger\Logger;
use App\Service\Mailer;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class GroupController.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupController extends AbstractController
{
    use TranslatorTrait;
    use TranslatedFlashTrait;

    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

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
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        // Fetch unapproved groups and decide on their fate
        // no pagination as there shouldn't be too many
        $groupsRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupsRepository->findBy([
            'approved' => [Group::NOT_APPROVED, Group::IN_DISCUSSION],
        ]);

        return $this->render('admin/groups/approve.html.twig', [
            'groups' => $groups,
            'submenu' => [
                'items' => $this->getSubmenuItems(),
                'active' => 'approval',
            ],
        ]);
    }

    /**
     * Allows to archive a group.
     *
     * @Route("/admin/groups/archival", name="admin_groups_archival")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function archiveGroups(Request $request)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        // Build Pagerfanta for groups
        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('g')
            ->from('App:Group', 'g')
            ->where("g.name NOT LIKE '[Archived] %'")
        ;
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(30); // 10 by default

        $currentPage = $request->get('page', '1');
        $pagerfanta->setCurrentPage($currentPage); // 1 by default

        return $this->render('admin/groups/archive.html.twig', [
            'groups' => $pagerfanta,
            'submenu' => [
                'items' => $this->getSubmenuItems(),
                'active' => 'archival',
            ],
        ]);
    }

    /**
     * Allows to unarchive a group.
     *
     * @Route("/admin/groups/unarchival", name="admin_groups_unarchival")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function unarchiveGroups(Request $request)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        // Build Pagerfanta for groups
        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('g')
            ->from('App:Group', 'g')
            ->where("g.name LIKE '[Archived] %'")
        ;
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(30); // 10 by default

        $currentPage = $request->get('page', '1');
        $pagerfanta->setCurrentPage($currentPage); // 1 by default

        return $this->render('admin/groups/archive.html.twig', [
            'groups' => $pagerfanta,
            'submenu' => [
                'items' => $this->getSubmenuItems(),
                'active' => 'unarchival',
            ],
        ]);
    }

    /**
     * Move a group creation requests to the discussion queue.
     *
     * @Route("/admin/groups/{id}/discuss", name="admin_groups_discuss")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function discussGroup(Request $request, Group $group, Logger $logger)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setApproved(Group::IN_DISCUSSION);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.group.moved.discussion', [
            '%name%' => $group->getName(),
        ]);

        $logger->write(
            'Group ' . $this->getGroupLinkTag($group) . ' moved into discussion by ' . $this->getUser()->getUsername() . '.',
            'Group'
        );

        $referrer = $request->headers->get('referer');
        if (null === $referrer) {
            return $this->redirectToRoute('admin_groups_approval');
        }

        return $this->redirect($referrer);
    }

    /**
     * Dismiss a group creation requests.
     *
     * @Route("/admin/groups/{id}/dismiss", name="admin_groups_dismiss")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function dismissGroup(Request $request, Group $group, Logger $logger)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setApproved(Group::DISMISSED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.group.dismissed', [
            '%name%' => $group->getName(),
        ]);

        $logger->write('Group ' . $this->getGroupLinkTag($group) . ' dismissed by ' . $this->getUser()->getUsername() . '.', 'Group');

        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * Approve a group creation requests.
     *
     * @Route("/admin/groups/{id}/approve", name="admin_groups_approve")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function approveGroup(Request $request, Group $group, Logger $logger)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have the Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setApproved(Group::APPROVED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.group.approved', [
            '%name%' => $group->getName(),
        ]);

        $logger->write('Group ' . $this->getGroupLinkTag($group) . ' approved by ' . $this->getUser()->getUsername() . '.', 'Group');

        $creator = current($group->getMembers());
        $this->sendNewGroupApprovedNotification($group, $creator);
        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * Archive a group .
     *
     * @Route("/admin/groups/{id}/archive", name="admin_groups_archive")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function archiveGroup(Request $request, Group $group, Logger $logger)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setName('[Archived] ' . $group->getName());
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.group.archived', [
            '%name%' => $group->getName(),
        ]);

        $logger->write('Group ' . $this->getGroupLinkTag($group) . ' archived by ' . $this->getUser()->getUsername() . '.', 'Group');

        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * Un-archive a group .
     *
     * @Route("/admin/groups/{id}/unarchive", name="admin_groups_unarchive")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function unarchiveGroup(Request $request, Group $group, Logger $logger)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setName(str_replace('[Archived] ', '', $group->getName()));
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.group.unarchived', [
            '%name%' => $group->getName(),
        ]);

        $logger->write('Group ' . $this->getGroupLinkTag($group) . ' un-archived by ' . $this->getUser()->getUsername() . '.', 'Group');

        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * Rename a group .
     *
     * @Route("/admin/groups/rename", name="admin_groups_rename")
     *
     * @throws AccessDeniedException
     *
     * @return Response|RedirectResponse
     */
    public function renameGroup(Request $request, Logger $logger)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $groupForm = $this->createFormBuilder()
            ->add('old_name', TextType::class, [
                'label' => 'admin.group.old.name',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('new_name', TextType::class, [
                'label' => 'admin.group.new.name',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('submit', SubmitType::class)
            ->getForm();
        $groupForm->handleRequest($request);
        if ($groupForm->isSubmitted() && $groupForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $groupForm->getData();
            $groupRepository = $em->getRepository(Group::class);
            /** @var Group $group */
            $group = $groupRepository->findOneBy(['name' => $data['old_name']]);
            if (null === $group) {
                $groupForm->addError(new FormError($this->getTranslator()->trans('admin.group.not.found')));
            } else {
                $group->setName($data['new_name']);
                $em->persist($group);
                $em->flush($group);
                $this->addTranslatedFlash('notice', 'admin.group.renamed', [
                    'oldName' => $data['old_name'],
                    'newName' => $data['new_name'],
                ]);

                $logger->write('Group ' . $data['old_name'] . ' renamed to ' . $this->getGroupLinkTag($group)
                    . ' by ' . $this->getUser()->getUsername() . '.', 'Group');

                return $this->redirectToRoute('admin_groups_approval');
            }
        }

        return $this->render('admin/groups/rename.html.twig', [
            'form' => $groupForm->createView(),
        ]);
    }

    private function sendNewGroupApprovedNotification(Group $group, Member $creator)
    {
        $subject = $this->translator->trans(// 'email.subject.group.approved'
            '[New Group] %group% approved',
            ['%group%' => strip_tags($group->getName())]
        );
        $this->mailer->sendGroupEmail($creator, 'group/approved', [
            'subject' => $subject,
            'group' => $group,
            'creator' => $creator,
            'admin' => $this->getUser(),
        ]);
    }

    private function hasGroupRightLevel(int $level)
    {
        /** @var Member $admin */
        $admin = $this->getUser();

        return $admin->getLevelForRight(Member::ROLE_ADMIN_GROUP) === $level;
    }

    /**
     * @return array
     */
    private function getSubMenuItems()
    {
        return [
            'approval' => [
                'key' => 'admin.groups.approval',
                'url' => $this->generateUrl('admin_groups_approval'),
            ],
            'archival' => [
                'key' => 'admin.groups.archival',
                'url' => $this->generateUrl('admin_groups_archival'),
            ],
            'unarchival' => [
                'key' => 'admin.groups.unarchival',
                'url' => $this->generateUrl('admin_groups_unarchival'),
            ],
            'rename' => [
                'key' => 'admin.groups.rename',
                'url' => $this->generateUrl('admin_groups_rename'),
            ],
            'logs' => [
                'key' => 'admin.groups.logs',
                'url' => $this->generateUrl('admin_groups_logs'),
            ],
        ];
    }

    private function getGroupLinkTag(Group $group)
    {
        return '<a href="' . $this->generateUrl('group_start', ['group_id' => $group->getId()]) . '">' . $group->getName() . '</a>';
    }
}
