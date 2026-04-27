<?php

namespace App\Controller;

use AnthonyMartin\GeoLocation\GeoPoint;
use App\Doctrine\GroupMembershipStatusType;
use App\Entity\Group;
use App\Entity\Member;
use App\Entity\Preference;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\MapSearchFormType;
use App\Form\SearchFormType;
use App\Pagerfanta\SearchAdapter;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use TypeError;

/**
 * Handles all search-related requests for members and locations.
 */
class SearchController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/search/members', name: 'search_members')]
    public function searchMembers(
        Request $request,
        MemberRepository $memberRepository,
    ): Response {
        $username = $request->query->get('username', '');
        $members = null;
        $activeTab = 2; // Default to 'username' tab

        if ('' !== $username) {
            $username = trim($username);

            // First try an exact match (or email)
            $user = $memberRepository->loadUserByIdentifier($username);

            if ($user) {
                return $this->redirectToRoute('member_profile', ['username' => $user->getUsername()]);
            }

            // If not found, try a wildcard search
            $members = $memberRepository->findByProfileInfoStartsWith($username);
            $activeTab = 1;

            if (1 === \count($members)) {
                return $this->redirectToRoute('member_profile', ['username' => $members[0]->getUsername()]);
            }
        }

        return $this->render('search/searchmembers.html.twig', [
            'members' => $members,
            'username' => $username,
            'active_tab' => $activeTab,
        ]);
    }

    #[Route(path: '/search/locations', name: 'search_locations')]
    public function searchLocations(
        Request $request,
        MemberRepository $memberRepository,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
    ): Response {
        $pager = null;
        $results = null;

        /** @var Member $member */
        $member = $this->getUser();

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);

        /** @var Preference $showMapPreference */
        $showMapPreference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_MAP]);
        $showMap = $member->getMemberPreferenceValue($showMapPreference);

        /** @var Preference $showOptionsPreference */
        $showOptionsPreference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_SEARCH_OPTIONS]);
        $showOptions = $member->getMemberPreferenceValue($showOptionsPreference);

        /** @var Preference $storedSearchFilter */
        $searchOptionsPreference = $preferenceRepository->findOneBy(['codename' => Preference::SEARCH_OPTIONS]);
        $memberSearchOptionsPreference = $member->getMemberPreference($searchOptionsPreference);
        $searchOptions = $memberSearchOptionsPreference->getValue();

        $searchFormRequest = new SearchFormRequest();
        if ('' !== $searchOptions) {
            try {
                $searchFormRequest = unserialize($searchOptions);
            } catch (TypeError) {
                // In case the format is corrupted just reset form. Next successful search will write the correct format
            }
        }
        $searchFormRequest->show_map = ('Yes' === $showMap);
        $searchFormRequest->show_options = ('Yes' === $showOptions);

        // Fetch choices without instantiating entities to prevent "dirty entity" bugs on flush
        $groupChoices = [];
        $groupData = $this->entityManager->createQuery(
            'SELECT g.id, g.name 
             FROM App\Entity\GroupMembership gm 
             JOIN gm.group g 
             WHERE gm.member = :member AND gm.status = :status AND g.approved = :approved'
        )
        ->setParameter('member', $member)
        ->setParameter('status', GroupMembershipStatusType::CURRENT_MEMBER)
        ->setParameter('approved', Group::APPROVED)
        ->getArrayResult();

        foreach ($groupData as $row) {
            $groupChoices[$row['name']] = $row['id'];
        }

        $languageChoices = [];
        $languageData = $this->entityManager->createQuery(
            'SELECT l.shortCode 
             FROM App\Entity\MemberLanguageLevel mll 
             JOIN mll.language l 
             WHERE mll.member = :member'
        )
        ->setParameter('member', $member)
        ->getArrayResult();

        foreach ($languageData as $row) {
            $code = $row['shortCode'];
            $languageChoices['lang_' . strtolower($code)] = $code;
        }

        $search = $formFactory->createNamed('search', SearchFormType::class, $searchFormRequest, [
            'group_choices' => $groupChoices,
            'language_choices' => $languageChoices,
            'search_options' => $searchOptions,
        ]);
        $search->handleRequest($request);

        $mapMembers = null;
        if ($search->isSubmitted() && $search->isValid()) {
            $searchRequest = $search->getData();

            // Calculate bounding box AFTER form processing
            if (
                null !== $searchRequest->location_geoname_id
                && 1 !== $searchRequest->location_admin_unit
                && -1 !== $searchRequest->distance
            ) {
                $distance = (int) $searchRequest->distance;
                if (-1 === $distance) {
                    $distance = 100;
                }

                $center = new GeoPoint(
                    (float) $searchRequest->location_latitude,
                    (float) $searchRequest->location_longitude
                );
                $boundingBox = $center->boundingBox($distance, 'km');

                $searchRequest->min_latitude = $boundingBox->getMinLatitude();
                $searchRequest->min_longitude = $boundingBox->getMinLongitude();
                $searchRequest->max_latitude = $boundingBox->getMaxLatitude();
                $searchRequest->max_longitude = $boundingBox->getMaxLongitude();
            }

            // Update preferences based on submitted form
            $memberShowMapPreference = $member->getMemberPreference($showMapPreference);
            $memberShowMapPreference->setValue($searchRequest->show_map ? 'Yes' : 'No');
            $memberShowOptionsPreference = $member->getMemberPreference($showOptionsPreference);
            $memberShowOptionsPreference->setValue($searchRequest->show_options ? 'Yes' : 'No');
            $this->entityManager->persist($memberShowMapPreference);
            $this->entityManager->persist($memberShowOptionsPreference);
            $this->entityManager->flush();

            $searchAdapter = new SearchAdapter($searchFormRequest, $this->entityManager, $member);
            $pager = new Pagerfanta($searchAdapter);
            $pager->setMaxPerPage($searchFormRequest->items > 0 ? $searchFormRequest->items : 20);
            $pager->setCurrentPage($searchFormRequest->page > 0 ? $searchFormRequest->page : 1);

            // Fetch the slice manually for the view to avoid breaking BC in twig immediately
            // Better to update twig to iterate over $pager directly, but keeping it safe for now.
            // $mapMembers = $searchAdapter->getMapResults();
        }

        return $this->render('search/searchlocations.html.twig', [
            'pager' => $pager,
            'form' => $search->createView(), // Changed to createView() as good practice for Symfony 8
            'members' => $pager,
            'map_members' => $mapMembers,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
            'searchParams' => $searchFormRequest, // Pass DTO for display logic
        ]);
    }

    /**
     * This method is used on the home screen to allow people interested in BeWelcome to check how many members are
     * available in a location.
     *
     * @return Response|RedirectResponse
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    #[Route(path: '/search/map', name: 'search_map')]
    public function searchOnMap(Request $request, TranslatorInterface $translator, MemberRepository $memberRepository): Response
    {
        // do not allow access to this page when logged in, redirect to /search/locations
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('search_locations');
        }

        $results = null;

        $form = $this->createForm(MapSearchFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $searchFormRequest = new SearchFormRequest();
            $searchFormRequest->page = 1;
            $searchFormRequest->location = $data['location'];
            $searchFormRequest->location_geoname_id = $data['location_geoname_id'];
            $searchFormRequest->location_latitude = $data['location_latitude'];
            $searchFormRequest->location_longitude = $data['location_longitude'];
            $searchFormRequest->accommodation_anytime = true;
            $searchFormRequest->accommodation_neverask = true;
            $searchFormRequest->has_profile_picture = false;
            $searchFormRequest->has_about_me = false;
            $searchFormRequest->has_comments = false;
            $searchFormRequest->last_active = 2400;
            $searchFormRequest->distance = 100;

            $searchAdapter = new SearchAdapter($searchFormRequest, $this->entityManager, $memberRepository, null);
            $results = $searchAdapter->getMapResults();
        }

        return $this->render('search/searchmap.html.twig', [
            'form' => $form->createView(),
            'map' => true,
            'results' => $results,
        ]);
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    #[Route(path: '/search/locations/ajax', name: 'search_members_ajax')]
    public function searchGetPageResultsAjax(
        Request $request,
        TranslatorInterface $translator,
        MemberRepository $memberRepository,
    ): Response {
        if ('POST' !== $request->getMethod()) {
            // JavaScript doesn't work on client
            // redirect to search members
            return $this->redirectToRoute('search_locations', $request->query->all());
        }

        $searchFormRequest = new SearchFormRequest();

        /** @var Member $member */
        $member = $this->getUser();

        $searchAdapter = new SearchAdapter($searchFormRequest, $this->entityManager, $memberRepository, $member);
        $pager = new Pagerfanta($searchAdapter);
        $pager->setMaxPerPage($searchFormRequest->items > 0 ? $searchFormRequest->items : 20);
        $pager->setCurrentPage($request->get('page', 1));

        return $this->render('member/results.html.twig', [
            'pager' => $pager,
            'routeName' => 'search_members_ajax',
            'routeParams' => $request->query->all(),
        ]);
    }
}
