<?php

namespace Rox\Admin\Logs;

use Rox\Models\Log;
use Symfony\Component\Routing\Router;
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
     * @var RoxModelBase
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

    public function showOverviewAction() {
        $page = new AdminLogsPage($this->router);

        $results = Log::with('member')->get()->take(2);
        $page->setParameters(
            ['logs' => $results->all(), 'logs_array' => $results->toArray()]
        );
        return new Response($page->render());
    }

    public function showIpOverview() {
        $page = new AdminLogsPage($this->router);

        $results = Log::with('member')->get()->take(2);
        $page->setParameters(
            ['logs' => $results->all(), 'logs_array' => $results->toArray()]
        );
        return new Response($page->render());
    }

    public function showUsernameOverview() {
        $page = new AdminLogsPage($this->router);

        $results = Log::with('member')->get()->take(2);
        $page->setParameters(
            ['logs' => $results->all(), 'logs_array' => $results->toArray()]
        );
        return new Response($page->render());
    }
}