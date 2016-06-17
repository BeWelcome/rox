<?php

namespace Rox\Start\Controller;

use Doctrine\Common\Cache\Cache;
use Rox\Core\Controller\AbstractController;
use Rox\Start\Service\StartService;
use Symfony\Component\HttpFoundation\Response;

class StartController extends AbstractController
{
    /**
     * @var StartService
     */
    protected $startService;

    /**
     * @var Cache
     */
    protected $cache;

    public function __construct(StartService $startService, Cache $cache)
    {
        $this->startService = $startService;
        $this->cache = $cache;
    }

    public function startPageAction()
    {
        $key = __METHOD__;

        if (!$stats = $this->cache->fetch($key)) {
            $stats = $this->startService->getStatistics();

            $this->cache->save($key, $stats, 60*60);
        }

        return new Response($this->render('@start/public.html.twig', [
            'stats' => $stats,
        ]));
    }
}
