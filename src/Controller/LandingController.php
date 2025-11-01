<?php

namespace App\Controller;

use App\Doctrine\AccommodationType;
use App\Entity\Activity;
use App\Entity\NewMember as Member;
use App\Entity\Notification;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\SearchFormType;
use App\Form\TripRadiusType;
use App\Model\CommunityNewsModel;
use App\Model\DonateModel;
use App\Model\LandingModel;
use App\Model\TripModel;
use App\Repository\ActivityRepository;
use App\Repository\NotificationRepository;
use App\Repository\SubtripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 *
 * \todo check if this can be changed (landing shows different information and needs this)
 */
class LandingController extends AbstractController
{
    public function __construct(private readonly LandingModel $landingModel, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/widget/conversations', name: '/widget/conversations')]
    public function getConversations(Request $request): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $unread = $request->query->get('unread', '0');

        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::MESSAGE_AND_REQUEST_FILTER]);
        $memberPreference = $member->getMemberPreference($preference);
        if ('1' === $unread) {
            $memberPreference->setValue('Unread');
        } else {
            $memberPreference->setValue('All');
        }

        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        $messages = $this->landingModel->getConversations($member, $unread, 5);

        $content = $this->render('landing/widget/conversations.html.twig', [
            'messages' => $messages,
        ]);

        return $content;
    }

    #[Route(path: '/widget/notifications', name: '/widget/notifications')]
    public function getNotifications(): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $notifications = $this->landingModel->getNotifications($member, 5);

        $content = $this->render('landing/widget/notifications.html.twig', [
            'notifications' => $notifications,
        ]);

        return $content;
    }

    #[Route(path: '/widget/visitors', name: '/widget/visitors')]
    public function getVisitors(Request $request, TripModel $tripModel): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $radius = $request->query->get('radius', -1);
        if (-1 === $radius || !is_numeric($radius)) {
            $radius = $tripModel->getTripsRadius($member);
        } else {
            $tripModel->setTripsRadius($member, $radius);
        }

        $tripLegs = $this->landingModel->getTravellersInAreaOfMember($member, $radius);

        return $this->render('landing/widget/triplegs.html.twig', [
            'legs' => $tripLegs,
            'radius' => $radius,
        ]);
    }

    #[Route(path: '/widget/threads', name: '/widget/threads')]
    public function getThreads(Request $request): Response
    {
        $groups = $request->query->get('groups', '0');
        $forum = $request->query->get('forum', '0');
        $following = $request->query->get('following');

        /** @var Member $member */
        $member = $this->getUser();
        $preferenceRepository = $this->entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::FORUM_FILTER]);
        $memberPreference = $member->getMemberPreference($preference);
        $value = '';
        if ('1' === $groups) {
            $value = 'Groups';
        }
        if ('1' === $forum) {
            if (!empty($value)) {
                $value .= 'And';
            }
            $value .= 'Forums';
        }
        $memberPreference->setValue($value);

        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        $threads = $this->landingModel->getThreads($member, $groups, $forum, $following, 5);

        $preference = $preferenceRepository->findOneBy(['codename' => Preference::FORUM_ORDER_LIST_ASC]);
        $memberPreference = $member->getMemberPreference($preference);
        $ascending = ('Yes' === $memberPreference->getValue());

        return $this->render('landing/widget/forums.html.twig', [
            'threads' => $threads,
            'ascending' => $ascending,
        ]);
    }

    /**
     * @throws Exception
     *
     * @return Response
     */
    #[Route(path: '/widget/activities', name: '/widget/activities')]
    public function getActivities(Request $request)
    {
        /** @var Member $member */
        $member = $this->getUser();
        $online = $request->query->get('online', '0');
        $activities = $this->landingModel->getUpcomingActivities($member, $online);

        $content = $this->render('landing/widget/activities.html.twig', [
            'activities' => $activities,
        ]);

        return $content;
    }

    /**
     * @return Response
     */
    #[Route(path: '/widget/accommodation', name: '/widget/accommodation')]
    public function setAccommodationAction(Request $request, Environment $twig)
    {
        /** @var Member $member */
        $member = $this->getUser();
        $accommodation = $request->request->get('accommodation');

        $valid = (AccommodationType::YES === $accommodation) || (AccommodationType::NO === $accommodation);
        if ($valid) {
            $member = $this->landingModel->updateMemberAccommodation($member, $accommodation);
        }

        // we need raw HTML and no response therefore we do not use the render method of the controller
        $profilePictureWithAccommodation = $twig->render('landing/widget/profilepicturewithaccommodation.html.twig', [
            'member' => $member,
        ]);

        $accommodationHtml = $twig->render('landing/widget/accommodation.html.twig', [
            'member' => $member,
        ]);

        return new JsonResponse([
            'profilePictureWithAccommodation' => $profilePictureWithAccommodation,
            'accommodationHtml' => $accommodationHtml,
        ]);
    }

    /**
     * Shows the landing page.
     *
     * @throws AccessDeniedException
     */
    #[Route(path: '/', name: 'landingpage')]
    public function show(
        CommunityNewsModel $communityNewsModel,
        EntityManagerInterface $entityManager,
        DonateModel $donateModel,
        TripModel $tripModel,
        FormFactoryInterface $formFactory,
    ): Response {
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        /** @var Member $member */
        $member = $this->getUser();
        $campaignDetails = $donateModel->getStatForDonations();

        $latestNews = $communityNewsModel->getLatest();

        // Prepare search form for home location link
        $searchHomeLocationRequest = $this->getSearchHomeLocationRequest($member);
        $searchHomeLocation = $formFactory->createNamed('home', SearchFormType::class, $searchHomeLocationRequest);

        // Prepare small search form
        $searchGotoLocation = $formFactory->createNamed(
            'tiny',
            SearchFormType::class,
            new SearchFormRequest()
        );

        $radius = $tripModel->getTripsRadius($member);
        $radiusForm = $this->createForm(TripRadiusType::class, ['radius' => $radius]);

        $preferenceRepository = $entityManager->getRepository(Preference::class);
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::MESSAGE_AND_REQUEST_FILTER]);
        $messageFilter = $member->getMemberPreferenceValue($preference);

        $preference = $preferenceRepository->findOneBy(['codename' => Preference::FORUM_FILTER]);
        $forumFilter = $member->getMemberPreferenceValue($preference);

        $preference = $preferenceRepository->findOneBy(['codename' => Preference::SHOW_ONLINE_ACTIVITIES]);
        $onlineActivities = ('Yes' === $member->getMemberPreferenceValue($preference)) ? 1 : 0;

        $content = $this->render('landing/landing.html.twig', [
            'title' => 'BeWelcome',
            'searchLocation' => $searchHomeLocation->createView(),
            'tinySearch' => $searchGotoLocation->createView(),
            'campaign' => [
                'year' => $campaignDetails['year'],
                'yearNeeded' => $campaignDetails['YearNeededAmount'],
                'yearDonated' => $campaignDetails['YearDonation'],
            ],
            'radiusForm' => $radiusForm->createView(),
            'communityNews' => $latestNews,
            'messageFilter' => $messageFilter,
            'forumFilter' => $forumFilter,
            'onlineActivities' => $onlineActivities,
            'notificationCount' => $this->getUncheckedNotificationsCount($member),
            'visitorsCount' => $this->getVisitorsCount($tripModel, $member),
            'activityCount' => $this->getUpcomingAroundLocationCount($member, $onlineActivities),
        ]);

        return $content;
    }

    protected function getUncheckedNotificationsCount(Member $member): int
    {
        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->entityManager->getRepository(Notification::class);

        return $notificationRepository->getUncheckedNotificationsCount($member);
    }

    protected function getVisitorsCount(TripModel $tripModel, Member $member): int
    {
        $radius = $tripModel->getTripsRadius($member);

        /** @var SubtripRepository $subtripRepository */
        $subtripRepository = $this->entityManager->getRepository(Subtrip::class);

        $visitorsCount = $subtripRepository->getVisitorsCount($member, $radius);

        return $visitorsCount;
    }

    private function getUpcomingAroundLocationCount(Member $member, bool $showOnlineActivities): int
    {
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->entityManager->getRepository(Activity::class);

        return $activityRepository->getUpcomingAroundLocationCount($member, $showOnlineActivities);
    }

    private function getSearchHomeLocationRequest(Member $member): SearchFormRequest
    {
        $searchHomeRequest = new SearchFormRequest();
        $geo = $member->getCity();
        if (null !== $geo) {
            $searchHomeRequest->location = $geo->getName();
            $searchHomeRequest->location_geoname_id = $geo->getGeonameId();
            $searchHomeRequest->location_latitude = $member->getLatitude();
            $searchHomeRequest->location_longitude = $member->getLongitude();
            $searchHomeRequest->accommodation_anytime = true;
            $searchHomeRequest->accommodation_neverask = true;
        }

        return $searchHomeRequest;
    }
}
