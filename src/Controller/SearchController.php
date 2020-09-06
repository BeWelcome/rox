<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Preference;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\SearchFormType;
use App\Pagerfanta\SearchAdapter;
use App\Repository\MemberRepository;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/members", name="search_members")
     *
     * @return Response
     */
    public function searchMembers(Request $request)
    {
        $members = null;
        $memberSearch = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'label' => 'label.username',
                'attr' => [
                    'class' => 'member-autocomplete-start',
                ],
                'help' => 'help.username.auto.complete',
            ])
            ->add('search', SubmitType::class, [
                'label' => 'label.search.username',
            ])
            ->getForm()
        ;
        $memberSearch->handleRequest($request);
        if ($memberSearch->isSubmitted() && $memberSearch->isValid()) {
            $data = $memberSearch->getData();
            $username = $data['username'];
            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            $members = $memberRepository->findByProfileInfoStartsWith($username);
        }

        return $this->render('search/searchmembers.html.twig', [
            'form' => $memberSearch->createView(),
            'members' => $members,
        ]);
    }

    /**
     * @Route("/search/locations", name="search_locations")
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function searchLocations(Request $request, TranslatorInterface $translator)
    {
        $pager = false;
        $results = false;
        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $showMapPreference */
        $showMapPreference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_MAP]);
        $showMap = $member->getMemberPreferenceValue($showMapPreference);
        /** @var Preference $showOptionsPreference */
        $showOptionsPreference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_SEARCH_OPTIONS]);
        $showOptions = $member->getMemberPreferenceValue($showOptionsPreference);

        $searchFormRequest = SearchFormRequest::fromRequest($request, $this->getDoctrine()->getManager());
        $searchFormRequest->show_map = ('Yes' === $showMap);
        $searchFormRequest->show_options = ('Yes' === $showOptions);

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
            $memberShowMapPreference = $member->getMemberPreference($showMapPreference);
            $memberShowMapPreference->setValue($data->show_map ? 'Yes' : 'No');
            $memberShowOptionsPreference = $member->getMemberPreference($showOptionsPreference);
            $memberShowOptionsPreference->setValue($data->show_map ? 'Yes' : 'No');
            $em = $this->getDoctrine()->getManager();
            $em->persist($memberShowMapPreference);
            $em->persist($memberShowOptionsPreference);
            $em->flush();

            $searchAdapter = new SearchAdapter(
                $data,
                $this->get('session'),
                $this->getParameter('database_host'),
                $this->getParameter('database_name'),
                $this->getParameter('database_user'),
                $this->getParameter('database_password'),
                $em,
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
        } elseif ($tinyIsSubmitted) {
            // The user probably clicked on 'go' to fast on the landing page
            // so set the entered location into the search location field and just show the form
            $viewData = $tiny->getViewData();
            $search->get('location')->submit($viewData->location);
        }

        return $this->render('search/searchlocations.html.twig', [
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
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function showMapAction(Request $request, TranslatorInterface $translator)
    {
        $pager = false;
        $results = false;

        $searchFormRequest = SearchFormRequest::fromRequest($request, $this->getDoctrine()->getManager());

        $formFactory = $this->get('form.factory');
        $form = $formFactory->createNamed('map', SearchFormType::class, $searchFormRequest);
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

        return $this->render('search/searchlocations.html.twig', [
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
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function searchGetPageResultsAjax(Request $request, TranslatorInterface $translator)
    {
        if ('POST' !== $request->getMethod()) {
            // JavaScript doesn't work on client
            // redirect to search members
            return $this->redirectToRoute('search_locations', $request->query->all());
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
