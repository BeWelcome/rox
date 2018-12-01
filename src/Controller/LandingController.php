<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Preference;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\SearchFormType;
use App\Model\CommunityNewsModel;
use App\Model\DonateModel;
use App\Model\LandingModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @Route( "/widget/messages", name="/widget/messages")
     *
     * @return Response
     */
    public function showMessagesAction(Request $request)
    {
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

        $homeModel = new LandingModel($this->getDoctrine());
        $messages = $homeModel->getMessages($member, $unread, 4);

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
    public function showNotificationsAction()
    {
        $member = $this->getUser();

        $homeModel = new LandingModel($this->getDoctrine());
        $notifications = $homeModel->getNotifications($member, 5);

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
    public function showThreadsAction(Request $request)
    {
        $groups = $request->query->get('groups', '0');
        $forum = $request->query->get('forum', '0');
        $following = $request->query->get('following');

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
        $homeModel = new LandingModel($this->getDoctrine());
        $threads = $homeModel->getThreads($member, $groups, $forum, $following, 4);

        $content = $this->render('landing/widget/forums.html.twig', [
            'threads' => $threads,
        ]);

        return $content;
    }

    /**
     * @Route( "/widget/activities", name="/widget/activities")
     *
     * @return Response
     */
    public function showActivitiesAction()
    {
        $member = $this->getUser();
        $homeModel = new LandingModel($this->getDoctrine());
        $activities = $homeModel->getActivities($member);

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
     * @return Response
     */
    public function setAccommodationAction(Request $request)
    {
        $accommodation = $request->request->get('accommodation');

        switch ($accommodation) {
            case Member::ACC_YES:
            case Member::ACC_MAYBE:
            case Member::ACC_NO:
                $valid = true;
                break;
            default:
                $valid = false;
        }

        $member = $this->getUser();
        if ($valid) {
            $landingModel = new LandingModel($this->getDoctrine());
            $member = $landingModel->updateMemberAccommodation($member, $accommodation);
        }

        // we need raw HTML and no response therefore we do not use the render method of the controller
        $profilePictureWithAccommodation = $this->container->get('twig')->render('landing/widget/profilepicturewithaccommodation.html.twig', [
            'member' => $member,
        ]);

        $accommodationHtml = $this->container->get('twig')->render('landing/widget/accommodation.html.twig', [
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $member = $this->getUser();
        $donationModel = new DonateModel($this->getDoctrine());
        $campaignDetails = $donationModel->getStatForDonations();

        $homeModel = new LandingModel($this->getDoctrine());
        $travellersInArea = $homeModel->getTravellersInAreaOfMember($member);

        $communityNews = new CommunityNewsModel($this->getDoctrine());
        $latestNews = $communityNews->getLatest();

        // Prepare search form for home location link
        $searchHomeLocationRequest = $this->getSearchHomeLocationRequest($member);
        $searchHomeLocation = $this->createForm(SearchFormType::class, $searchHomeLocationRequest);

        // Prepare small search form
        $searchGotoLocation = $this->createForm(SearchFormType::class, new SearchFormRequest());

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
        $searchHomeRequest = new SearchFormRequest();
        $geo = $member->getCity();
        $searchHomeRequest->geoname_id = $geo->getGeonameid();
        $searchHomeRequest->location = $geo->getName();
        $searchHomeRequest->latitude = $member->getLatitude();
        $searchHomeRequest->longitude = $member->getLongitude();
        $searchHomeRequest->accommodation_anytime = true;
        $searchHomeRequest->accommodation_dependonrequest = true;
        $searchHomeRequest->accommodation_neverask = true;

        return $searchHomeRequest;
    }
}
