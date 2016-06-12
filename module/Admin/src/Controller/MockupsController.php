<?php

namespace Rox\Admin\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * dashboard controller
 *
 * @package Dashboard
 * @author Amnesiac84
 */
class MockupsController extends \RoxControllerBase
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function showMockup($mockup)
    {
        $content = $this->engine->render('@admin/mockups/' . $mockup . '.html.twig');

        return new Response($content);
    }
}
