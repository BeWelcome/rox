<?php

/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 28.06.2018
 * Time: 19:08.
 */

namespace App\Controller;

use App\Model\StatisticsModel;
use App\Utilities\SessionSingleton;
use EnvironmentExplorer;
use PException;
use StatsModel;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function showAboutStatistics()
    {
        $statistics = [
            'members' => [
                'headline' => 'members',
                'route' => 'stats_members',
            ],
            'sent_messages' => [
                'headline' => 'sent_messages',
                'route' => 'stats_messages_sent',
            ],
            'read_messages' => [
                'headline' => 'read_messages',
                'route' => 'stats_messages_read',
            ],
            'sent_requests' => [
                'headline' => 'sent_requests',
                'route' => 'stats_requests_sent',
            ],
            'accepted_requests' => [
                'headline' => 'accepted_requests',
                'route' => 'stats_requests_accepted',
            ],
        ];

        return $this->render('about/statistics.html.twig', [
            'statistics' => $statistics,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
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
