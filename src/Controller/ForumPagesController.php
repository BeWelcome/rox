<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\Member;
use App\Entity\Preference;
use App\Entity\ReportToModerator;
use App\Form\ModeratePostType;
use App\Form\ModerateThreadType;
use App\Model\ForumModel;
use App\Model\ForumModerationModel;
use App\Repository\ForumPostRepository;
use App\Repository\ForumThreadRepository;
use App\Security\ForumVoter;
use App\Security\GroupVoter;
use App\Utilities\GroupSubmenuTrait;
use App\Utilities\ItemsPerPageTraits;
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
    use ItemsPerPageTraits;

    public const int POSTS_PER_PAGE = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ForumModel $forumModel,
        private readonly ForumModerationModel $moderationModel,
    ) {
    }

    /**
     * The forums overview is gone: the groups page has the agora, the member's groups and their subscriptions.
     * ?groups=mine (the home page's link to the posts of the member's groups) goes to My groups.
     */
    #[Route(path: '/forums', name: 'forums', methods: ['GET'])]
    public function overview(Request $request): RedirectResponse
    {
        return 'mine' === $request->query->get('groups')
            ? $this->redirectToRoute('groups_mygroups', [], Response::HTTP_MOVED_PERMANENTLY)
            : $this->redirectToRoute('groups_redirect', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * The legacy overview had a page of group threads and one of agora threads.
     */
    #[Route(path: '/forums/page{groupsPage}/page{bwforumsPage}/', name: 'forums_pages', requirements: ['groupsPage' => '\d+', 'bwforumsPage' => '\d+'], methods: ['GET'])]
    public function overviewPages(): RedirectResponse
    {
        return $this->redirectToRoute('groups_redirect', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * The legacy overview of the groups' threads; the groups page has them now.
     */
    #[Route(path: '/groups/forums', name: 'groups_forums_overview', methods: ['GET'])]
    #[Route(path: '/groups/forums/{page_number}', name: 'groups_forums_overview_paged', requirements: ['page_number' => '\d+'], methods: ['GET'])]
    public function groupsOverview(): RedirectResponse
    {
        return $this->redirectToRoute('groups_redirect', [], Response::HTTP_MOVED_PERMANENTLY);
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

    /**
     * A post in its thread, on the page it is on; keeps the moderation panel's query (moderate, section, saved).
     */
    #[Route(path: '/forums/post/{postId}', name: 'forum_post', requirements: ['postId' => '\d+'], methods: ['GET'])]
    public function post(Request $request, #[MapEntity(id: 'postId')] ForumPost $post): RedirectResponse
    {
        $thread = $post->getThread();
        if (null === $thread) {
            throw $this->createNotFoundException();
        }
        /** @var ForumPostRepository $postRepository */
        $postRepository = $this->entityManager->getRepository(ForumPost::class);
        $member = $this->getMember();
        $page = $postRepository->findPageOf($post, $member, $this->isModerator(), $this->readsAscending($member), self::POSTS_PER_PAGE);

        $query = array_filter([
            'moderate' => $request->query->getInt('moderate') ?: null,
            'section' => \in_array($request->query->get('section'), ['post', 'topic'], true) ? $request->query->get('section') : null,
            'saved' => $request->query->getBoolean('saved') ? 1 : null,
        ]);
        $parameters = $thread->isAgora()
            ? ['threadId' => $thread->getId()] + (1 < $page ? ['pageId' => $page] : [])
            : ['group_id' => $thread->getGroup()?->getId(), 'thread' => $thread->getId()] + (1 < $page ? ['page_id' => $page] : []);
        $route = $thread->isAgora()
            ? (1 < $page ? 'forum_thread_pages' : 'forum_thread')
            : (1 < $page ? 'group_forum_thread_pages' : 'group_forum_thread');

        return $this->redirect($this->generateUrl($route, $parameters + $query) . '#post' . $post->getId());
    }

    #[Route(path: '/group/{group_id}/forum', name: 'group_forum', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    #[Route(path: '/group/{group_id}/forum/page{page_id}', name: 'group_forum_pages', requirements: ['group_id' => '\d+', 'page_id' => '\d+'], methods: ['GET'])]
    public function groupForum(Request $request, #[MapEntity(id: 'group_id')] Group $group, int $page_id = 1): Response
    {
        if (!$this->isGranted(GroupVoter::GROUP_VIEW, $group)) {
            return $this->redirectToRoute('groups_search');
        }
        $member = $this->getMember();
        $canRead = $this->isGranted(GroupVoter::GROUP_VIEW_FORUM, $group);

        // the forum moderators see all threads and can show only the reported or the hidden ones
        $moderator = $this->isModerator();
        $filter = $moderator && \in_array($request->query->get('filter'), ['reported', 'hidden'], true) ? (string) $request->query->get('filter') : 'all';
        $threads = null;
        if ($canRead) {
            $queryBuilder = $this->getThreadRepository()->createLatestQueryBuilder($member, $moderator, $group);
            if ($moderator) {
                $this->getThreadRepository()->filterForModerators($queryBuilder, $filter);
            }
            $threads = $this->paginate($queryBuilder, $page_id, $this->getItemsPerPage($member));
        }

        $parameters = [
            'group' => $group,
            'canRead' => $canRead,
            'ascending' => $this->readsAscending($member),
            'threads' => $threads,
            'moderator' => $moderator,
            'filter' => $filter,
            'filterCounts' => $moderator && $canRead ? $this->getThreadRepository()->countForModerators($member, $group) : [],
            'openReports' => $moderator && null !== $threads ? $this->moderationModel->countOpenReportsOfThreads(
                array_map(static fn (ForumThread $thread): int => (int) $thread->getId(), iterator_to_array($threads->getCurrentPageResults(), false)),
            ) : [],
        ];
        $parameters['submenu'] = ['items' => $this->getSubmenuItems($member, $group), 'active' => 'forum'];

        return $this->render('forum/group.html.twig', $parameters);
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

        $ascending = $this->readsAscending($member);
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
        $this->forumModel->markRead($thread, $member);
        // The first post (the oldest one shown: the thread's own first post id is not always right) and its author
        $starter = $postRepository->createThreadPostsQueryBuilder($thread, $member, $this->isModerator(), true)
            ->select('fp.id AS id, IDENTITY(fp.author) AS author')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        // Who wrote in the thread, in the order they first did
        $people = $postRepository->createThreadPostsQueryBuilder($thread, $member, $this->isModerator(), true)
            ->select('a.username AS username, MIN(fp.created) AS HIDDEN firstPost')
            ->andWhere('a.id IS NOT NULL')
            ->resetDQLPart('orderBy')
            ->groupBy('a.id, a.username')
            ->orderBy('firstPost', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $group = $thread->getGroup();
        $moderator = $this->isModerator();
        $reports = $moderator ? $this->moderationModel->findReportsOfPosts(
            array_map(static fn (ForumPost $post): int => (int) $post->getId(), iterator_to_array($posts->getCurrentPageResults(), false)),
        ) : [];

        $parameters = [
            'thread' => $thread,
            'group' => $group,
            'posts' => $posts,
            'ascending' => $ascending,
            'reverse' => $reverse,
            'locale' => $request->getLocale(),
            'subscribed' => $this->forumModel->isSubscribed($thread, $member),
            'people' => array_column($people, 'username'),
            'starter' => $starter,
            // the moderators see the reports about each post and have the moderation panel
            'reports' => $reports,
            'panel' => $moderator ? $this->getPanel($request, $thread, $starter, $reports) : null,
        ];
        if (null !== $group) {
            $parameters['submenu'] = ['items' => $this->getSubmenuItems($member, $group), 'active' => 'forum'];
        }

        return $this->render('forum/thread.html.twig', $parameters);
    }

    /**
     * The moderation panel of a thread: its forms for a post (?moderate={postId}; the first post if none) and for the
     * thread, sent to forum_admin. Open when the page has ?moderate or ?section (post or topic, the part shown).
     *
     * @param array{id: int, author: ?int}|null   $starter
     * @param array<int, list<ReportToModerator>> $reports the reports about the posts of the page
     *
     * @return array<string, mixed>|null
     */
    private function getPanel(Request $request, ForumThread $thread, ?array $starter, array $reports): ?array
    {
        $postId = $request->query->getInt('moderate') ?: (int) ($starter['id'] ?? 0);
        $post = 0 === $postId ? null : $this->entityManager->getRepository(ForumPost::class)->find($postId);
        if (!$post instanceof ForumPost || $post->getThread()?->getId() !== $thread->getId()) {
            return null;
        }
        $section = 'topic' === $request->query->get('section') ? 'topic' : 'post';
        $action = $this->generateUrl('forum_admin', ['postId' => $post->getId()]);

        return [
            'open' => $request->query->has('moderate') || $request->query->has('section'),
            'section' => $section,
            'saved' => $request->query->getBoolean('saved') ? $section : null,
            'post' => $post,
            'postForm' => $this->createForm(ModeratePostType::class, ModeratePostType::dataOf($post), ['action' => $action])->createView(),
            'threadForm' => $this->createForm(ModerateThreadType::class, ModerateThreadType::dataOf($thread), ['action' => $action])->createView(),
            'reports' => $reports[(int) $post->getId()] ?? $this->moderationModel->findReportsOfPosts([(int) $post->getId()])[(int) $post->getId()] ?? [],
        ];
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

    /**
     * Whether the member reads threads oldest post first.
     */
    private function readsAscending(Member $member): bool
    {
        return 'Yes' === $this->getPreference($member, Preference::FORUM_ORDER_LIST_ASC);
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
