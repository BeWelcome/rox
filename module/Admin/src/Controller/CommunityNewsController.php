<?php

namespace Rox\Admin\Controller;

use Rox\Admin\Form\CommunityNewsType;
use Rox\CommunityNews\Model\CommunityNews;
use Rox\Core\Exception\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityNewsController extends Controller
{
    public function deleteAction()
    {
    }

    /***
     * Create a new community news
     *
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $communityNews = new CommunityNews();
        $form = $this->createForm(CommunityNewsType::class, $communityNews);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // form was submitted and all inputs are valid
            $data = $form->getData();
            $user = $this->getUser();
            $data->created_by = $user->id;
            $data->save();

            $this->addFlash('notify', 'New community news created.');

            $this->redirectToRoute('admin/communitynews');
        }

        return new Response(
            $this->render('@admin/communitynews/create.html.twig', [
                'form' => $form->createView(),
            ])
        );
    }

    /***
     * @param Request $request
     * @param int $id Id of the community news to edit
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        try {
            $communityNewsRepository = new CommunityNews();
            $communityNews = $communityNewsRepository->getById($id);
        } catch (NotFoundException $e) {
            throw $e;
        }

        $form = $this->createForm(CommunityNewsType::class, $communityNews);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            // form was submitted and all inputs
            return new Response('Succcesss: ' . $user->Username);
        }

        return new Response(
            $this->render('@admin/communitynews/edit.html.twig', [
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showList(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 15);

        list($communityNews, $count) = $this->communityNewsRepository->getAll($page, $limit);

        return new Response(
            $this->getEngine()->render('@admin/communitynews/list.html.twig', [
                'communityNews' => $communityNews,
                'filter' => $request->query->all(),
                'page' => $page,
                'pages' => ceil($count/$limit),
            ])
        );
    }

    public function toogleAction()
    {
    }
}
