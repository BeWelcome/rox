<?php

namespace App\Controller;

use App\Model\StatisticsModel;
use App\Utilities\SessionSingleton;
use EnvironmentExplorer;
use PException;
use StatsModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StatisticsController extends AboutBaseController
{
    /** @var StatisticsModel */
    private $statisticsModel;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(StatisticsModel $statisticsModel, UrlGeneratorInterface $urlGenerator)
    {
        $this->statisticsModel = $statisticsModel;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/about/statistics", name="about_statistics")
     * @Route("/about/stats", name="stats")
     *
     * @return Response
     */
    public function showAboutStatistics(Request $request)
    {
        return $this->render('about/statistics.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'statistics',
            ],
        ]);
    }

    /**
     * @Route("/stats/members/{period}", name="stats_members",
     *     requirements = {"period" = "weekly|daily"})
     *
     * @param string $period timeframe for data
     *
     * @return JsonResponse
     */
    public function membersData(string $period)
    {
        $membersData = $this->statisticsModel->getMembersData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    /**
     * @Route("/stats/messages/sent/{period}", name="stats_messages_sent",
     *     requirements = {"period" = "weekly|daily"})
     *
     * @param string $period timeframe for data
     *
     * @return JsonResponse
     */
    public function sentMessagesData(string $period)
    {
        $membersData = $this->statisticsModel->getSentMessagesData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    /**
     * @Route("/stats/messages/read/{period}", name="stats_messages_read",
     *     requirements = {"period" = "weekly|daily"})
     *
     * @param string $period timeframe for data
     *
     * @return JsonResponse
     */
    public function readMessagesData(string $period)
    {
        $membersData = $this->statisticsModel->getReadMessagesData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    /**
     * @Route("/stats/requests/sent/{period}", name="stats_requests_sent",
     *     requirements = {"period" = "weekly|daily"})
     *
     * @param string $period timeframe for data
     *
     * @return JsonResponse
     */
    public function sentRequestsData(string $period)
    {
        $membersData = $this->statisticsModel->getSentRequestsData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    /**
     * @Route("/stats/requests/accepted/{period}", name="stats_requests_accepted",
     *     requirements = {"period" = "weekly|daily"})
     *
     * @param string $period timeframe for data
     *
     * @return JsonResponse
     */
    public function acceptedRequestsData(string $period)
    {
        $membersData = $this->statisticsModel->getAcceptedRequestsData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    /**
     * @Route("/stats/invitations/sent/{period}", name="stats_invitations_sent",
     *     requirements = {"period" = "weekly|daily"})
     *
     * @param string $period timeframe for data
     *
     * @return JsonResponse
     */
    public function sentInvitationsData(string $period)
    {
        $membersData = $this->statisticsModel->getSentInvitationsData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    /**
     * @Route("/stats/invitations/accepted/{period}", name="stats_invitations_accepted",
     *     requirements = {"period" = "weekly|daily"})
     *
     * @param string $period timeframe for data
     *
     * @return JsonResponse
     */
    public function acceptedInvitationsData(string $period)
    {
        $membersData = $this->statisticsModel->getAcceptedInvitationsData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    /**
     * @Route("/stats/legs/created/{period}", name="stats_legs_created",
     *     requirements = {"period" = "weekly|daily"})
     *
     * @param string $period timeframe for data
     *
     * @return JsonResponse
     */
    public function legsCreatedData(string $period)
    {
        $membersData = $this->statisticsModel->getLegsCreatedData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    /**
     * @Route("/stats/languages/spoken", name="stats_spoken_languages")
     *
     * @return JsonResponse
     */
    public function languagesData()
    {
        $languagesData = $this->statisticsModel->getLanguagesData();

        $response = new JsonResponse();
        $response->setData($languagesData);

        return $response;
    }

    /**
     * @Route("/stats/languages/preferred", name="stats_preferred_languages")
     *
     * @return JsonResponse
     */
    public function preferredLanguagesData()
    {
        $preferredLanguagesData = $this->statisticsModel->getPreferredLanguagesData();

        $response = new JsonResponse();
        $response->setData($preferredLanguagesData);

        return $response;
    }

    /**
     * @Route("/stats/members/logins", name="stats_members_logins")
     *
     * @return JsonResponse
     */
    public function loginsData()
    {
        $data = $this->statisticsModel->getMembersPerLoginData();

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /**
     * @Route("/stats/members/countries", name="stats_members_per_countries")
     *
     * @return JsonResponse
     */
    public function getMembersPerCountryData()
    {
        $data = $this->statisticsModel->getMembersPerCountryData();

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /**
     * @Route("/stats/data/{type}", name="stats_data",
     *     requirements = {"type" = "alltime|requests|last2month|other"},
     *     defaults={"type": "alltime"})
     *
     * @param mixed $type
     *
     * @return JsonResponse
     */
    public function data($type)
    {
        $data = [];
        switch ($type) {
            case 'other':
                $data = $this->otherData();
                break;
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    private function otherData()
    {
        $this->kickstartSession();
        $statsModel = new StatsModel();
        try {
            $languages = $statsModel->getLanguages();
            $preferredLanguages = $statsModel->getPreferredLanguages();
            $logins = $statsModel->getLastLoginRankGrouped();
            $countries = $statsModel->getMembersPerCountry();
        } catch (PException $e) {
            $logins = [];
            $countries = [];
            $languages = [];
            $preferredLanguages = [];
        }

        return [
            'languages' => $languages,
            'preferred' => $preferredLanguages,
            'countries' => $countries,
            'logins' => $logins,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function kickstartSession()
    {
        // Kick-start the Symfony session. This replaces session_start() in the
        // old code, which is now turned off.
        $session = $this->get('session');
        $session->start();

        // Make sure the Rox classes find this session
        SessionSingleton::createInstance($session);

        // make sure everything's setup for the old code used below
        $environmentExplorer = new EnvironmentExplorer($this->urlGenerator);
        $environmentExplorer->initializeGlobalState(
            $this->getParameter('database_host'),
            $this->getParameter('database_name'),
            $this->getParameter('database_user'),
            $this->getParameter('database_password')
        );
    }
}
