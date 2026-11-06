<?php

namespace App\Controller\Admin;

use App\Entity\Group;
use App\Entity\Member;
use App\Logger\Logger;
use App\Repository\GroupRepository;
use App\Service\Mailer;
use App\Utilities\GroupAdminSubmenuTrait;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\RedirectBackTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * The group admin team's pages: the new groups waiting for approval (approve, discuss, dismiss), archiving and
 * unarchiving a group (its name gets or loses "[Archived] ") and renaming one. Needs the Group right at level 10.
 * The actions are POSTs with a CSRF token and go back to the page they came from.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class GroupController extends AbstractController
{
    use GroupAdminSubmenuTrait;
    use ItemsPerPageTraits;
    use RedirectBackTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public const string TOKEN = 'admin-groups';

    private const string ARCHIVED = '[Archived] ';
    private const int NAME_LENGTH = 100;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly EntityManagerInterface $entityManager,
        private readonly Logger $logger,
    ) {
    }

    /**
     * The new groups waiting for a decision, the ones under discussion first; no paging, there are only a few.
     */
    #[Route(path: '/admin/groups/approval', name: 'admin_groups_approval', methods: ['GET'])]
    public function approveGroups(): Response
    {
        $this->denyUnlessGroupAdmin();

        $groups = $this->getGroupRepository()->findBy(
            ['approved' => [Group::NOT_APPROVED, Group::IN_DISCUSSION]],
            ['approved' => 'DESC', 'created' => 'ASC'],
        );

        return $this->render('admin/groups/approve.html.twig', [
            'groups' => $groups,
            'submenu' => $this->getGroupAdminSubmenu('approval'),
        ]);
    }

    #[Route(path: '/admin/groups/archival', name: 'admin_groups_archival', methods: ['GET'])]
    public function archiveGroups(Request $request): Response
    {
        return $this->showArchive($request, false);
    }

    #[Route(path: '/admin/groups/unarchival', name: 'admin_groups_unarchival', methods: ['GET'])]
    public function unarchiveGroups(Request $request): Response
    {
        return $this->showArchive($request, true);
    }

    #[Route(path: '/admin/groups/{id}/discuss', name: 'admin_groups_discuss', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function discussGroup(Request $request, Group $group): RedirectResponse
    {
        return $this->decide($request, $group, Group::IN_DISCUSSION, 'flash.group.moved.discussion', 'moved into discussion');
    }

    #[Route(path: '/admin/groups/{id}/dismiss', name: 'admin_groups_dismiss', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function dismissGroup(Request $request, Group $group): RedirectResponse
    {
        return $this->decide($request, $group, Group::DISMISSED, 'flash.group.dismissed', 'dismissed');
    }

    /**
     * Approves the group and tells its creator.
     */
    #[Route(path: '/admin/groups/{id}/approve', name: 'admin_groups_approve', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function approveGroup(Request $request, Group $group): RedirectResponse
    {
        $response = $this->decide($request, $group, Group::APPROVED, 'flash.group.approved', 'approved');

        $creator = current($group->getMembers());
        if ($creator instanceof Member) {
            $this->sendNewGroupApprovedNotification($group, $creator);
        }

        return $response;
    }

    #[Route(path: '/admin/groups/{id}/archive', name: 'admin_groups_archive', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function archiveGroup(Request $request, Group $group): RedirectResponse
    {
        $this->denyUnlessGroupAdmin();

        if (!str_starts_with($group->getName(), self::ARCHIVED)) {
            $group->setName(mb_substr(self::ARCHIVED . $group->getName(), 0, self::NAME_LENGTH));
            $this->entityManager->flush();
            $this->log($group, 'archived');
        }
        $this->addTranslatedFlash('notice', 'admin.groups.archived', ['name' => $group->getName()]);

        return $this->redirectBack($request, 'admin_groups_archival');
    }

    #[Route(path: '/admin/groups/{id}/unarchive', name: 'admin_groups_unarchive', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function unarchiveGroup(Request $request, Group $group): RedirectResponse
    {
        $this->denyUnlessGroupAdmin();

        if (str_starts_with($group->getName(), self::ARCHIVED)) {
            $group->setName(substr($group->getName(), \strlen(self::ARCHIVED)));
            $this->entityManager->flush();
            $this->log($group, 'un-archived');
        }
        $this->addTranslatedFlash('notice', 'admin.groups.unarchived', ['name' => $group->getName()]);

        return $this->redirectBack($request, 'admin_groups_unarchival');
    }

    /**
     * Renames an approved group, picked by its current name.
     */
    #[Route(path: '/admin/groups/rename', name: 'admin_groups_rename', methods: ['GET', 'POST'])]
    public function renameGroup(Request $request): Response
    {
        $this->denyUnlessGroupAdmin();

        $groupForm = $this->createFormBuilder()
            ->add('group', EntityType::class, [
                'attr' => [
                    'class' => 'js-tom-select',
                    'data-no-results-text' => $this->getTranslator()->trans('admin.group.not.found'),
                ],
                'class' => Group::class,
                'query_builder' => static fn (EntityRepository $repository) => $repository->createQueryBuilder('g')
                    ->where('g.approved = :approved')
                    ->setParameter('approved', Group::APPROVED)
                    ->orderBy('g.name', 'ASC'),
                'choice_label' => 'name',
                'placeholder' => 'admin.groups.rename.pick',
                'label' => 'admin.group.old.name',
                'required' => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('new_name', TextType::class, [
                'label' => 'admin.group.new.name',
                'required' => true,
                'attr' => ['maxlength' => self::NAME_LENGTH],
                'constraints' => [new NotBlank(), new Length(max: self::NAME_LENGTH)],
            ])
            ->add('submit', SubmitType::class, ['label' => 'admin.groups.rename.submit'])
            ->getForm();

        $groupForm->handleRequest($request);
        if ($groupForm->isSubmitted() && $groupForm->isValid()) {
            /** @var Group $group */
            $group = $groupForm->get('group')->getData();
            $newName = trim((string) $groupForm->get('new_name')->getData());
            $oldName = $group->getName();
            $group->setName($newName);
            $this->entityManager->flush();

            $this->addTranslatedFlash('notice', 'admin.group.renamed', [
                'oldName' => $oldName,
                'newName' => $newName,
            ]);
            $this->logger->write("Group {$oldName} renamed to {$this->getGroupLinkTag($group)} by {$this->getAdmin()->getUsername()}.", 'Group');

            return $this->redirectToRoute('admin_groups_rename');
        }

        return $this->render('admin/groups/rename.html.twig', [
            'form' => $groupForm->createView(),
            'submenu' => $this->getGroupAdminSubmenu('rename'),
        ]);
    }

    /**
     * The groups to archive (archived false) or the archived ones to unarchive, by name, filtered by a part of it.
     */
    private function showArchive(Request $request, bool $archived): Response
    {
        $this->denyUnlessGroupAdmin();

        $search = trim($request->query->getString('q'));
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('g')
            ->from(Group::class, 'g')
            ->where($archived ? 'g.name LIKE :archived' : 'g.name NOT LIKE :archived')
            ->setParameter('archived', self::ARCHIVED . '%')
            // approved groups, also the closed ones; not the requests or the dismissed ones
            ->andWhere('g.approved IN (:approved)')
            ->setParameter('approved', [Group::APPROVED, Group::APPROVED_CLOSED])
            ->orderBy('g.name', 'ASC');
        if ('' !== $search) {
            $queryBuilder->andWhere('g.name LIKE :search')->setParameter('search', '%' . addcslashes($search, '%_') . '%');
        }

        $groups = new Pagerfanta(new QueryAdapter($queryBuilder));
        $groups->setMaxPerPage($this->getItemsPerPage($this->getAdmin()));
        try {
            $groups->setCurrentPage(max(1, $request->query->getInt('page', 1)));
        } catch (OutOfRangeCurrentPageException) {
            $groups->setCurrentPage(max(1, $groups->getNbPages()));
        }
        $onPage = iterator_to_array($groups->getCurrentPageResults(), false);

        return $this->render('admin/groups/archive.html.twig', [
            'groups' => $groups,
            'archived' => $archived,
            'search' => $search,
            'member_counts' => $this->getGroupRepository()->countMembersOfGroups($onPage),
            'submenu' => $this->getGroupAdminSubmenu($archived ? 'unarchival' : 'archival'),
        ]);
    }

    private function decide(Request $request, Group $group, int $decision, string $flash, string $logText): RedirectResponse
    {
        $this->denyUnlessGroupAdmin();

        $group->setApproved($decision);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', $flash, ['%name%' => $group->getName()]);
        $this->log($group, $logText);

        return $this->redirectBack($request, 'admin_groups_approval');
    }

    private function log(Group $group, string $what): void
    {
        $this->logger->write("Group {$this->getGroupLinkTag($group)} {$what} by {$this->getAdmin()->getUsername()}.", 'Group');
    }

    private function sendNewGroupApprovedNotification(Group $group, Member $creator): void
    {
        $subject = $this->getTranslator()->trans(// 'email.subject.group.approved'
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

    private function denyUnlessGroupAdmin(): void
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have the Group right to access this.');
        }
        if (10 !== $this->getAdmin()->getLevelForRight(Member::ROLE_ADMIN_GROUP)) {
            throw $this->createAccessDeniedException('You need to have level 10 to access this.');
        }
    }

    private function getAdmin(): Member
    {
        /** @var Member $admin */
        $admin = $this->getUser();

        return $admin;
    }

    private function getGroupRepository(): GroupRepository
    {
        /** @var GroupRepository $repository */
        $repository = $this->entityManager->getRepository(Group::class);

        return $repository;
    }

    private function getGroupLinkTag(Group $group): string
    {
        return '<a href="' . $this->generateUrl('group_start', ['group_id' => $group->getId()]) . '">'
            . htmlspecialchars($group->getName()) . '</a>';
    }
}
