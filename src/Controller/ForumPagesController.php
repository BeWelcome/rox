<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\Member;
use App\Entity\Preference;
use App\Repository\ForumPostRepository;
use App\Repository\ForumThreadRepository;
use App\Security\ForumVoter;
use App\Security\GroupVoter;
use App\Utilities\GroupSubmenuTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Reading the forums: the overview of the latest threads in all groups, a group's forum (the BeWelcome group's is
 * the agora) and a thread. The URLs are the ones the legacy forum had; an agora thread lives at /forums/s{id}, a
 * group's thread at /group/{groupId}/forum/s{id}.
 */
class ForumPagesController extends AbstractController
{
    use GroupSubmenuTrait;

    public const int POSTS_PER_PAGE = 100;
    private const int THREADS_PER_PAGE = 20;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/forums', name: 'forums', methods: ['GET'])]
    public function overview(Request $request): Response
    {
        $member = $this->getMember();
        $onlyMyGroups = 'Yes' === $this->getPreference($member, Preference::SHOW_MY_GROUP_POSTS_ONLY);
        $threads = $this->paginate(
            $this->getThreadRepository()->createLatestQueryBuilder($member, $this->isModerator(), null, $onlyMyGroups),
            $request->query->getInt('page', 1),
            $this->getThreadsPerPage($member),
        );

        return $this->render('forum/overview.html.twig', [
            'threads' => $threads,
            'onlyMyGroups' => $onlyMyGroups,
        ]);
    }

    /**
     * The legacy overview had a page of group threads and one of agora threads.
     */
    #[Route(path: '/forums/page{groupsPage}/page{bwforumsPage}/', name: 'forums_pages', requirements: ['groupsPage' => '\d+', 'bwforumsPage' => '\d+'], methods: ['GET'])]
    public function overviewPages(int $groupsPage): RedirectResponse
    {
        return $this->redirectToRoute('forums', ['page' => $groupsPage], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * The legacy overview of the groups' threads; the forum overview shows them all now.
     */
    #[Route(path: '/groups/forums', name: 'groups_forums_overview', methods: ['GET'])]
    #[Route(path: '/groups/forums/{page_number}', name: 'groups_forums_overview_paged', requirements: ['page_number' => '\d+'], methods: ['GET'])]
    public function groupsOverview(): RedirectResponse
    {
        return $this->redirectToRoute('forums', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route(path: '/forums/bwforum', name: 'bwforum', methods: ['GET'])]
    public function agora(): RedirectResponse
    {
        return $this->redirectToRoute('group_forum', ['group_id' => Group::BEWELCOME], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route(path: '/forums/bwforum/page{bwforumsPage}/', name: 'bwforum_pages', requirements: ['bwforumsPage' => '\d+'], methods: ['GET'])]
    public function agoraPages(int $bwforumsPage): RedirectResponse
    {
        return $this->redirectToRoute('group_forum_pages', ['group_id' => Group::BEWELCOME, 'page_id' => $bwforumsPage], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route(path: '/group/{group_id}/forum', name: 'group_forum', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    #[Route(path: '/group/{group_id}/forum/page{page_id}', name: 'group_forum_pages', requirements: ['group_id' => '\d+', 'page_id' => '\d+'], methods: ['GET'])]
    public function groupForum(#[MapEntity(id: 'group_id')] Group $group, int $page_id = 1): Response
    {
        if (!$this->isGranted(GroupVoter::GROUP_VIEW, $group)) {
            return $this->redirectToRoute('groups_search');
        }
        $member = $this->getMember();
        $canRead = $this->isGranted(GroupVoter::GROUP_VIEW_FORUM, $group);

        return $this->render('forum/group.html.twig', [
            'group' => $group,
            'canRead' => $canRead,
            'threads' => $canRead ? $this->paginate(
                $this->getThreadRepository()->createLatestQueryBuilder($member, $this->isModerator(), $group),
                $page_id,
                self::THREADS_PER_PAGE,
            ) : null,
            'submenu' => ['items' => $this->getSubmenuItems($member, $group), 'active' => 'forum'],
        ]);
    }

    #[Route(path: '/forums/s{threadId}', name: 'forum_thread', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    #[Route(path: '/forums/s{threadId}/', name: 'forum_permalink', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    #[Route(path: '/forums/s{threadId}-{title}', name: 'forum_thread_title', requirements: ['threadId' => '\d+', 'title' => '[^/]*'], methods: ['GET'])]
    #[Route(path: '/forums/s{threadId}/page{pageId}', name: 'forum_thread_pages', requirements: ['threadId' => '\d+', 'pageId' => '\d+'], methods: ['GET'])]
    #[Route(path: '/forums/s{threadId}//reverse', name: 'forum_reverse', requirements: ['threadId' => '\d+'], defaults: ['reverse' => true], methods: ['GET'])]
    public function agoraThread(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread, int $pageId = 1, bool $reverse = false): Response
    {
        if (!$thread->isAgora()) {
            return $this->redirectToThread($thread, $pageId, $reverse);
        }

        return $this->showThread($request, $thread, $pageId, $reverse);
    }

    #[Route(path: '/group/{group_id}/forum/s{thread}', name: 'group_forum_thread', requirements: ['group_id' => '\d+', 'thread' => '\d+'], methods: ['GET'])]
    #[Route(path: '/group/{group_id}/forum/s{thread}/', name: 'group_forum_thread_slash', requirements: ['group_id' => '\d+', 'thread' => '\d+'], methods: ['GET'])]
    #[Route(path: '/group/{group_id}/forum/s{thread}-{title}', name: 'group_forum_thread_title', requirements: ['group_id' => '\d+', 'thread' => '\d+', 'title' => '[^/]*'], methods: ['GET'])]
    #[Route(path: '/group/{group_id}/forum/s{thread}-{title}/', name: 'group_forum_thread_title_slash', requirements: ['group_id' => '\d+', 'thread' => '\d+', 'title' => '[^/]*'], methods: ['GET'])]
    #[Route(path: '/group/{group_id}/forum/s{thread}/page{page_id}', name: 'group_forum_thread_pages', requirements: ['group_id' => '\d+', 'thread' => '\d+', 'page_id' => '\d+'], methods: ['GET'])]
    #[Route(path: '/group/{group_id}/forum/s{thread}/reverse', name: 'group_forum_thread_reverse', requirements: ['group_id' => '\d+', 'thread' => '\d+'], defaults: ['reverse' => true], methods: ['GET'])]
    public function groupThread(
        Request $request,
        int $group_id,
        #[MapEntity(id: 'thread')] ForumThread $thread,
        int $page_id = 1,
        bool $reverse = false,
    ): Response {
        if ($thread->getGroup()?->getId() !== $group_id || $thread->isAgora()) {
            return $this->redirectToThread($thread, $page_id, $reverse);
        }

        return $this->showThread($request, $thread, $page_id, $reverse);
    }

    private function showThread(Request $request, ForumThread $thread, int $page, bool $reverse): Response
    {
        $member = $this->getMember();
        if (!$this->isGranted(ForumVoter::THREAD_VIEW, $thread)) {
            return $this->render('forum/thread_hidden.html.twig', ['thread' => $thread], new Response('', Response::HTTP_FORBIDDEN));
        }

        $ascending = 'Yes' === $this->getPreference($member, Preference::FORUM_ORDER_LIST_ASC);
        if ($reverse) {
            $ascending = !$ascending;
        }

        /** @var ForumPostRepository $postRepository */
        $postRepository = $this->entityManager->getRepository(ForumPost::class);
        $posts = $this->paginate(
            $postRepository->createThreadPostsQueryBuilder($thread, $member, $this->isModerator(), $ascending),
            $page,
            self::POSTS_PER_PAGE,
        );
        $this->getThreadRepository()->countView($thread);

        $group = $thread->getGroup();

        $parameters = [
            'thread' => $thread,
            'group' => $group,
            'posts' => $posts,
            'ascending' => $ascending,
            'reverse' => $reverse,
            'locale' => $request->getLocale(),
        ];
        if (null !== $group && !$group->isBeWelcome()) {
            $parameters['submenu'] = ['items' => $this->getSubmenuItems($member, $group), 'active' => 'forum'];
        }

        return $this->render('forum/thread.html.twig', $parameters);
    }

    private function redirectToThread(ForumThread $thread, int $page, bool $reverse): RedirectResponse
    {
        if ($thread->isAgora()) {
            return $reverse
                ? $this->redirectToRoute('forum_reverse', ['threadId' => $thread->getId()], Response::HTTP_MOVED_PERMANENTLY)
                : $this->redirectToRoute(1 === $page ? 'forum_thread' : 'forum_thread_pages', array_filter([
                    'threadId' => $thread->getId(),
                    'pageId' => 1 === $page ? null : $page,
                ]), Response::HTTP_MOVED_PERMANENTLY);
        }

        $parameters = ['group_id' => $thread->getGroup()->getId(), 'thread' => $thread->getId()];
        if ($reverse) {
            return $this->redirectToRoute('group_forum_thread_reverse', $parameters, Response::HTTP_MOVED_PERMANENTLY);
        }

        return 1 === $page
            ? $this->redirectToRoute('group_forum_thread', $parameters, Response::HTTP_MOVED_PERMANENTLY)
            : $this->redirectToRoute('group_forum_thread_pages', $parameters + ['page_id' => $page], Response::HTTP_MOVED_PERMANENTLY);
    }

    private function paginate(QueryBuilder $queryBuilder, int $page, int $perPage): Pagerfanta
    {
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder, false, false));
        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage(min(max(1, $page), max(1, $pager->getNbPages())));

        return $pager;
    }

    private function getThreadsPerPage(Member $member): int
    {
        $value = (int) $this->getPreference($member, Preference::NUMBER_GROUPS_POSTS);

        return $value > 0 ? $value : self::THREADS_PER_PAGE;
    }

    private function getPreference(Member $member, string $codename): ?string
    {
        $preference = $this->entityManager->getRepository(Preference::class)->findOneBy(['codename' => $codename]);

        return null === $preference ? null : $member->getMemberPreference($preference)->getValue();
    }

    private function isModerator(): bool
    {
        return $this->isGranted(Member::ROLE_ADMIN_FORUMMODERATOR) || $this->isGranted(Member::ROLE_ADMIN_ADMIN);
    }

    private function getThreadRepository(): ForumThreadRepository
    {
        /** @var ForumThreadRepository $repository */
        $repository = $this->entityManager->getRepository(ForumThread::class);

        return $repository;
    }

    private function getMember(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }
}
