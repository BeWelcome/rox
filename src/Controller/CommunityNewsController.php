<?php

namespace App\Controller;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Form\CommunityNewsCommentType;
use App\Form\CustomDataClass\CommunityNewsCommentRequest;
use App\Model\CommunityNewsModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $communityNews = $communityNewsModel->getPaginator($page, $limit);

        return $this->render('communitynews/list.html.twig', [
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
    public function showAction(Request $request, CommunityNews $communityNews)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $communityNewsModel = new CommunityNewsModel($this->getDoctrine());
        $comments = $communityNewsModel->getCommentsPaginator($communityNews, $page, $limit);

        return $this->render('communitynews/show.html.twig', [
            'communityNews' => $communityNews,
            'comments' => $comments,
        ]);
    }

    /**
     * @Route("/communitynews/{id}/comment/add", name="communitynews_comment_add")
     *
     * @param Request       $request
     * @param CommunityNews $communityNews
     *
     * @return Response
     */
    public function addCommentAction(Request $request, CommunityNews $communityNews)
    {
        $communityNewsCommentRequest = new CommunityNewsCommentRequest();
        $form = $this->createForm(CommunityNewsCommentType::class, $communityNewsCommentRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $communityNewsComment = new CommunityNewsComment();
            $communityNewsComment->setCommunityNews($communityNews);
            $communityNewsComment->setTitle($data->title);
            $communityNewsComment->setText($data->text);
            $communityNewsComment->setCreated(new \DateTime());
            $communityNewsComment->setAuthor($this->getUser());
            $em->persist($communityNewsComment);
            $em->flush();

            return $this->redirectToRoute('communitynews_show', ['id' => $communityNews->getId()]);
        }

        return $this->render('communitynews/addcomment.html.twig', [
            'communityNews' => $communityNews,
            'form' => $form->createView(),
        ]);
    }
}
