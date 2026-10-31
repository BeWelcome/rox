<?php

namespace App\Controller;

use App\Doctrine\GroupMembershipStatusType;
use App\Dto\GroupSearch;
use App\Entity\Group;
use App\Entity\GroupCategory;
use App\Entity\GroupMembership;
use App\Entity\Member;
use App\Entity\RelatedGroup;
use App\Repository\GroupCategoryRepository;
use App\Repository\GroupRepository;
use App\Security\GroupVoter;
use App\Security\MemberVoter;
use App\Utilities\GroupSubmenuTrait;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The pages that show groups: the search, the member's groups, a group's page and its members, and the group
 * pictures. The URLs are the ones the legacy group pages had.
 */
class GroupPagesController extends AbstractController
{
    use GroupSubmenuTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private const int GROUPS_PER_PAGE = 30;
    private const int MEMBERS_PER_PAGE = 20;
    private const int LATEST_THREADS = 10;
    private const int SIDEBAR_MEMBERS = 12;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/groups/search', name: 'groups_search', methods: ['GET'])]
    public function search(Request $request, #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] GroupSearch $search = new GroupSearch()): Response
    {
        $groups = $this->paginate($this->getGroupRepository()->createSearchQueryBuilder($search), $search->page, self::GROUPS_PER_PAGE);

        return $this->render('group/search.html.twig', $this->getListParameters($request, $search) + [
            'groups' => $groups,
        ]);
    }

    /**
     * The legacy list of all groups; the search without filters shows the same.
     */
    #[Route(path: '/groups/featured', name: 'groups_featured', methods: ['GET'])]
    public function featured(): RedirectResponse
    {
        return $this->redirectToRoute('groups_search', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route(path: '/groups/mygroups', name: 'groups_mygroups', methods: ['GET', 'POST'])]
    public function myGroups(Request $request): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $page = max(1, $request->query->getInt('page', 1));

        $profileForm = $this->createProfileGroupsForm($member);
        $profileForm->handleRequest($request);
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $shown = array_map('intval', $profileForm->get('groups')->getData());
            foreach ($this->getCurrentMemberships($member) as $membership) {
                $membership->setShownOnProfile(\in_array($membership->getGroup()->getId(), $shown, true));
            }
            $this->entityManager->flush();
            $this->addTranslatedFlash('notice', 'group.profile.groups.saved');

            return $this->redirectToRoute('groups_mygroups');
        }

        // Newest post first unless another order is asked for
        $order = (string) $request->query->get('order', 'actdesc');
        $order = \in_array($order, GroupSearch::ORDERS, true) ? $order : 'actdesc';
        $groups = $this->paginate($this->getGroupRepository()->createMyGroupsQueryBuilder($member, $order), $page, self::GROUPS_PER_PAGE);

        return $this->render('group/mygroups.html.twig', $this->getListParameters($request, new GroupSearch(order: $order)) + [
            'groups' => $groups,
            'profileForm' => $profileForm,
        ]);
    }

    /**
     * The groups a member shows on their profile, in the profile layout: a card per group with its members and
     * last post, Joined on the ones the viewer is in. On their own page a button leads to the groups to choose.
     */
    #[Route(path: '/members/{username}/groups', name: 'group_user_all', methods: ['GET'])]
    public function memberGroups(
        #[MapEntity(mapping: ['username' => 'username'])] Member $member,
        ProfileSubmenu $profileSubmenu,
    ): Response {
        $this->denyAccessUnlessGranted(MemberVoter::MEMBER_VIEW, $member);
        /** @var Member $viewer */
        $viewer = $this->getUser();
        $ids = array_map(static fn (Group $group): int => (int) $group->getId(), $member->getProfileGroups());
        $rows = [] === $ids ? [] : $this->getGroupRepository()->createMyGroupsQueryBuilder($member, 'nameasc')
            ->andWhere('g.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
        $own = $viewer === $member;
        $myGroupIds = $own ? [] : array_map(
            static fn (GroupMembership $membership): int => (int) $membership->getGroup()->getId(),
            $this->getCurrentMemberships($viewer),
        );

        return $this->render('group/member_groups.html.twig', [
            'member' => $member,
            'groups' => $rows,
            'myGroupIds' => $myGroupIds,
            'submenu' => $profileSubmenu->getSubmenu($viewer, $member, ['active' => 'groups']),
        ]);
    }

    #[Route(path: '/group/{group_id}', name: 'group_start', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function show(Request $request, #[MapEntity(id: 'group_id')] Group $group): Response
    {
        if (!$this->isGranted(GroupVoter::GROUP_VIEW, $group)) {
            $this->addTranslatedFlash('notice', 'group.not.approved.yet');

            return $this->redirectToRoute('groups_search');
        }

        /** @var Member $member */
        $member = $this->getUser();
        $repository = $this->getGroupRepository();
        $canViewForum = $this->isGranted(GroupVoter::GROUP_VIEW_FORUM, $group);

        return $this->render('group/show.html.twig', [
            'group' => $group,
            'description' => $group->getDescription($request->getLocale()),
            'membershipStatus' => $repository->findMembershipStatus($group, $member),
            'canViewForum' => $canViewForum,
            'threads' => $canViewForum ? $repository->findLatestThreads($group, self::LATEST_THREADS) : [],
            'memberCount' => $repository->countMembers($group),
            'latestMembers' => $repository->createMembersQueryBuilder($group)
                ->setMaxResults(self::SIDEBAR_MEMBERS)->getQuery()->getResult(),
            'administrators' => $group->getAdministrators(),
            'relatedGroups' => $this->getRelatedGroups($group),
            'submenu' => [
                'items' => $this->getSubmenuItems($member, $group),
                'active' => 'overview',
            ],
        ]);
    }

    #[Route(path: '/group/{group_id}/members', name: 'group_members', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    #[Route(
        path: '/group/{group_id}/members/page/{page_number}',
        name: 'group_members_paged',
        requirements: ['group_id' => '\d+', 'page_number' => '\d+'],
        methods: ['GET'],
    )]
    public function members(#[MapEntity(id: 'group_id')] Group $group, int $page_number = 1): Response
    {
        if (!$this->isGranted(GroupVoter::GROUP_VIEW, $group)) {
            return $this->redirectToRoute('groups_search');
        }

        /** @var Member $member */
        $member = $this->getUser();

        return $this->render('group/members.html.twig', [
            'group' => $group,
            'memberships' => $this->paginate(
                $this->getGroupRepository()->createMembersQueryBuilder($group),
                max(1, $page_number),
                self::MEMBERS_PER_PAGE,
            ),
            'submenu' => [
                'items' => $this->getSubmenuItems($member, $group),
                'active' => 'members',
            ],
        ]);
    }

    #[Route(path: '/group/realimg/{group_id}', name: 'group_realimg', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function picture(
        #[MapEntity(id: 'group_id')] Group $group,
        #[Autowire('%group_directory%')] string $groupDirectory,
    ): BinaryFileResponse {
        return $this->pictureResponse($groupDirectory, $group->getPicture());
    }

    #[Route(path: '/group/thumbimg/{group_id}', name: 'group_thumbimg', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function thumbnail(
        #[MapEntity(id: 'group_id')] Group $group,
        #[Autowire('%group_directory%')] string $groupDirectory,
    ): BinaryFileResponse {
        return $this->pictureResponse($groupDirectory, '' === $group->getPicture() ? '' : 'thumb' . $group->getPicture());
    }

    /**
     * Which of the member's groups their profile shows: a checkbox per group.
     */
    private function createProfileGroupsForm(Member $member): FormInterface
    {
        $choices = [];
        $shown = [];
        foreach ($this->getCurrentMemberships($member) as $membership) {
            $group = $membership->getGroup();
            $choices[$group->getName()] = $group->getId();
            if ($membership->isShownOnProfile()) {
                $shown[] = $group->getId();
            }
        }
        ksort($choices, \SORT_NATURAL | \SORT_FLAG_CASE);

        return $this->container->get('form.factory')->createNamedBuilder('profile_groups', FormType::class, ['groups' => $shown])
            ->add('groups', ChoiceType::class, [
                'choices' => $choices,
                'choice_translation_domain' => false,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'group.profile.groups',
            ])
            ->add('save', SubmitType::class, ['label' => 'save'])
            ->getForm();
    }

    /**
     * What the lists (my groups, all groups, archived) show around the groups: the search, the categories, the number of
     * groups on each tab and the groups the member is in (Joined).
     *
     * @return array<string, mixed>
     */
    private function getListParameters(Request $request, GroupSearch $search): array
    {
        $member = $this->getUser();
        /** @var GroupCategoryRepository $categoryRepository */
        $categoryRepository = $this->entityManager->getRepository(GroupCategory::class);
        $myGroupIds = $member instanceof Member ? array_map(
            static fn (GroupMembership $membership): int => (int) $membership->getGroup()->getId(),
            $this->getCurrentMemberships($member),
        ) : [];

        return [
            'search' => $search,
            'categories' => $categoryRepository->findAllSortedByName($request->getLocale()),
            'myGroupIds' => $myGroupIds,
            'myCount' => \count($myGroupIds),
            'allCount' => $this->paginate($this->getGroupRepository()->createSearchQueryBuilder(new GroupSearch()), 1, 1)->getNbResults(),
            'archivedCount' => $this->paginate($this->getGroupRepository()->createSearchQueryBuilder(new GroupSearch(archived: true)), 1, 1)->getNbResults(),
        ];
    }

    /**
     * @return list<GroupMembership>
     */
    private function getCurrentMemberships(Member $member): array
    {
        return $this->entityManager->getRepository(GroupMembership::class)->findBy([
            'member' => $member,
            'status' => GroupMembershipStatusType::CURRENT_MEMBER,
        ]);
    }

    private function pictureResponse(string $groupDirectory, string $fileName): BinaryFileResponse
    {
        $path = $groupDirectory . '/' . $fileName;
        if ('' === $fileName || str_contains($fileName, '/') || !is_file($path) || 0 === filesize($path)) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($path);
        $response->setPrivate();
        $response->setMaxAge(86400);

        return $response;
    }

    /**
     * @return list<Group> the approved groups the group links to
     */
    private function getRelatedGroups(Group $group): array
    {
        $links = $this->entityManager->createQueryBuilder()
            ->select('rg', 'r')
            ->from(RelatedGroup::class, 'rg')
            ->join('rg.relatedGroup', 'r')
            ->where('rg.group = :group')
            ->andWhere('rg.deletedBy IS NULL')
            ->andWhere('r.approved = :approved')
            ->setParameter('group', $group)
            ->setParameter('approved', Group::APPROVED)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(static fn (RelatedGroup $link): Group => $link->getRelatedGroup(), $links);
    }

    private function paginate(QueryBuilder $queryBuilder, int $page, int $perPage): Pagerfanta
    {
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder, false, false));
        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage(min(max(1, $page), max(1, $pager->getNbPages())));

        return $pager;
    }

    private function getGroupRepository(): GroupRepository
    {
        /** @var GroupRepository $repository */
        $repository = $this->entityManager->getRepository(Group::class);

        return $repository;
    }
}
