<?php

namespace Rox\Admin\Controller;

use Illuminate\Database\Eloquent\Builder;
use Rox\Admin\Service\LogService;
use Rox\Core\Controller\AbstractController;
use Rox\Member\Repository\MemberRepositoryInterface;
use Rox\Models\Log;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogsController extends AbstractController
{
    /**
     * @var LogService
     */
    protected $logService;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    public function __construct(
        LogService $logService,
        MemberRepositoryInterface $memberRepository
    ) {
        $this->logService = $logService;
        $this->memberRepository = $memberRepository;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showOverview(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);

        $parameters = $this->getParameters($request);

        $query = $this->getQuery($parameters);

        $query->forPage($page, $limit);

        /** @var \Illuminate\Database\Query\Builder $q */
        $q = $query->getQuery();

        $count = $q->getCountForPagination();

        $logs = $query->get();

        $params = $parameters + [
            'currentPage' => $page,
            'lastPage' => ceil($count / $limit),
            'route' => 'admin/logs',
            'routeParams' => ['limit' => $limit],
            'count' => $count,
            'logs' => $logs,
        ];

        $content = $this->render('@admin/logs/logs.html.twig', $params);

        return new Response($content);
    }

    private function getParameters(Request $request)
    {
        $types = $this->logService->getLogTypes();

        $memberName = $request->query->get('membername');
        $logType = $request->query->get('logtype');
        $ipAddress = $request->query->get('ipaddress');

        return [
            'types' => $types,
            'membername' => $memberName,
            'logtype' => $logType,
            'ipaddress' => $ipAddress,
        ];
    }

    /**
     * @param array $parameters
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    private function getQuery(array $parameters)
    {
        $log = new Log();

        $query = $log->newQuery();

        $query->with('member');

        $query->where('IdMember', '!=', 0);

        if ($parameters['membername']) {
            $member = $this->memberRepository
                ->getByUsername($parameters['membername']);

            $query->where('IdMember', $member->id);
        }

        if ($parameters['logtype']) {
            $types = $parameters['types'];

            $query->where('Type', $types[$parameters['logtype']]);
        }

        if ($parameters['ipaddress']) {
            $query->where('ipaddress', ip2long($parameters['ipaddress']));
        }

        $query->orderBy('created', 'desc');

        return $query;
    }
}
