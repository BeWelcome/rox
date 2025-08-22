<?php

namespace App\Controller;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Form\CommunityNewsCommentType;
use App\Form\CustomDataClass\CommunityNewsCommentRequest;
use App\Model\CommunityNewsModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommunityNewsController extends AbstractController
{
    public function __construct(private readonly CommunityNewsModel $communityNewsModel)
    {
    }

    /**
     * @return Response
     */
    #[Route(path: '/communitynews', name: 'communitynews')]
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $communityNews = $this->communityNewsModel->getPaginator($page, $limit);

        return $this->render('communitynews/list.html.twig', [
            'communityNews' => $communityNews,
        ]);
    }

    #[Route(path: '/communitynews/{id}', name: 'communitynews_show')]
    public function showAction(Request $request, CommunityNews $communityNews, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED', null, "Can't access this page");

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->communityNewsModel->getCommentsPaginator($communityNews, $page, $limit);

        $communityNewsCommentRequest = new CommunityNewsCommentRequest();
        $form = $this->createForm(CommunityNewsCommentType::class, $communityNewsCommentRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $communityNewsComment = new CommunityNewsComment();
            $communityNewsComment->setCommunityNews($communityNews);
            $communityNewsComment->setTitle($data->title);
            $communityNewsComment->setText($data->text);
            $communityNewsComment->setAuthor($this->getUser());
            $entityManager->persist($communityNewsComment);
            $entityManager->flush();

            return $this->redirectToRoute('communitynews_show', ['id' => $communityNews->getId()]);
        }

        return $this->render('communitynews/show.html.twig', [
            'communityNews' => $communityNews,
            'comments' => $comments,
            'form' => $form->createView(),
        ]);
    }
}
