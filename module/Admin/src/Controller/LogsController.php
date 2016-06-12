<?php

namespace Rox\Admin\Controller;

use Illuminate\Database\Query\Builder;
use Rox\Admin\Service\LogService;
use Rox\Member\Repository\MemberRepositoryInterface;
use Rox\Models\Log;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * dashboard controller
 *
 * @package Dashboard
 * @author Amnesiac84
 */
class LogsController
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var LogService
     */
    protected $logService;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    public function __construct(
        EngineInterface $engine,
        LogService $logService,
        MemberRepositoryInterface $memberRepository
    ) {
        $this->engine = $engine;
        $this->logService = $logService;
        $this->memberRepository = $memberRepository;
    }

    /**
     * @param $currentPage
     * @param $itemsPerPage
     *
     * @return Response
     */
    public function showOverview(Request $request, $currentPage, $itemsPerPage)
    {
        $first = ($currentPage - 1) * $itemsPerPage;

        $parameters = $this->getParameters($request);

        $query = $this->getQuery($parameters);

        $count = $query->count();

        $logs = $query->skip($first)->take($itemsPerPage)->get();

        $lastPage = ceil($count / $itemsPerPage);

        $params = $parameters + [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'route' => 'admin_logs',
            'routeParams' => ['itemsPerPage' => $itemsPerPage],
            'count' => $count,
            'logs' => $logs,
        ];

        $content = $this->engine->render('@admin/logs/logs.html.twig', $params);

        return new Response($content);
    }

    /**
     * @param $ipAddress
     * @param $currentPage
     * @param $itemsPerPage
     *
     * @return Response
     * @internal param Request $request
     */
    public function showIpOverview(Request $request, $ipAddress, $currentPage, $itemsPerPage)
    {
        $first = ($currentPage - 1) * $itemsPerPage;
        $page = new AdminLogsPage($this->getRouter());

        $parameters = $this->getParameters($request);
        $parameters['ipaddress'] = $ipAddress;
        $query = $this->getQuery($parameters);
        $count = $query->count();
        $logs = $query->skip($first)->take($itemsPerPage)->get();
        $lastPage = ceil($count / $itemsPerPage);
        $page->addParameters([
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'route' => 'admin_logs',
            'routeParams' => ['itemsPerPage' => $itemsPerPage],
            'count' => $count,
            'logs' => $logs,
        ]);
        return new Response($page->render());
    }

    /**
     * @param Request $request
     * @param $membername
     * @param $currentPage
     * @param $itemsPerPage
     *
     * @return Response
     */
    public function showUsernameOverview(Request $request, $membername, $currentPage, $itemsPerPage)
    {
        $first = ($currentPage - 1) * $itemsPerPage;

        $parameters = $this->getParameters($request);

        $parameters['membername'] = $membername;

        $member = $this->memberRepository->getByUsername($membername);

        $logs = [];
        $count = 0;

        if ($member) {
            $query = $this->getQuery($parameters);
            $count = $query->count();
            $logs = $query->skip($first)->take($itemsPerPage)->get();
        }

        $lastPage = ceil($count / $itemsPerPage);

        $params = $parameters + [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'route' => 'admin_logs',
            'routeParams' => ['itemsPerPage' => $itemsPerPage],
            'count' => $count,
            'logs' => $logs,
        ];

        $content = $this->engine->render('@admin/logs/logs.html.twig', $params);

        return new Response($content);
    }

    /**
     * @param Request $request
     * @param $type
     * @param $currentPage
     * @param $itemsPerPage
     *
     * @return Response
     */
    public function showTypeOverview(Request $request, $type, $currentPage, $itemsPerPage)
    {
        $first = ($currentPage - 1) * $itemsPerPage;

        $searchParams = $this->getParameters($request);

        $searchParams['logtype'] = $type;

        $query = $this->getQuery($searchParams);

        $count = $query->count();

        $logs = $query->skip($first)->take($itemsPerPage)->get();

        $lastPage = ceil($count / $itemsPerPage);

        $params = $searchParams + [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'route' => 'admin_logs',
            'routeParams' => ['itemsPerPage' => $itemsPerPage],
            'count' => $count,
            'logs' => $logs,
        ];

        $content = $this->engine->render('@admin/logs/logs.html.twig', $params);

        return new Response($content);
    }

    private function getParameters(Request $request)
    {
        $types = $this->logService->getLogTypes();

        $memberName = $request->query->get('membername');
        $logType = $request->query->get('logtype', -1);
        $ipAddress = $request->query->get('ipaddress');

        return [
            'types' => $types,
            'membername' => $memberName,
            'logtype' => $logType,
            'ipaddress' => $ipAddress,
        ];
    }

    private function getQuery($parameters)
    {
        $log = new Log();

        /** @var Builder $query */
        $query = $log->with('member');

        if ($parameters['membername']) {
            $member = $this->memberRepository
                ->getByUsername($parameters['membername']);

            if ($member) {
                $query->where('IdMember', $member->id);
            }
        }

        if ($parameters['logtype'] > -1 && $parameters['logtype'] < count($parameters['types'])) {
            //$types = $parameters['types'];

            //$query->where('Type', $types[$parameters['logtype']]);
            $query->where('Type', $parameters['logtype']);
        }

        if ($parameters['ipaddress']) {
            $query->where('ipaddress', ip2long($parameters['ipaddress']));
        }

        $query->orderBy('created', 'desc');

        return $query;
    }
}
