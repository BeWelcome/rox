<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Templating\EngineInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /** @var EngineInterface */
    private $engine;

    /**
     * @Required
     * @param EngineInterface $engine
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $content = $this->getEngine()->render('security/access.denied.html.twig', [
            'message' => $accessDeniedException->getMessage()
        ]);

        return new Response("Hallo" /*$content*/, 403);
    }

    /**
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }
}
