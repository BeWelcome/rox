<?php

namespace Rox\CommunityNews\Controller;

use Rox\CommunityNews\Model\CommunityNews;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityNewsController extends Controller
{
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 15);

        $communityNewsRepository = new CommunityNews();

        $communityNews = $communityNewsRepository->getAll($page, $limit);
        $count = $communityNewsRepository->getAllCount();

        $content = $this->render('@communitynews/communitynews/list.html.twig', [
            'communityNews' => $communityNews,
            'filter' => $request->query->all(),
            'page' => $page,
            'pages' => ceil($count/$limit),
        ]);

        return new Response($content);
    }

    public function showAction($id)
    {
        $communityNewsRepository = new CommunityNews();
        $communityNews = $communityNewsRepository->getById($id);

        $content = $this->render('@communitynews/communitynews/show.html.twig', [
            'communityNews' => $communityNews,
        ]);

        return new Response($content);
    }
}
