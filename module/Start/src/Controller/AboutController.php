<?php

namespace Rox\Start\Controller;

use Rox\CommunityNews\Model\CommunityNews;
use Rox\CommunityNews\Service\CommunityNewsService;
use Rox\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AboutController extends AbstractController
{
    /**
     * @var CommunityNewsService
     */
    protected $communityNewsService;

    public function __construct(CommunityNewsService $communityNewsService)
    {
        $this->communityNewsService = $communityNewsService;
    }

    public function indexAction(Request $request)
    {
        $page = $request->attributes->get('page');

        $pageTemplate = '@start/about/' . $page . '.html.twig';

        if (!$this->getEngine()->exists($pageTemplate)) {
            throw new NotFoundHttpException();
        }

        $communityNews = new CommunityNews();

        $communityNews = $communityNews->newQuery()
            ->limit(2)->orderBy('created_at', 'desc')->get();

        return new Response($this->render('@start/about.html.twig', [
            'pageTemplate' => $pageTemplate,
            'page' => $page,
            'communityNews' => $communityNews,
        ]));
    }
}
