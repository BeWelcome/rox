<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Member;
use App\Model\WikiIndexModel;
use App\Model\WikiModel;
use App\Security\GroupVoter;
use App\Utilities\GroupSubmenuTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use App\Utilities\WikiPagesTrait;
use App\Wiki\WikiScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * A group's wiki page in the group's Wiki tab: read (also an older version), edit and create, history and changes.
 * Every member can read it; the group's members and those who administer the group edit it. The agora has none.
 */
class GroupWikiController extends AbstractController
{
    use GroupSubmenuTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;
    use WikiPagesTrait;

    public function __construct(
        private readonly WikiModel $wikiModel,
        private readonly WikiIndexModel $wikiIndexModel,
    ) {
    }

    #[Route(path: '/group/{id}/wiki', name: 'group_wiki_page', requirements: ['id' => '\d+'])]
    public function show(Group $group): Response
    {
        return $this->showVersion($group, 0);
    }

    #[Route(path: '/group/{id}/wiki/{version}', name: 'group_wiki_page_version', requirements: ['id' => '\d+', 'version' => '\d+'])]
    public function showVersion(Group $group, int $version): Response
    {
        $scope = $this->scope($group);
        $view = $this->wikiReadView($scope, $version, $this->wikiModel);
        if (null === $view && 0 !== $version) {
            throw $this->createNotFoundException();
        }

        return $this->render('group/wiki.html.twig', ($view ?? ['scope' => $scope, 'page' => null]) + $this->frame($group));
    }

    #[Route(path: '/group/{id}/wiki/edit', name: 'group_wiki_page_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Group $group): Response
    {
        $this->denyAccessUnlessGranted(GroupVoter::EDIT_WIKI, $group);

        return $this->wikiEditPage($request, $this->scope($group), false, $this->wikiModel, $this->wikiIndexModel, 'group/wiki.edit.html.twig', $this->frame($group));
    }

    #[Route(path: '/group/{id}/wiki/create', name: 'group_wiki_page_create', requirements: ['id' => '\d+'])]
    public function create(Request $request, Group $group): Response
    {
        $this->denyAccessUnlessGranted(GroupVoter::EDIT_WIKI, $group);

        return $this->wikiEditPage($request, $this->scope($group), true, $this->wikiModel, $this->wikiIndexModel, 'group/wiki.edit.html.twig', $this->frame($group));
    }

    #[Route(path: '/group/{id}/wiki/history', name: 'group_wiki_history', requirements: ['id' => '\d+'])]
    public function history(Group $group): Response
    {
        $view = $this->wikiHistoryView($this->scope($group), $this->wikiModel);
        if (null === $view) {
            return $this->redirectToRoute('group_wiki_page', ['id' => $group->getId()]);
        }

        return $this->render('group/wiki.history.html.twig', $view + $this->frame($group));
    }

    #[Route(path: '/group/{id}/wiki/changes/{version}', name: 'group_wiki_changes', requirements: ['id' => '\d+', 'version' => '\d+'])]
    public function changes(Group $group, int $version): Response
    {
        $view = $this->wikiChangesView($this->scope($group), $version, $this->wikiModel);
        if (null === $view) {
            throw $this->createNotFoundException();
        }

        return $this->render('group/wiki.changes.html.twig', $view + $this->frame($group));
    }

    /**
     * The group's wiki; groups not approved yet show to those who administer them only, the agora has no wiki.
     */
    private function scope(Group $group): WikiScope
    {
        if ($group->isBeWelcome() || !$this->isGranted(GroupVoter::GROUP_VIEW, $group)) {
            throw $this->createNotFoundException();
        }

        return WikiScope::group($group, $this->isGranted(GroupVoter::EDIT_WIKI, $group));
    }

    /**
     * @return array<string, mixed> the group's head and tabs
     */
    private function frame(Group $group): array
    {
        /** @var Member $member */
        $member = $this->getUser();

        return [
            'group' => $group,
            'submenu' => ['active' => 'wiki', 'items' => $this->getSubmenuItems($member, $group)],
        ];
    }
}
