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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 *
 * \todo Check how to split into more focused controllers.
 */
class StatisticsController extends AboutBaseController
{
    public function __construct(
        private readonly StatisticsModel $statisticsModel,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(path: '/about/statistics', name: 'about_statistics')]
    #[Route(path: '/about/stats', name: 'stats')]
    public function showAboutStatistics(Request $request): Response
    {
        return $this->render('about/statistics.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'statistics',
            ],
        ]);
    }

    #[Route(path: '/stats/members/{period}', name: 'stats_members', requirements: ['period' => 'weekly|daily'])]
    public function membersData(string $period): JsonResponse
    {
        $membersData = $this->statisticsModel->getMembersData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    #[Route(path: '/stats/messages/sent/{period}', name: 'stats_messages_sent', requirements: ['period' => 'weekly|daily'])]
    public function sentMessagesData(string $period): JsonResponse
    {
        $membersData = $this->statisticsModel->getSentMessagesData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    #[Route(path: '/stats/messages/read/{period}', name: 'stats_messages_read', requirements: ['period' => 'weekly|daily'])]
    public function readMessagesData(string $period): JsonResponse
    {
        $membersData = $this->statisticsModel->getReadMessagesData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    #[Route(path: '/stats/requests/sent/{period}', name: 'stats_requests_sent', requirements: ['period' => 'weekly|daily'])]
    public function sentRequestsData(string $period): JsonResponse
    {
        $membersData = $this->statisticsModel->getSentRequestsData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    #[Route(path: '/stats/requests/accepted/{period}', name: 'stats_requests_accepted', requirements: ['period' => 'weekly|daily'])]
    public function acceptedRequestsData(string $period): JsonResponse
    {
        $membersData = $this->statisticsModel->getAcceptedRequestsData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    #[Route(path: '/stats/invitations/sent/{period}', name: 'stats_invitations_sent', requirements: ['period' => 'weekly|daily'])]
    public function sentInvitationsData(string $period): JsonResponse
    {
        $membersData = $this->statisticsModel->getSentInvitationsData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    #[Route(path: '/stats/invitations/accepted/{period}', name: 'stats_invitations_accepted', requirements: ['period' => 'weekly|daily'])]
    public function acceptedInvitationsData(string $period): JsonResponse
    {
        $membersData = $this->statisticsModel->getAcceptedInvitationsData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    #[Route(path: '/stats/legs/created/{period}', name: 'stats_legs_created', requirements: ['period' => 'weekly|daily'])]
    public function legsCreatedData(string $period): JsonResponse
    {
        $membersData = $this->statisticsModel->getLegsCreatedData($period);

        $response = new JsonResponse();
        $response->setData($membersData);

        return $response;
    }

    #[Route(path: '/stats/languages/spoken', name: 'stats_spoken_languages')]
    public function languagesData(): JsonResponse
    {
        $languagesData = $this->statisticsModel->getLanguagesData();

        $response = new JsonResponse();
        $response->setData($languagesData);

        return $response;
    }

    #[Route(path: '/stats/languages/preferred', name: 'stats_preferred_languages')]
    public function preferredLanguagesData(): JsonResponse
    {
        $preferredLanguagesData = $this->statisticsModel->getPreferredLanguagesData();

        $response = new JsonResponse();
        $response->setData($preferredLanguagesData);

        return $response;
    }

    #[Route(path: '/stats/members/logins', name: 'stats_members_logins')]
    public function loginsData(): JsonResponse
    {
        $data = $this->statisticsModel->getMembersPerLoginData();

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    #[Route(path: '/stats/members/countries', name: 'stats_members_per_countries')]
    public function getMembersPerCountryData(): JsonResponse
    {
        $data = $this->statisticsModel->getMembersPerCountryData();

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    #[Route(
        path: '/stats/data/{type}',
        name: 'stats_data',
        requirements: ['type' => 'alltime|requests|last2month|other'],
        defaults: ['type' => 'alltime']
    )]
    public function data(string $type): JsonResponse
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

    private function otherData(): array
    {
        $this->kickstartSession();
        $statsModel = new StatsModel();
        try {
            $languages = $statsModel->getLanguages();
            $preferredLanguages = $statsModel->getPreferredLanguages();
            $logins = $statsModel->getLastLoginRankGrouped();
            $countries = $statsModel->getMembersPerCountry();
        } catch (PException) {
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
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    private function kickstartSession(): void
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
