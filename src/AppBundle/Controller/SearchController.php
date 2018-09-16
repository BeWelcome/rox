<?php

namespace AppBundle\Controller;

use AppBundle\Form\CustomDataClass\SearchFormRequest;
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
        $member = $this->getUser();

        // Check if request contains a standard search form or one of the specialized search form
        // if the latter turn them into a standard form (add missing default fields).
        if ($request->request->has('search_form_base')) {
            $request = $this->updateRequestSearchFormData($request, 'search_form_base');
        }
        if ($request->request->has('search_goto_location_form')) {
            $request = $this->updateRequestSearchFormData($request, 'search_goto_location_form');
        }

        $searchFormRequest = new SearchFormRequest();
        $form = $this->createForm(SearchFormType::class, $searchFormRequest, [
            'groups' => $member->getGroups(),
            'languages' => $member->getLanguages(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $searchAdapter = new SearchAdapter($this->get('service_container'), $data);
            $results = $searchAdapter->getMapResults();
            $pager = new Pagerfanta($searchAdapter);
            $pager->setMaxPerPage($data->items);
            $pager->setCurrentPage($data->page);
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

        $searchAdapter = new SearchAdapter($this->get('service_container'), $searchFormRequest);
        $pager = new Pagerfanta($searchAdapter);
        $pager->setMaxPerPage($searchFormRequest->items);
        $pager->setCurrentPage($searchFormRequest->page);

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
        // Overwrite the CSRF token with the one for the general search form
        $data['_token'] = $this->get('security.csrf.token_manager')->getToken('search_form')->getValue();
        $request->request->remove($formName);
        $request->request->add(['search_form' => $data]);

        return $request;
    }
}
