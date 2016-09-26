<?php

namespace Rox\Admin\Controller;

use Rox\Admin\Form\CommunityNewsType;
use Rox\CommunityNews\Model\CommunityNews;
use Rox\Core\Exception\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommunityNewsController
 * @package Rox\Admin\Controller
 */
class CommunityNewsController extends Controller
{
    /**
     * @param $create
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws NotFoundException
     */
    private function handleEditCreateAction($request, $id = 0)
    {
        try {
            $communityNews = new CommunityNews();
            $flashText = 'Community news created.';
            if ($id !== 0) {
                $flashText = 'Community news updated.';
                $communityNewsRepository = new CommunityNews();
                $communityNews = $communityNewsRepository->getById($id);
            }
        } catch (NotFoundException $e) {
            throw $e;
        }

        $form = $this->createForm(CommunityNewsType::class, $communityNews);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $this->getUser();
            if ($id === 0) {
                $data->created_by = $user->id;
            }
            if ($id !== 0) {
                $data->updated_by = $user->id;
            }

            $data->save();

            $this->addFlash('notice', $flashText);

            return $this->redirectToRoute('admin/communitynews');
        }

        return new Response(
            $this->render('@admin/communitynews/edit.html.twig', [
                'form' => $form->createView(),
            ])
        );
    }

    /***
     * Create a new community news
     *
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        return $this->handleEditCreateAction($request);
    }

    /***
     * @param Request $request
     * @param int $id Id of the community news to edit
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        return $this->handleEditCreateAction($request, $id);
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
        $communityNewsRepository = new CommunityNews();

        $communityNews = $communityNewsRepository->getAll($page, $limit);
        $count = $communityNewsRepository->getAllCount();

        return new Response(
            $this->render('@admin/communitynews/list.html.twig', [
                'communityNews' => $communityNews,
                'filter' => $request->query->all(),
                'page' => $page,
                'pages' => ceil($count/$limit),
            ])
        );
    }

    /**
     * @param $id
     * @param $visible
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function setPublic($id, $visible)
    {
        try {
            $communityNewsRepository = new CommunityNews();
            $communityNews = $communityNewsRepository->getById($id);
        } catch (NotFoundException $e) {
            $this->addFlash('notice', 'Community news with id ' . $id . ' doesn\'t exist.');

            return $this->redirectToRoute('admin/communitynews');
        }
        $communityNews->public = $visible;
        $communityNews->save();

        return $communityNews->title;
    }

    /**
     * Sets the public flag so that the news becomes visible for all members.
     *
     * @param integer $id
     */
    public function showAction($id)
    {
        $title = $this->setPublic($id, true);
        $this->addFlash('notice', 'Community news \'' . $title . '\' is now visible for all members.');

        return $this->redirectToRoute('admin/communitynews');
    }

    /**
     * Sets the public flag so that the news becomes invisible for all members.
     *
     * @param integer $id
     */
    public function hideAction($id)
    {
        $title = $this->setPublic($id, false);
        $this->addFlash('notice', 'Community news \'' . $title . '\' is now invisible for all members.');

        return $this->redirectToRoute('admin/communitynews');
    }
}
