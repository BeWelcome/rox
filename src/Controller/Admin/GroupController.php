<?php

namespace App\Controller\Admin;

use App\Entity\Group;
use App\Entity\GroupCategory;
use App\Entity\Member;
use App\Form\Admin\GroupCategoryNameType;
use App\Logger\Logger;
use App\Repository\GroupCategoryRepository;
use App\Service\Mailer;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\RedirectBackTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class GroupController.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class GroupController extends AbstractController
{
    use ItemsPerPageTraits;
    use RedirectBackTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(private Mailer $mailer, private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Allows to set a status for group creation requests.
     *
     * @throws AccessDeniedException
     */
    #[Route(path: '/admin/groups/approval', name: 'admin_groups_approval')]
    public function approveGroups(): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.1');
        }

        if (!$this->hasGroupRightLevel(10)) {
            /** @var Member $admin */
            $admin = $this->getUser();
            $level = $admin->getLevelForRight(Member::ROLE_ADMIN_GROUP);
            throw $this->createAccessDeniedException('You need to have level 10 to access this.' . $level);
        }

        // Fetch unapproved groups and decide on their fate
        // no pagination as there shouldn't be too many
        $groupsRepository = $this->entityManager->getRepository(Group::class);
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
     * @throws AccessDeniedException
     */
    #[Route(path: '/admin/groups/archival', name: 'admin_groups_archival')]
    public function archiveGroups(#[MapQueryParameter] int $currentPage = 1): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        // Build Pagerfanta for groups
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('g')
            ->from(Group::class, 'g')
            ->where("g.name NOT LIKE '[Archived] %'")
        ;

        /** @var Member $user */
        $user = $this->getUser();
        $maxPerPage = $this->getItemsPerPage($user);

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($currentPage);

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
     * @throws AccessDeniedException
     */
    #[Route(path: '/admin/groups/unarchival', name: 'admin_groups_unarchival')]
    public function unarchiveGroups(#[MapQueryParameter] int $currentPage = 1): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        // Build Pagerfanta for groups
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('g')
            ->from(Group::class, 'g')
            ->where("g.name LIKE '[Archived] %'")
        ;

        /** @var Member $user */
        $user = $this->getUser();
        $maxPerPage = $this->getItemsPerPage($user);

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($currentPage);

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
     * @throws AccessDeniedException|Exception
     */
    #[Route(path: '/admin/groups/{id}/discuss', name: 'admin_groups_discuss')]
    public function discussGroup(Request $request, Group $group, Logger $logger): RedirectResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setApproved(Group::IN_DISCUSSION);

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.group.moved.discussion', [
            '%name%' => $group->getName(),
        ]);

        /** @var Member $groupAdmin */
        $groupAdmin = $this->getUser();
        $logger->write(
            "Group {$this->getGroupLinkTag($group)} moved into discussion by {$groupAdmin->getUsername()}.",
            'Group'
        );

        return $this->redirectBack($request, 'admin_groups_approval');
    }

    /**
     * Dismiss a group creation requests.
     *
     * @throws AccessDeniedException
     */
    #[Route(path: '/admin/groups/{id}/dismiss', name: 'admin_groups_dismiss')]
    public function dismissGroup(Request $request, Group $group, Logger $logger): RedirectResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setApproved(Group::DISMISSED);

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.group.dismissed', [
            '%name%' => $group->getName(),
        ]);

        /** @var Member $groupAdmin */
        $groupAdmin = $this->getUser();
        $logger->write("Group {$this->getGroupLinkTag($group)} dismissed by {$groupAdmin->getUsername()}", 'Group');

        return $this->redirectBack($request, 'admin_groups_approval');
    }

    /**
     * Approve a group creation requests.
     *
     * @throws AccessDeniedException
     */
    #[Route(path: '/admin/groups/{id}/approve', name: 'admin_groups_approve')]
    public function approveGroup(Request $request, Group $group, Logger $logger): RedirectResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have the Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setApproved(Group::APPROVED);

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.group.approved', [
            '%name%' => $group->getName(),
        ]);

        /** @var Member $groupAdmin */
        $groupAdmin = $this->getUser();
        $logger->write("Group {$this->getGroupLinkTag($group)} approved by {$groupAdmin->getUsername()}.", 'Group');

        /** @var Member $creator */
        $creator = current($group->getMembers());
        $this->sendNewGroupApprovedNotification($group, $creator);

        return $this->redirectBack($request, 'admin_groups_approval');
    }

    /**
     * Archive a group .
     *
     * @throws AccessDeniedException
     */
    #[Route(path: '/admin/groups/{id}/archive', name: 'admin_groups_archive')]
    public function archiveGroup(Request $request, Group $group, Logger $logger): RedirectResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setName('[Archived] ' . $group->getName());

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.group.archived', [
            '%name%' => $group->getName(),
        ]);

        /** @var Member $groupAdmin */
        $groupAdmin = $this->getUser();
        $logger->write("Group {$this->getGroupLinkTag($group)} archived by {$groupAdmin->getUsername()}.", 'Group');

        return $this->redirectBack($request, 'admin_groups_approval');
    }

    /**
     * Un-archive a group .
     *
     * @throws AccessDeniedException
     */
    #[Route(path: '/admin/groups/{id}/unarchive', name: 'admin_groups_unarchive')]
    public function unarchiveGroup(Request $request, Group $group, Logger $logger): RedirectResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $group->setName(str_replace('[Archived] ', '', $group->getName()));

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.group.unarchived', [
            '%name%' => $group->getName(),
        ]);

        /** @var Member $groupAdmin */
        $groupAdmin = $this->getUser();
        $logger->write("Group {$this->getGroupLinkTag($group)} un-archived by {$groupAdmin->getUsername()}.", 'Group');

        return $this->redirectBack($request, 'admin_groups_approval');
    }

    /**
     * Rename a group .
     *
     * @throws AccessDeniedException
     */
    #[Route(path: '/admin/groups/rename', name: 'admin_groups_rename')]
    public function renameGroup(Request $request, Logger $logger): Response|RedirectResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        if (!$this->hasGroupRightLevel(10)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }

        $groupForm = $this->createFormBuilder()
            ->add('group', EntityType::class, [
                'attr' => [
                    'class' => 'select2',
                ],
                'class' => Group::class,
                'query_builder' => static function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->where('g.approved = 1')
                        ->orderBy('g.name', 'ASC');
                },
                'choice_label' => 'name',
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
            $data = $groupForm->getData();
            $group = $data['group'];
            if (null === $group) {
                $groupForm->addError(new FormError($this->getTranslator()->trans('admin.group.not.found')));
            } else {
                $oldName = $group->getName();
                $group->setName($data['new_name']);

                $this->entityManager->persist($group);
                $this->entityManager->flush();

                $this->addTranslatedFlash('notice', 'admin.group.renamed', [
                    'oldName' => $oldName,
                    'newName' => $data['new_name'],
                ]);

                /** @var Member $groupAdmin */
                $groupAdmin = $this->getUser();
                $logger->write("Group {$oldName} renamed to {$this->getGroupLinkTag($group)} by "
                    . $groupAdmin->getUsername() . '.', 'Group');

                return $this->redirectToRoute('admin_groups_approval');
            }
        }

        return $this->render('admin/groups/rename.html.twig', [
            'form' => $groupForm->createView(),
        ]);
    }

    /**
     * The list of group categories, with a form to add one (by its English name).
     */
    #[Route(path: '/admin/groups/categories', name: 'admin_groups_categories')]
    #[IsGranted(Member::ROLE_ADMIN_GROUP)]
    public function groupCategories(Request $request, Logger $logger): Response
    {
        $form = $this->createForm(GroupCategoryNameType::class, null, ['new' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = new GroupCategory()->setName('en', $form->get('name')->getData());
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addTranslatedFlash('notice', 'admin.group.category.saved');
            $logger->write("Group category #{$category->getId()} ({$category->getName()}) added", 'Group');

            return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId()]);
        }

        /** @var GroupCategoryRepository $repository */
        $repository = $this->entityManager->getRepository(GroupCategory::class);

        return $this->render('admin/groups/categories.html.twig', [
            'categories' => $repository->findAllSortedByName($request->getLocale()),
            'groupCounts' => $repository->countGroups(),
            'form' => $form,
            'submenu' => [
                'items' => $this->getSubmenuItems(),
                'active' => 'categories',
            ],
        ]);
    }

    /**
     * A group category's names: set one per language; an empty name removes the language (but not English).
     */
    #[Route(path: '/admin/groups/categories/{id}', name: 'admin_groups_category', requirements: ['id' => '\d+'])]
    #[IsGranted(Member::ROLE_ADMIN_GROUP)]
    public function groupCategory(
        Request $request,
        GroupCategory $category,
        Logger $logger,
        #[Autowire('%locales%')]
        array $locales,
    ): Response {
        $form = $this->createForm(GroupCategoryNameType::class, ['locale' => $request->getLocale()], [
            'locales' => $locales,
            'display_locale' => $request->getLocale(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $locale = $form->get('locale')->getData();
            $name = (string) $form->get('name')->getData();
            if ('en' === $locale && '' === trim($name)) {
                $form->get('name')->addError(new FormError($this->getTranslator()->trans('admin.group.category.english')));
            } else {
                $category->setName($locale, $name);
                $this->entityManager->flush();

                $this->addTranslatedFlash('notice', 'admin.group.category.saved');
                $logger->write("Group category #{$category->getId()} ({$category->getName()}): name in {$locale} set", 'Group');

                return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId()]);
            }
        }

        return $this->render('admin/groups/category.html.twig', [
            'category' => $category,
            'form' => $form,
            'submenu' => [
                'items' => $this->getSubmenuItems(),
                'active' => 'categories',
            ],
        ]);
    }

    /**
     * Deletes a group category; the groups in it just lose it.
     */
    #[Route(path: '/admin/groups/categories/{id}/delete', name: 'admin_groups_category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(Member::ROLE_ADMIN_GROUP)]
    public function deleteGroupCategory(Request $request, GroupCategory $category, Logger $logger): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete-group-category-' . $category->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $logger->write("Group category #{$category->getId()} ({$category->getName()}) deleted", 'Group');
        $this->entityManager->remove($category);
        $this->entityManager->flush();
        $this->addTranslatedFlash('notice', 'admin.group.category.deleted');

        return $this->redirectToRoute('admin_groups_categories');
    }

    private function sendNewGroupApprovedNotification(Group $group, Member $creator): void
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

    private function hasGroupRightLevel(int $level): bool
    {
        /** @var Member $admin */
        $admin = $this->getUser();

        return $admin->getLevelForRight(Member::ROLE_ADMIN_GROUP) === $level;
    }

    private function getSubMenuItems(): array
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
            'categories' => [
                'key' => 'admin.groups.categories',
                'url' => $this->generateUrl('admin_groups_categories'),
            ],
            'logs' => [
                'key' => 'admin.groups.logs',
                'url' => $this->generateUrl('admin_groups_logs'),
            ],
        ];
    }

    private function getGroupLinkTag(Group $group): string
    {
        return '<a href="' . $this->generateUrl('group_start', ['group_id' => $group->getId()]) . '">'
            . $group->getName() . '</a>';
    }
}
