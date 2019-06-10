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
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/members", name="search_members")
     *
     * @param Request             $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function searchAction(Request $request, TranslatorInterface $translator)
    {
        $pager = false;
        $results = false;
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_MAP]);
        $showMap = $member->getMemberPreferenceValue($preference);

        $searchFormRequest = SearchFormRequest::fromRequest($request, $this->getDoctrine()->getManager());
        $searchFormRequest->showmap = ('Yes' === $showMap) ? true : false;

        // There are three different forms that might end up on this page
        $formFactory = $this->get('form.factory');
        $tiny = $formFactory->createNamed('tiny', SearchFormType::class, $searchFormRequest);
        $home = $formFactory->createNamed('home', SearchFormType::class, $searchFormRequest);
        $search = $formFactory->createNamed('search', SearchFormType::class, $searchFormRequest, [
            'groups' => $member->getGroups(),
            'languages' => $member->getLanguages(),
        ]);

        // Check which form was used to get here
        $tiny->handleRequest($request);
        $tinyIsSubmitted = $tiny->isSubmitted();
        $tinyIsValid = ($tinyIsSubmitted && $tiny->isValid());

        $home->handleRequest($request);
        $homeIsSubmitted = $home->isSubmitted();
        $homeIsValid = ($homeIsSubmitted && $home->isValid());

        $search->handleRequest($request);
        $searchIsSubmitted = $search->isSubmitted();
        $searchIsValid = ($searchIsSubmitted && $search->isValid());

        if ($tinyIsValid || $homeIsValid || $searchIsValid) {
            $data = null;
            /* @var SearchFormRequest $data */
            if ($tinyIsValid) {
                $data = $tiny->getData();
            }
            if ($homeIsValid) {
                $data = $home->getData();
            }
            if ($searchIsValid) {
                $data = $search->getData();
            }
            $memberPreference = $member->getMemberPreference($preference);
            if ($data->showmap) {
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
                $this->getParameter('database_password'),
                $this->getDoctrine()->getManager(),
                $translator
            );
            $results = $searchAdapter->getFullResults();
            $pager = new Pagerfanta($searchAdapter);
            $pager->setMaxPerPage($data->items);
            $pager->setCurrentPage($request->get('page', 1));
            if (!$searchIsValid) {
                // only set data if the form wasn't submitted from search_members
                $search->setData($data);
            }
        } else {
            if ($tinyIsSubmitted) {
                // The user probably clicked on 'go' to fast on the landing page
                // so set the entered location into the search location field and just show the form
                $viewData = $tiny->getViewData();
                $search->get('location')->submit($viewData->location);
            }
        }

        return $this->render('search/searchmembers.html.twig', [
            'form' => $search->createView(),
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
     * @param Request             $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function showMapAction(Request $request, TranslatorInterface $translator)
    {
        $pager = false;
        $results = false;

        $searchFormRequest = SearchFormRequest::fromRequest($request, $this->getDoctrine()->getManager());
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
                $this->getParameter('database_password'),
                $this->getDoctrine()->getManager(),
                $translator
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
     * @param Request             $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function searchGetPageResultsAjax(Request $request, TranslatorInterface $translator)
    {
        if ('POST' !== $request->getMethod()) {
            // JavaScript doesn't work on client
            // redirect to search members
            return $this->redirectToRoute('search_members', $request->query->all());
        }

        $searchFormRequest = SearchFormRequest::fromRequest($request, $this->getDoctrine()->getManager());

        $searchAdapter = new SearchAdapter(
            $searchFormRequest,
            $this->get('session'),
            $this->getParameter('database_host'),
            $this->getParameter('database_name'),
            $this->getParameter('database_user'),
            $this->getParameter('database_password'),
            $this->getDoctrine()->getManager(),
            $translator
        );
        $pager = new Pagerfanta($searchAdapter);
        $pager->setMaxPerPage($searchFormRequest->items);
        $pager->setCurrentPage($request->get('page', 1));

        return $this->render('member/results.html.twig', [
            'pager' => $pager,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
        ]);
    }
}
