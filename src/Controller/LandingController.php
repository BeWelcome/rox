<?php

namespace App\Controller;

use App\Doctrine\AccommodationType;
use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\SearchFormType;
use App\Model\CommunityNewsModel;
use App\Model\DonateModel;
use App\Model\LandingModel;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class LandingController extends AbstractController
{
    /**
     * @var LandingModel
     */
    private $landingModel;

    public function __construct(LandingModel $landingModel)
    {
        $this->landingModel = $landingModel;
    }

    /**
     * @param Request $request
     *
     * @Route( "/widget/messages", name="/widget/messages")
     *
     * @return Response
     */
    public function getMessages(Request $request)
    {
        /** @var Member $member */
        $member = $this->getUser();
        $unread = $request->query->get('unread', '0');

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::MESSAGE_AND_REQUEST_FILTER]);
        $memberPreference = $member->getMemberPreference($preference);
        if ('1' === $unread) {
            $memberPreference->setValue('Unread');
        } else {
            $memberPreference->setValue('All');
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($memberPreference);
        $em->flush();

        $messages = $this->landingModel->getMessages($member, $unread, 5);

        $content = $this->render('landing/widget/messages.html.twig', [
            'messages' => $messages,
        ]);

        return $content;
    }

    /**
     * @Route( "/widget/notifications", name="/widget/notifications")
     *
     * @return Response
     */
    public function getNotifications()
    {
        /** @var Member $member */
        $member = $this->getUser();

        $notifications = $this->landingModel->getNotifications($member, 5);

        $content = $this->render('landing/widget/notifications.html.twig', [
            'notifications' => $notifications,
        ]);

        return $content;
    }

    /**
     * @param Request $request
     *
     * @Route( "/widget/threads", name="/widget/threads")
     *
     * @return Response
     */
    public function getThreads(Request $request)
    {
        $groups = $request->query->get('groups', '0');
        $forum = $request->query->get('forum', '0');
        $following = $request->query->get('following');

        /** @var Member $member */
        $member = $this->getUser();
        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
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
        $em = $this->getDoctrine()->getManager();
        $em->persist($memberPreference);
        $em->flush();
        $threads = $this->landingModel->getThreads($member, $groups, $forum, $following, 5);

        return $this->render('landing/widget/forums.html.twig', [
            'threads' => $threads,
        ]);
    }

    /**
     * @Route( "/widget/activities", name="/widget/activities")
     *
     * @throws Exception
     *
     * @return Response
     */
    public function getActivities()
    {
        /** @var Member $member */
        $member = $this->getUser();
        $activities = $this->landingModel->getLocalActivities($member);

        $content = $this->render('landing/widget/activities.html.twig', [
            'activities' => $activities,
        ]);

        return $content;
    }

    /**
     * @Route( "/widget/accommodation", name="/widget/accommodation")
     *
     * @param Request $request
     *
     * @param Environment $twig
     * @return Response
     */
    public function setAccommodationAction(Request $request, Environment $twig)
    {
        $accommodation = $request->request->get('accommodation');

        switch ($accommodation) {
            case AccommodationType::YES:
            case AccommodationType::NO:
                $valid = true;
                break;
            default:
                $valid = false;
        }

        /** @var Member $member */
        $member = $this->getUser();
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
     * @Route("/", name="landingpage")
     *
     * @param CommunityNewsModel $communityNewsModel
     * @param DonateModel        $donateModel
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function indexAction(CommunityNewsModel $communityNewsModel, DonateModel $donateModel)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /** @var Member $member */
        $member = $this->getUser();
        $campaignDetails = $donateModel->getStatForDonations();

        $travellersInArea = $this->landingModel->getTravellersInAreaOfMember($member);

        $latestNews = $communityNewsModel->getLatest();

        $formFactory = $this->get('form.factory');
        // Prepare search form for home location link
        $searchHomeLocationRequest = $this->getSearchHomeLocationRequest($member);
        $searchHomeLocation = $formFactory->createNamed('home', SearchFormType::class, $searchHomeLocationRequest);

        // Prepare small search form
        $searchGotoLocation = $formFactory->createNamed('tiny', SearchFormType::class, new SearchFormRequest($this->getDoctrine()->getManager()));

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::MESSAGE_AND_REQUEST_FILTER]);
        $messageFilter = $member->getMemberPreferenceValue($preference);

        $preference = $preferenceRepository->findOneBy(['codename' => Preference::FORUM_FILTER]);
        $forumFilter = $member->getMemberPreferenceValue($preference);
        $content = $this->render('landing/landing.html.twig', [
                'title' => 'BeWelcome',
                'searchLocation' => $searchHomeLocation->createView(),
                'tinySearch' => $searchGotoLocation->createView(),
                'campaign' => [
                    'year' => $campaignDetails->year,
                    'yearNeeded' => $campaignDetails->YearNeededAmount,
                    'yearDonated' => $campaignDetails->YearDonation,
                ],
                'travellers' => $travellersInArea,
                'communityNews' => $latestNews,
                'messageFilter' => $messageFilter,
                'forumFilter' => $forumFilter,
        ]);

        return $content;
    }

    /**
     * @param Member $member
     *
     * @return SearchFormRequest
     */
    private function getSearchHomeLocationRequest(Member $member)
    {
        $searchHomeRequest = new SearchFormRequest($this->getDoctrine()->getManager());
        $geo = $member->getCity();
        $searchHomeRequest->location = $geo->getName();
        $searchHomeRequest->location_geoname_id = $geo->getGeonameid();
        $searchHomeRequest->location_latitude = $member->getLatitude();
        $searchHomeRequest->location_longitude = $member->getLongitude();
        $searchHomeRequest->accommodation_anytime = true;
        $searchHomeRequest->accommodation_dependonrequest = true;
        $searchHomeRequest->accommodation_neverask = true;

        return $searchHomeRequest;
    }
}
