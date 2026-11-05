<?php

namespace App\Controller;

use App\Model\WikiIndexModel;
use App\Model\WikiModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use App\Utilities\WikiPagesTrait;
use App\Wiki\WikiLinkResolver;
use App\Wiki\WikiPageName;
use App\Wiki\WikiScope;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The main wiki: the start page, pages with their history and changes, the editor, recent changes, categories and
 * the search. A group's page (Group_Lisbon) is shown in the group's Wiki tab (GroupWikiController).
 */
class WikiController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;
    use WikiPagesTrait;

    private const int RECENT_PER_PAGE = 20;

    public function __construct(
        private readonly WikiModel $wikiModel,
        private readonly WikiIndexModel $wikiIndexModel,
    ) {
    }

    #[Route(path: '/wiki', name: 'wiki_front_page')]
    public function showWikiFrontPage(): Response
    {
        $view = $this->wikiReadView(WikiScope::main(WikiPageName::FRONT_PAGE), 0, $this->wikiModel);
        if (null === $view) {
            return $this->redirectToRoute('wiki_page_create', ['pageTitle' => WikiPageName::FRONT_PAGE]);
        }

        return $this->render('wiki/page.html.twig', $this->withFrontPageBoxes($view));
    }

    #[Route(path: '/wiki/recent', name: 'wiki_recent', priority: 10)]
    public function showRecentChanges(#[MapQueryParameter] int $page = 1): Response
    {
        $pager = new Pagerfanta(new ArrayAdapter($this->wikiIndexModel->getRecentChanges()));
        $pager->setMaxPerPage(self::RECENT_PER_PAGE);
        $pager->setCurrentPage(max(1, min($page, $pager->getNbPages())));
        $edits = iterator_to_array($pager->getCurrentPageResults(), false);

        return $this->render('wiki/recent.html.twig', [
            'pager' => $pager,
            'members' => $this->wikiModel->getMembers(array_column($edits, 'author')),
        ]);
    }

    #[Route(path: '/wiki/search', name: 'wiki_search', priority: 10)]
    public function search(#[MapQueryParameter] string $q = ''): Response
    {
        $search = $this->wikiIndexModel->search($q, 50);

        return $this->render('wiki/search.html.twig', [
            'query' => trim($q),
            'search' => $search,
            'create_url' => $this->createUrl($q, $search['exact']),
        ]);
    }

    /**
     * The search box's suggestions.
     */
    #[Route(path: '/wiki/search/suggest', name: 'wiki_search_suggest', priority: 10)]
    public function suggest(#[MapQueryParameter] string $q = ''): JsonResponse
    {
        $search = $this->wikiIndexModel->search($q);
        $frontPage = $this->getTranslator()->trans('wiki.start.page');

        return new JsonResponse([
            'results' => array_map(
                static fn (array $result) => [
                    'title' => '' === $result['title'] ? $frontPage : $result['title'],
                    'url' => $result['url'],
                    'snippet' => $result['snippet'],
                ],
                $search['results'],
            ),
            'create' => $this->createUrl($q, $search['exact']),
        ]);
    }

    #[Route(path: '/wiki/category/{name}', name: 'wiki_category', priority: 10)]
    public function showCategory(string $name): Response
    {
        $pages = $this->wikiIndexModel->getPagesInCategory($name);

        return $this->render('wiki/category.html.twig', [
            'category' => str_replace('_', ' ', $name),
            'pages' => $pages,
            'members' => $this->wikiModel->getMembers(array_column($pages, 'author')),
        ]);
    }

    #[Route(path: '/wiki/{pageTitle}/{version}', name: 'wiki_page', requirements: ['version' => '\d+'], defaults: ['version' => 0])]
    public function showWikiPage(string $pageTitle, int $version, WikiLinkResolver $linkResolver): Response
    {
        $pageName = WikiPageName::fromTitle($pageTitle);
        $group = $linkResolver->groupsByPageName([$pageName])[$pageName] ?? null;
        if (null !== $group) {
            return 0 === $version
                ? $this->redirectToRoute('group_wiki_page', ['id' => $group['id']])
                : $this->redirectToRoute('group_wiki_page_version', ['id' => $group['id'], 'version' => $version]);
        }

        $scope = WikiScope::main($pageName);
        $view = $this->wikiReadView($scope, $version, $this->wikiModel);
        if (null === $view) {
            if (0 === $version) {
                return $this->redirectToRoute('wiki_page_create', ['pageTitle' => $pageName]);
            }
            throw $this->createNotFoundException();
        }

        if ($scope->isFrontPage()) {
            $view = $this->withFrontPageBoxes($view);
        } else {
            $view['related'] = $this->wikiIndexModel->getRelatedPages($pageName, $view['parsed']->categories);
        }

        return $this->render('wiki/page.html.twig', $view);
    }

    #[Route(path: '/wiki/{pageTitle}/edit', name: 'wiki_page_edit')]
    public function editWikiPage(Request $request, string $pageTitle): Response
    {
        return $this->wikiEditPage($request, WikiScope::main(WikiPageName::fromTitle($pageTitle)), false, $this->wikiModel, $this->wikiIndexModel, 'wiki/edit.html.twig', []);
    }

    #[Route(path: '/wiki/{pageTitle}/create', name: 'wiki_page_create')]
    public function createWikiPage(Request $request, string $pageTitle): Response
    {
        return $this->wikiEditPage($request, WikiScope::main(WikiPageName::fromTitle($pageTitle)), true, $this->wikiModel, $this->wikiIndexModel, 'wiki/edit.html.twig', []);
    }

    #[Route(path: '/wiki/{pageTitle}/history', name: 'wiki_page_history')]
    public function showHistory(string $pageTitle): Response
    {
        $view = $this->wikiHistoryView(WikiScope::main(WikiPageName::fromTitle($pageTitle)), $this->wikiModel);
        if (null === $view) {
            throw $this->createNotFoundException();
        }

        return $this->render('wiki/history.html.twig', $view);
    }

    #[Route(path: '/wiki/{pageTitle}/changes/{version}', name: 'wiki_page_changes', requirements: ['version' => '\d+'])]
    public function showChanges(string $pageTitle, int $version): Response
    {
        $view = $this->wikiChangesView(WikiScope::main(WikiPageName::fromTitle($pageTitle)), $version, $this->wikiModel);
        if (null === $view) {
            throw $this->createNotFoundException();
        }

        return $this->render('wiki/changes.html.twig', $view);
    }

    /**
     * The form for a page with the title searched for, unless it exists; a slash can't be in a page's name.
     */
    private function createUrl(string $query, bool $exact): ?string
    {
        $pageName = WikiPageName::fromTitle($query);
        if ($exact || '' === $pageName || str_contains($pageName, '/')) {
            return null;
        }

        return $this->generateUrl('wiki_page_create', ['pageTitle' => $pageName]);
    }

    /**
     * The start page's sidebar: the categories and the pages changed last.
     *
     * @param array<string, mixed> $view
     *
     * @return array<string, mixed>
     */
    private function withFrontPageBoxes(array $view): array
    {
        $recent = $this->wikiIndexModel->getRecentlyChanged();

        return [
            'categories' => $this->wikiIndexModel->getCategories(),
            'recently_changed' => $recent,
            'members' => $this->wikiModel->getMembers([$view['page']->getAuthor(), ...array_column($recent, 'author')]),
        ] + $view;
    }
}
