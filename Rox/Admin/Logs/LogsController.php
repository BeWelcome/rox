<?php

namespace Rox\Admin\Logs;

use Rox\Models\Log;
use Rox\Models\Member;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * dashboard controller
 *
 * @package Dashboard
 * @author Amnesiac84
 */
class LogsController extends \RoxControllerBase
{

    /**
     * @var \RoxModelBase
     */
    private $_model;

    public function __construct()
    {
        parent::__construct();
        $this->_model = new \RoxModelBase();
    }

    public function __destruct()
    {
        unset($this->_model);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function showOverview($pageNumber, $itemsPerPage) {
        $first = ($pageNumber - 1) * $itemsPerPage;
        $page = new AdminLogsPage($this->router);

        $query = Log::with('member')->orderBy('created', 'desc');
        $count = $query->count();
        $logs = $query->skip($first)->take($itemsPerPage)->get();
        $lastPage = ceil($count / $itemsPerPage);
        $page->setParameters(
            [
                'currentPage' => $pageNumber,
                'lastPage' => $lastPage,
                'route' => 'admin_logs',
                'routeParams' => ['itemsPerPage' => $itemsPerPage],
                'count' => $count,
                'logs' => $logs
            ]
        );
        return new Response($page->render());
    }

    /**
     * @param $ipAddress
     * @param $currentPage
     * @param $itemsPerPage
     * @return Response
     */
    public function showIpOverview($ipAddress, $currentPage, $itemsPerPage) {
        $first = ($currentPage - 1) * $itemsPerPage;
        $page = new AdminLogsPage($this->router);

        $query = Log::with('member')->where('ipAddress', ip2long($ipAddress))->orderBy('created', 'desc');
        $count = $query->count();
        $logs = $query->skip($first)->take($itemsPerPage)->get();
        $lastPage = ceil($count / $itemsPerPage);
        $page->setParameters(
            [
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'route' => 'admin_logs_ip',
                'routeParams' => ['itemsPerPage' => $itemsPerPage, 'ipAddress' => $ipAddress],
                'count' => $count,
                'logs' => $logs
            ]
        );
        return new Response($page->render());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function showUsernameOverview($username, $currentPage, $itemsPerPage) {
        $member = Member::where('Username', '=', $username)->first();
        if ($member) {
            $first = ($currentPage - 1) * $itemsPerPage;
            $page = new AdminLogsPage($this->router);

            $query = Log::with('member')->where('IdMember', $member->id)->orderBy('created', 'desc');
            $count = $query->count();
            $logs = $query->skip($first)->take($itemsPerPage)->get();
            $lastPage = ceil($count / $itemsPerPage);
            $page->setParameters(
                [
                    'currentPage' => $currentPage,
                    'lastPage' => $lastPage,
                    'route' => 'admin_logs_username',
                    'routeParams' => ['itemsPerPage' => $itemsPerPage, 'username' => $username],
                    'count' => $count,
                    'logs' => $logs
                ]
            );
            return new Response($page->render());
        } else {
            return new Response('Not found', 404);
        }
    }

    /**
     * @return Response
     */
    public function showTypeOverview($type, $currentPage, $itemsPerPage) {
        $first = ($currentPage - 1) * $itemsPerPage;
        $page = new AdminLogsPage($this->router);

        $query = Log::with('member')->where('Type', $type)->orderBy('created', 'desc');
        $count = $query->count();
        $logs = $query->skip($first)->take($itemsPerPage)->get();
        $lastPage = ceil($count / $itemsPerPage);
        $page->setParameters(
            [
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'route' => 'admin_logs_type',
                'routeParams' => ['itemsPerPage' => $itemsPerPage, 'type' => $type],
                'count' => $count,
                'logs' => $logs
            ]
        );
        return new Response($page->render());
    }
}