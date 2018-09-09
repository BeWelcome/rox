<?php

namespace AppBundle\Controller;

use AppBundle\Form\SearchFormType;
use AppBundle\Pagerfanta\SearchAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
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
        // Check if request contains a standard search form or one of the specialized search form
        // if the latter turn them into a standard form (add missing default fields).
        if ($request->request->has('search_home_location_form')) {
            $request = $this->updateRequestSearchFormData($request, 'search_home_location_form');
        }
        if ($request->request->has('search_goto_location_form')) {
            $request = $this->updateRequestSearchFormData($request, 'search_goto_location_form');
        }

        $form = $this->createForm(SearchFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page = $request->query->get('page', 1);
            if ('' === $page) {
                $page = 1;
            }
            $searchAdapter = new SearchAdapter($this->get('service_container'), $form->getData());
            $results = $searchAdapter->getMapResults();
            $pager = new Pagerfanta($searchAdapter);
            $pager->setCurrentPage($page);
        }

        return $this->render(':search:searchmembers.html.twig', [
            'form' => $form->createView(),
            'pager' => $pager,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
            'results' => $results,
        ]);
    }

    /**
     * @Route("/search/members/ajax", name="search_members_ajax")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchGetPageResultsAjax(Request $request)
    {
        if ('POST' !== $request->getMethod()) {
            // JavaScript doesn't work on client
            // redirect to search members
            return $this->redirectToRoute('search_members', $request->query->all());
        }

        $page = $request->query->get('page', 1);
        $form = $this->createForm(SearchFormType::class, $request->query->all(), ['csrf_protection' => false]);

        $searchAdapter = new SearchAdapter($this->get('service_container'), $form->getData());
        $pager = new Pagerfanta($searchAdapter);
        $pager->setCurrentPage($page);

        return $this->render(':member:results.html.twig', [
            'pager' => $pager,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
        ]);
    }

    /**
     * @param Request $request
     * @param $formName
     *
     * @return Request
     */
    private function updateRequestSearchFormData(Request $request, $formName)
    {
        $data = $request->request->get($formName);
        $data['search_accommodation_anytime'] = true;
        $data['search_accommodation_dependonrequest'] = true;
        $data['search_accommodation_neverask'] = true;
        $data['search_can_host'] = 1;
        $data['search_distance'] = 5;

        $request->request->remove($formName);
        $request->request->add(['search_form' => $data]);

        return $request;
    }
}
