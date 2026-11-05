<?php

namespace App\Utilities;

use App\Entity\Member;
use App\Form\CustomDataClass\WikiPageRequest;
use App\Form\WikiPageType;
use App\Model\WikiIndexModel;
use App\Model\WikiModel;
use App\Wiki\WikiConflictException;
use App\Wiki\WikiParser;
use App\Wiki\WikiScope;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The pages a wiki page has in the main wiki and in a group's Wiki tab: read (also an older version), edit and
 * create, history and the changes of a version. The controllers render them in their own frame.
 *
 * Needs TranslatedFlashTrait and TranslatorTrait.
 */
trait WikiPagesTrait
{
    /**
     * @return array<string, mixed>|null null if the page, or that version of it, doesn't exist
     */
    private function wikiReadView(WikiScope $scope, int $version, WikiModel $wikiModel): ?array
    {
        $page = $wikiModel->getPage($scope->pageName, $version);
        if (null === $page) {
            return null;
        }

        $latest = $wikiModel->countVersions($scope->pageName);

        return [
            'scope' => $scope,
            'page' => $page,
            'parsed' => $wikiModel->parse($page->getContent()),
            'latest' => $latest,
            'is_old' => $page->getVersion() < $latest,
            'members' => $wikiModel->getMembers([$page->getAuthor()]),
        ];
    }

    /**
     * The editor; a saved version leads to the page.
     *
     * @param array<string, mixed> $view what the frame adds
     */
    private function wikiEditPage(Request $request, WikiScope $scope, bool $create, WikiModel $wikiModel, WikiIndexModel $wikiIndexModel, string $template, array $view): Response
    {
        $page = $wikiModel->getPage($scope->pageName);
        if ($create && null !== $page) {
            return $this->redirectToRoute($scope->routes['edit'], $scope->params);
        }
        if (!$create && null === $page) {
            return $this->redirectToRoute($scope->routes['create'], $scope->params);
        }

        $form = $this->createForm(WikiPageType::class, new WikiPageRequest($page?->getContent() ?? '', $page?->getVersion() ?? 0));
        $form->handleRequest($request);
        $status = Response::HTTP_OK;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var WikiPageRequest $data */
            $data = $form->getData();
            /** @var Member $member */
            $member = $this->getUser();
            try {
                $wikiModel->save($scope->pageName, (string) $data->content, $member->getUsername(), $data->version);
                $this->addTranslatedFlash('notice', $create ? 'wiki.flash.created' : 'wiki.flash.updated');

                return $this->redirectToRoute($scope->routes['read'], $scope->params);
            } catch (WikiConflictException $conflict) {
                // the draft stays; saving it again makes it the next version
                $form = $this->createForm(WikiPageType::class, new WikiPageRequest($data->content, $conflict->latestVersion));
                $form->get('content')->addError(new FormError($this->getTranslator()->trans('wiki.conflict', ['version' => $conflict->latestVersion])));
                $create = false;
                $page = $wikiModel->getPage($scope->pageName);
                $status = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        }

        $blocker = WikiParser::visualBlocker((string) $form->get('content')->getData());

        return $this->render($template, $view + [
            'scope' => $scope,
            'form' => $form,
            'is_create' => $create,
            'next_version' => ($page?->getVersion() ?? 0) + 1,
            'latest' => $page?->getVersion() ?? 0,
            'visual_editable' => null === $blocker,
            'visual_blocker' => $blocker,
            'all_categories' => $scope->isGroup() ? [] : array_keys($wikiIndexModel->getCategories()),
        ], new Response(status: $status));
    }

    /**
     * @return array<string, mixed>|null null for a page without versions
     */
    private function wikiHistoryView(WikiScope $scope, WikiModel $wikiModel): ?array
    {
        $history = $wikiModel->getHistory($scope->pageName);
        if ([] === $history) {
            return null;
        }

        return [
            'scope' => $scope,
            'history' => $history,
            'latest' => $history[0]['version'],
            'members' => $wikiModel->getMembers(array_column($history, 'author')),
        ];
    }

    /**
     * @return array<string, mixed>|null null for a version without one before it
     */
    private function wikiChangesView(WikiScope $scope, int $version, WikiModel $wikiModel): ?array
    {
        $changes = $wikiModel->getChanges($scope->pageName, $version);
        if (null === $changes) {
            return null;
        }

        return [
            'scope' => $scope,
            'changes' => $changes,
            'latest' => $wikiModel->countVersions($scope->pageName),
        ];
    }
}
