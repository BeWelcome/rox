<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CommunityNews;
use AppBundle\Model\CommunityNewsModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityNewsController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/communitynews", name="communitynews")
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $communityNewsModel = new CommunityNewsModel($this->getDoctrine());
        $communityNews = $communityNewsModel->getLatestPaginator($page, $limit);

        return $this->render(':communitynews:list.html.twig', [
            'communityNews' => $communityNews,
        ]);
    }

    /**
     * @Route("/communitynews/{id}", name="communitynews_show")
     *
     * @param CommunityNews $communityNews
     *
     * @return Response
     */
    public function showAction(CommunityNews $communityNews)
    {
        return $this->render(':communitynews:show.html.twig', [
            'communityNews' => $communityNews,
        ]);
    }
}
