<?php

namespace Rox\Start\Controller;

use Doctrine\Common\Cache\Cache;
use Rox\Start\Service\StartService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
* dashboard controller
*
* @package Dashboard
* @author Amnesiac84
*/
class StartController
{
    /**
     * @var StartService
     */
    protected $startService;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var EngineInterface
     */
    protected $engine;

    public function __construct(
        StartService $startService,
        Cache $cache,
        EngineInterface $engine
    ) {
        $this->startService = $startService;
        $this->cache        = $cache;
        $this->engine       = $engine;
    }

    public function __invoke()
    {
        $key = __METHOD__;

        if (!$stats = $this->cache->fetch($key)) {
            $stats = $this->startService->getStatistics();

            $this->cache->save($key, $stats, 60*60);
        }

        return new Response($this->engine->render('@start/public.html.twig', [
            'stats' => $stats,
        ]));
    }
}
