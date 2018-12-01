<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Preference;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\SearchFormType;
use App\Pagerfanta\SearchAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/members", name="search_members")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchAction(Request $request)
    {
        $pager = false;
        $results = false;
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_MAP]);
        $showMap = $member->getMemberPreferenceValue($preference);

        $searchFormRequest = new SearchFormRequest();
        $searchFormRequest->showMap = ('Yes' === $showMap) ? true : false;
        $form = $this->createForm(SearchFormType::class, $searchFormRequest, [
            'groups' => $member->getGroups(),
            'languages' => $member->getLanguages(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $memberPreference = $member->getMemberPreference($preference);
            if ($data->showMap) {
                $memberPreference->setValue('Yes');
            } else {
                $memberPreference->setValue('No');
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($memberPreference);
            $em->flush();
            $searchAdapter = new SearchAdapter(
                $data,
                $this->get('session'),
                $this->getParameter('database_host'),
                $this->getParameter('database_name'),
                $this->getParameter('database_user'),
                $this->getParameter('database_password')
            );
            $results = $searchAdapter->getFullResults();
            $pager = new Pagerfanta($searchAdapter);
            $pager->setMaxPerPage($data->items);
            $pager->setCurrentPage($data->page);
        }

        return $this->render('search/searchmembers.html.twig', [
            'form' => $form->createView(),
            'pager' => $pager,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
            'results' => $results,
            'showMemberDetails' => true,
        ]);
    }

    /**
     * This method is used on the home screen to allow people interested in BeWelcome to check how many members are
     * available in a location.
     *
     * @Route("/search/map", name="search_map")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function showMapAction(Request $request)
    {
        $pager = false;
        $results = false;

        $searchFormRequest = new SearchFormRequest();
        $form = $this->createForm(SearchFormType::class, $searchFormRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $searchAdapter = new SearchAdapter(
                $data,
                $this->get('session'),
                $this->getParameter('database_host'),
                $this->getParameter('database_name'),
                $this->getParameter('database_user'),
                $this->getParameter('database_password')
            );
            $results = $searchAdapter->getMapResults();
            $pager = new Pagerfanta($searchAdapter);
            $pager->setMaxPerPage($data->items);
            $pager->setCurrentPage($data->page);
        }

        return $this->render('search/searchmembers.html.twig', [
            'form' => $form->createView(),
            'pager' => $pager,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
            'results' => $results,
            'showMemberDetails' => false,
        ]);
    }

    /**
     * @Route("/search/members/ajax", name="search_members_ajax")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function searchGetPageResultsAjax(Request $request)
    {
        if ('POST' !== $request->getMethod()) {
            // JavaScript doesn't work on client
            // redirect to search members
            return $this->redirectToRoute('search_members', $request->query->all());
        }

        $searchFormRequest = SearchFormRequest::fromRequest($request);

        $searchAdapter = new SearchAdapter(
            $searchFormRequest,
            $this->get('session'),
            $this->getParameter('database_host'),
            $this->getParameter('database_name'),
            $this->getParameter('database_user'),
            $this->getParameter('database_password')
        );
        $pager = new Pagerfanta($searchAdapter);
        $pager->setMaxPerPage($searchFormRequest->items);
        $pager->setCurrentPage($searchFormRequest->page);

        return $this->render('member/results.html.twig', [
            'pager' => $pager,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
        ]);
    }
}
