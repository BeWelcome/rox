<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\Group;
use App\Entity\Member;
use App\Security\ForumVoter;
use App\Security\GroupVoter;
use App\Service\ForumSearch;
use App\Utilities\GroupSubmenuTrait;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Searching the forums (all of them, or one group's), at the legacy forum's URLs. The hits are the posts the member
 * may read (ForumVoter), newest first.
 */
class ForumSearchController extends AbstractController
{
    use GroupSubmenuTrait;

    private const int POSTS_PER_PAGE = 30;

    /** A legacy tag in a URL: its number, maybe followed by its name (/forums/t12-travel). */
    private const string TAG = '\d+[^/]*';

    public function __construct(
        private readonly ForumSearch $forumSearch,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * The search form sends the words as a query parameter; the results have them in the path, as the legacy forum did.
     */
    #[Route(path: '/forums/search', name: 'forums_search_form', methods: ['GET'])]
    public function form(Request $request): RedirectResponse
    {
        $keyword = trim((string) $request->query->get('q'));

        return '' === $keyword
            ? $this->redirectToRoute('forums')
            : $this->redirectToRoute('forums_search', ['keyword' => $keyword]);
    }

    #[Route(path: '/forums/search/{keyword}', name: 'forums_search', requirements: ['keyword' => '[^/]+'], methods: ['GET'])]
    #[Route(path: '/forums/search/{keyword}/{page}', name: 'forums_search_pages', requirements: ['keyword' => '[^/]+', 'page' => '\d+'], methods: ['GET'])]
    public function search(string $keyword, int $page = 1): Response
    {
        return $this->render('forum/search.html.twig', [
            'keyword' => $keyword,
            'posts' => $this->findPosts($keyword, null, $page),
            'group' => null,
        ]);
    }

    #[Route(path: '/group/{group_id}/search', name: 'group_search_discussions', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function searchGroup(Request $request, #[MapEntity(id: 'group_id')] Group $group): Response
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_VIEW_FORUM, $group);
        $keyword = trim((string) $request->query->get('q'));
        $parameters = [
            'keyword' => $keyword,
            'posts' => '' === $keyword ? null : $this->findPosts($keyword, $group, max(1, $request->query->getInt('page', 1))),
            'group' => $group,
        ];
        if (!$group->isBeWelcome()) {
            /** @var Member $member */
            $member = $this->getUser();
            $parameters['submenu'] = ['items' => $this->getSubmenuItems($member, $group), 'active' => 'forum'];
        }

        return $this->render('forum/search.html.twig', $parameters);
    }

    /**
     * The legacy forum's tag pages and its order switch: the tags are gone, the overview is what's left.
     */
    #[Route(path: '/forums/t{tag}', name: 'forum_tag', requirements: ['tag' => self::TAG], methods: ['GET'])]
    #[Route(path: '/forums/t{tag}/{detail1}', name: 'forum_tag_detail_1', requirements: ['tag' => self::TAG], methods: ['GET'])]
    #[Route(path: '/forums/t{tag}/{detail1}/{detail2}', name: 'forum_tag_detail_2', requirements: ['tag' => self::TAG], methods: ['GET'])]
    #[Route(path: '/forums/reverse', name: 'forums_reverse', methods: ['GET'])]
    public function legacyOverviews(): RedirectResponse
    {
        return $this->redirectToRoute('forums', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    private function findPosts(string $keyword, ?Group $group, int $page): Pagerfanta
    {
        $moderator = $this->isGranted(Member::ROLE_ADMIN_FORUMMODERATOR) || $this->isGranted(Member::ROLE_ADMIN_ADMIN);
        $postIds = $this->forumSearch->findPostIds($keyword, $moderator, $group?->getId());

        $posts = [];
        if ([] !== $postIds) {
            $found = $this->entityManager->getRepository(ForumPost::class)->findBy(['id' => $postIds]);
            usort($found, static fn (ForumPost $a, ForumPost $b): int => $b->getCreated() <=> $a->getCreated());
            $posts = array_values(array_filter($found, fn (ForumPost $post): bool => $this->isGranted(ForumVoter::POST_VIEW, $post)));
        }

        $pager = new Pagerfanta(new ArrayAdapter($posts));
        $pager->setMaxPerPage(self::POSTS_PER_PAGE);
        $pager->setCurrentPage(min(max(1, $page), max(1, $pager->getNbPages())));

        return $pager;
    }
}
