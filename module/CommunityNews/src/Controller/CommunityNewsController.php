<?php

namespace Rox\CommunityNews\Controller;

use Rox\Core\Controller\AbstractController;
use Rox\CommunityNews\Repository\CommunityNewsRepositoryInterface;
use Rox\CommunityNews\Service\CommunityNewsServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityNewsController extends AbstractController
{
    /**
     * @var CommunityNewsRepositoryInterface
     */
    protected $communityNewsRepository;

    /**
     * @var CommunityNewsServiceInterface
     */
    protected $communityNewsService;

    /**
     * CommunityNewsController constructor.
     *
     * @param CommunityNewsRepositoryInterface $communityNewsRepository
     * @param CommunityNewsServiceInterface    $communityNewsService
     */
    public function __construct(
        CommunityNewsRepositoryInterface $communityNewsRepository,
        CommunityNewsServiceInterface $communityNewsService
    ) {
        $this->communityNewsRepository = $communityNewsRepository;
        $this->communityNewsService = $communityNewsService;
    }

    public function listAction(Request $request)
    {
        $request;
        $communityNews = $this->communityNewsRepository->getAll();

        $content = $this->render('@communitynews/communitynews/list.html.twig', [
            'communityNews' => $communityNews,
        ]);

        return new Response($content);
    }

    public function showAction($id)
    {
        $communityNews = $this->communityNewsRepository->getById($id);

        $content = $this->render('@communitynews/communitynews/show.html.twig', [
            'communityNews' => $communityNews,
        ]);

        return new Response($content);
    }
}
