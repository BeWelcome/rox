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

    /**
     * LogsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_model = new \RoxModelBase();
    }

    public function __destruct()
    {
        unset($this->_model);
    }

    private function _getParameters(Request $request) {
        $types = LogsModel::getLogTypes();
        $membername = $request->query->get('membername');
        $logType = $request->query->get('logtype', -1);
        $ipAddress = $request->query->get('ipaddress');
        return [
            'types' => $types,
            'membername' => $membername,
            'logtype' => $logType,
            'ipaddress' => $ipAddress
        ];
    }

    private function _getQuery($parameters) {

        $query = Log::with('member');

        if ($parameters['membername']) {
            $member = Member::where('username', $parameters['membername'])->first();
            if ($member) {
                $query->where('IdMember', $member->id);
            }
        }
        if ($parameters['logtype'] > -1 && $parameters['logtype'] < count($parameters['types'])) {
            $types = $parameters['types'];
            $query->where('Type', $types[$parameters['logtype']]);
        }
        if ($parameters['ipaddress']) {
            $query->where('ipaddress', ip2long($parameters['ipaddress']));
        }
        $query->orderBy('created', 'desc');

        return $query;
    }

    /**
     * @param $currentPage
     * @param $itemsPerPage
     *
     * @return Response
     */
    public function showOverview(Request $request, $currentPage, $itemsPerPage) {
        $first = ($currentPage - 1) * $itemsPerPage;
        $page = new AdminLogsPage($this->router);

        $parameters = $this->_getParameters($request);
        $query = $this->_getQuery($parameters);
        $count = $query->count();
        $logs = $query->skip($first)->take($itemsPerPage)->get();
        $lastPage = ceil($count / $itemsPerPage);
        $page->addParameters(
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'route' => 'admin_logs',
            'routeParams' => ['itemsPerPage' => $itemsPerPage],
            'count' => $count,
            'logs' => $logs
            ];
        $page->setParameters(array_merge($pageParameters, $parameters));
        return new Response($page->render());
    }

    /**
     * @param $ipAddress
     * @param $currentPage
     * @param $itemsPerPage
     *
     * @return Response
     * @internal param Request $request
     */
    public function showIpOverview(Request $request, $ipAddress, $currentPage, $itemsPerPage) {
        $first = ($currentPage - 1) * $itemsPerPage;
        $page = new AdminLogsPage($this->router);

        $parameters = $this->_getParameters($request);
        $parameters['ipaddress'] = $ipAddress;
        $query = $this->_getQuery($parameters);
        $count = $query->count();
        $logs = $query->skip($first)->take($itemsPerPage)->get();
        $lastPage = ceil($count / $itemsPerPage);
        $page->addParameters(
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'route' => 'admin_logs',
            'routeParams' => ['itemsPerPage' => $itemsPerPage],
            'count' => $count,
            'logs' => $logs
        ];
        $page->setParameters(array_merge($pageParameters, $parameters));
        return new Response($page->render());
    }

    /**
     * @param $membername
     * @param $currentPage
     * @param $itemsPerPage
     *
     * @return Response
     * @internal param Request $request
     */
    public function showUsernameOverview(Request $request, $membername, $currentPage, $itemsPerPage) {
        $page = new AdminLogsPage($this->router);
        $first = ($currentPage - 1) * $itemsPerPage;
        $parameters = $this->_getParameters($request);
        $parameters['membername'] = $membername;
        $member = Member::where('username', $membername)->first();
        if ($member) {
            $query = $this->_getQuery($parameters);
            $count = $query->count();
            $logs = $query->skip($first)->take($itemsPerPage)->get();
        } else {
            $logs = [];
            $count = 0;
        }
        $lastPage = ceil($count / $itemsPerPage);
            $page->addParameters(
        $pageParameters = [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'route' => 'admin_logs',
            'routeParams' => ['itemsPerPage' => $itemsPerPage],
            'count' => $count,
            'logs' => $logs
        ];
        $page->setParameters(array_merge($pageParameters, $parameters));
        return new Response($page->render());
    }

    /**
     * @param $type
     * @param $currentPage
     * @param $itemsPerPage
     *
     * @return Response
     */
    public function showTypeOverview(Request $request, $type, $currentPage, $itemsPerPage) {
        $first = ($currentPage - 1) * $itemsPerPage;
        $page = new AdminLogsPage($this->router);

        $parameters = $this->_getParameters($request);
        $parameters['logtype'] = $type;
        $query = $this->_getQuery($parameters);
        $count = $query->count();
        $logs = $query->skip($first)->take($itemsPerPage)->get();
        $lastPage = ceil($count / $itemsPerPage);
        $page->addParameters(
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'route' => 'admin_logs',
            'routeParams' => ['itemsPerPage' => $itemsPerPage],
            'count' => $count,
            'logs' => $logs
        ];
        $page->setParameters(array_merge($pageParameters, $parameters));
        return new Response($page->render());
    }
}