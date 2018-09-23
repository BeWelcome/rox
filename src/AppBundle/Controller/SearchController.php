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

        $searchFormRequest = new SearchFormRequest();
        $form = $this->createForm(SearchFormType::class, $searchFormRequest, [
            'groups' => $member->getGroups(),
            'languages' => $member->getLanguages(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $searchAdapter = new SearchAdapter($this->container, $data);
            $results = $searchAdapter->getFullResults();
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
            $searchAdapter = new SearchAdapter($this->container, $data);
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

        $searchAdapter = new SearchAdapter($this->container, $searchFormRequest);
        $pager = new Pagerfanta($searchAdapter);
        $pager->setMaxPerPage($searchFormRequest->items);
        $pager->setCurrentPage($searchFormRequest->page);

        return $this->render(':member:results.html.twig', [
            'pager' => $pager,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
        ]);
    }
}
