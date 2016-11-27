<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Rox\CommunityNews\Model\CommunityNews;
use Rox\Main\Home\HomeModel as HomeService;
use Rox\Start\Form\SearchGotoLocationFormType;
use Rox\Start\Form\SearchHomeLocationFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LandingController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showMessagesAction(Request $request)
    {
        $all = $request->query->get('all');
        $unread = $request->query->get('unread');

        $member = $this->getUser();

        $homeService = new HomeService();
        $messages = $homeService->getMessages($member, $all, $unread, 4);

        $content = $this->render(':landing/widget:messages.html.twig', [
            'messages' => $messages,
        ]);

        return new Response($content);
    }

    public function showNotificationsAction()
    {
        $member = $this->getUser();

        $homeService = new HomeService();
        $notifications = $homeService->getNotifications($member, 5);

        $content = $this->render(':landing/widget:notifications.html.twig', [
            'notifications' => $notifications,
        ]);

        return new Response($content);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showThreadsAction(Request $request)
    {
        $groups = $request->query->get('groups');
        $forum = $request->query->get('forum');
        $following = $request->query->get('following');

        $member = $this->getUser();
        $homeService = new HomeService();
        $threads = $homeService->getThreads($member, $groups, $forum, $following, 4);

        $content = $this->render(':landing/widget:forums.html.twig', [
            'threads' => $threads,
        ]);

        return new Response($content);
    }

    public function showActivitiesAction()
    {
        $member = $this->getUser();
        $homeService = new HomeService();
        $activities = $homeService->getActivities($member, 4);

        $content = $this->render(':landing/widget:activities.html.twig', [
            'activities' => $activities,
        ]);

        return new Response($content);
    }

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
            $member->Accomodation = $accommodation;
            $member->save();
        }

        $profilePictureWithAccommodation = $this->render('@start/widget/profilepicturewithaccommodation.html.twig', [
            'member' => $member,
        ]);

        $accommodationHtml = $this->render('@start/widget/accommodation.html.twig', [
            'member' => $member,
        ]);

        return new JsonResponse([
            'profilePictureWithAccommodation' => $profilePictureWithAccommodation,
            'accommodationHtml' => $accommodationHtml,

        ]);
    }

    /**
     * @param Member $member
     * @return array
     */
    private function getSearchHomeLocationData(Member $member)
    {
        $data['search_geoname_id'] = $member->getIdcity();
        $geo = new \Geo($member->getIdcity());
        $data['search'] = $geo->getName();
        $data['search_latitude'] = $member->getLatitude();
        $data['search_longitude'] = $member->getLongitude();
        return $data;
    }

    /**
     * Shows the landing page
     *
     * @Route("/home", name="landingpage")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $homeService = new HomeService();
        $donationCampaign = $homeService->getDonationCampaignDetails();
        $member = $this->getUser();

        $travellersInArea = $homeService->getTravellersInAreaOfMember($member);

        $communityNews = new CommunityNews();
        $latestNews = $communityNews->getLatest();

        // Prepare search form for home location link
        $data = $this->getSearchHomeLocationData($member);
        $searchHomeLocation = $this->createForm(SearchHomeLocationFormType::class, $data);

        // Prepare small search form
        $searchGotoLocation = $this->createForm(SearchGotoLocationFormType::class);

        $content = $this->render(':landing:landing.html.twig', [
                'language' => $this->getParameter('locale'),
                'title' => 'BeWelcome',
                'my_member' => $this->getUser(),
                'searchLocation' => $searchHomeLocation->createView(),
                'tinySearch' => $searchGotoLocation->createView(),
                'campaign' => $donationCampaign,
                'travellers' => $travellersInArea,
                'communityNews' => $latestNews,
        ]);

        return new Response($content->getContent());
    }

}
