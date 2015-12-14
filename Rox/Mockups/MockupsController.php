<?php

namespace Rox\Mockups;

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
class MockupsController extends \RoxControllerBase
{

    /**
     * LogsController constructor.
     *
     * @param $page The name of the template to load
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @param         $template
     *
     * @return Response
     */
    public function showMockup(Request $request, $mockup) {
        $page = new MockupPage($this->routing, $mockup);
        return new Response($page->render());
    }
}