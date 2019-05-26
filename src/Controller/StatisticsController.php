<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 28.06.2018
 * Time: 19:08.
 */

namespace App\Controller;

use App\Utilities\SessionSingleton;
use EnvironmentExplorer;
use StatsModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    /**
     * @Route("/stats/data/{type}", name="stats_data",
     *     requirements = {"type" = "alltime|last2month|other"},
     *     defaults={"type": "alltime"})
     *
     * @param mixed $type
     *
     * @return JsonResponse
     */
    public function membersDataAction($type)
    {
        $data = [];
        switch ($type) {
            case 'alltime':
                $data = $this->getDataAllTime();
                break;
            case 'last2month':
                $data = $this->getDataLast2Months();
                break;
            case 'other':
                $data = $this->otherDataAction();
                break;
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
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
        $environmentExplorer = new EnvironmentExplorer();
        $environmentExplorer->initializeGlobalState(
            $this->getParameter('database_host'),
            $this->getParameter('database_name'),
            $this->getParameter('database_user'),
            $this->getParameter('database_password')
        );
    }

    private function prepareStatisticsData($statistics)
    {
        // get all values from stats table
        $i = 0;
        $labels = [];
        $members = [];
        $newMembers = [];
        $newMembersPercent = [];
        $membersLoggedIn = [];
        $membersLoggedInPercent = [];
        $membersWithPositiveComments = [];
        $messageSent = [];
        $messageRead = [];
        foreach ($statistics as $val) {
            $members[$i] = $val->NbActiveMembers;
            if (isset($val->week)) {
                $yearWeek = strtotime(substr($val->week, 0, 4).'-W'.substr($val->week, 4, 2).'-1');
                $labels[] = date('Y-m-d', $yearWeek);
            } else {
                $labels[] = date('Y-m-d', strtotime('-'.(60 - $i).'days'));
            }
            if (0 === $i) {
                $newMembers[$i] = 0;
            } else {
                $newMembers[$i] = $members[$i] - $members[$i - 1];
            }
            if (0 === $i) {
                $newMembersPercent[$i] = 0;
            } else {
                if (0 === $members[$i]) {
                    $newMembersPercent[$i] = 0;
                } else {
                    $newMembersPercent[$i] = $newMembers[$i] / $members[$i] * 100;
                }
            }
            $messageSent[$i] = $val->NbMessageSent;
            $messageRead[$i] = $val->NbMessageRead;
            $membersWithPositiveComments[$i] = $val->NbMemberWithOneTrust;
            $membersLoggedIn[$i] = $val->NbMemberWhoLoggedToday;
            if (0 === $members[$i]) {
                $membersLoggedInPercent[$i] = 0;
            } else {
                $membersLoggedInPercent[$i] = $membersLoggedIn[$i] / $members[$i] * 100;
            }
            ++$i;
        }

        return [
            'labels' => $labels,
            'statistics' => [
                'members' => $members,
                'newMembers' => $newMembers,
                'newMembersPercent' => $newMembersPercent,
                'membersLoggedIn' => $membersLoggedIn,
                'newMembersLoggedInPercent' => $membersLoggedInPercent,
                'membersWithPositiveComments' => $membersWithPositiveComments,
                'messageSent' => $messageSent,
                'messageRead' => $messageRead,
            ],
        ];
    }

    private function getDataAllTime()
    {
        $this->kickstartSession();
        $statsModel = new StatsModel();
        $statsAll = $statsModel->getStatsLogAll();

        return $this->prepareStatisticsData($statsAll);
    }

    private function getDataLast2Months()
    {
        $this->kickstartSession();
        $statsModel = new StatsModel();
        $statsLast2Months = $statsModel->getStatsLog2Months();

        return $this->prepareStatisticsData($statsLast2Months);
    }

    private function otherDataAction()
    {
        $this->kickstartSession();
        $statsModel = new StatsModel();
        $logins = [];
        $countries = [];
        $languages = [];
        $preferredLanguages = [];
        try {
            $languages = $statsModel->getLanguages();
            $preferredLanguages = $statsModel->getPreferredLanguages();
            $logins = $statsModel->getLastLoginRankGrouped();
            $countries = $statsModel->getMembersPerCountry();
        } catch (\PException $e) {
        }

        return [
            'languages' => $languages,
            'preferred' => $preferredLanguages,
            'countries' => $countries,
            'logins' => $logins,
        ];
    }
}
