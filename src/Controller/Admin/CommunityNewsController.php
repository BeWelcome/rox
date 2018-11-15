<?php

namespace App\Controller\Admin;

use App\Entity\CommunityNews;
use App\Form\CommunityNewsType;
use App\Model\CommunityNewsModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommunityNewsController extends Controller
{
    /**
     * @Route("/admin/communitynews", name="admin_communitynews_overview")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showOverviewAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);

        $communityNewsModel = new CommunityNewsModel($this->getDoctrine());
        $communityNews = $communityNewsModel->getAdminPaginator($page, $limit);

        return $this->render('admin/communitynews/list.html.twig', [
            'communityNews' => $communityNews,
        ]);
    }

    /**
     * @Route("/admin/communitynews/create", name="admin_communitynews_create")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $communityNews = new CommunityNews();
        $communityNewsForm = $this->createForm(CommunityNewsType::class, $communityNews);

        $communityNewsForm->handleRequest($request);
        if ($communityNewsForm->isSubmitted() && $communityNewsForm->isValid()) {
            $data = $communityNewsForm->getData();
            $now = new \DateTime();
            $data->setCreatedBy($this->getUser());
            $data->setCreatedAt($now);
            $data->setUpdatedBy($this->getUser());
            $data->setUpdatedAt($now);
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->flush();

            return $this->redirectToRoute('admin_communitynews_overview');
        }

        return $this->render('admin/communitynews/editcreate.html.twig', [
            'form' => $communityNewsForm->createView(),
            ]);
    }

    /**
     * @Route("/admin/communitynews/{id}/edit", name="admin_communitynews_edit")
     *
     * @param Request       $request
     * @param CommunityNews $communityNews
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, CommunityNews $communityNews)
    {
        $communityNewsForm = $this->createForm(CommunityNewsType::class, $communityNews);

        $communityNewsForm->handleRequest($request);
        if ($communityNewsForm->isSubmitted() && $communityNewsForm->isValid()) {
            $data = $communityNewsForm->getData();
            $data->setUpdatedAt(new \DateTime());
            $data->setUpdatedby($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->flush();

            return $this->redirectToRoute('admin_communitynews_overview');
        }

        return $this->render('admin/communitynews/editcreate.html.twig', [
            'form' => $communityNewsForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/communitynews/{id}/hide", name="admin_communitynews_hide")
     *
     * @param CommunityNews $communityNews
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function hideAction(CommunityNews $communityNews)
    {
        $communityNews->setPublic(false);
        $communityNews->setUpdatedAt(new \DateTime());
        $communityNews->setUpdatedby($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($communityNews);
        $em->flush();

        $this->addFlash('notice', 'Community News '.$communityNews->getTitle().' is now hidden for all members');

        return new RedirectResponse($this->generateUrl('admin_communitynews_overview'));
    }

    /**
     * @Route("/admin/communitynews/{id}/show", name="admin_communitynews_unhide")
     *
     * @param CommunityNews $communityNews
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unhideAction(CommunityNews $communityNews)
    {
        $communityNews->setPublic(true);
        $communityNews->setUpdatedAt(new \DateTime());
        $communityNews->setUpdatedby($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($communityNews);
        $em->flush();

        $this->addFlash('notice', 'Community News '.$communityNews->getTitle().' is now visible for all members');

        return new RedirectResponse($this->generateUrl('admin_communitynews_overview'));
    }
}
