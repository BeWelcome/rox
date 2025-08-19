<?php

namespace App\LegacyKernel;

use Psr\Container\ContainerInterface;
use RoxFrontRouter;
use SessionMemory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

/**
 * Fallback dispatcher for requests that Symfony couldn't match.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class LegacyHttpKernel extends HttpKernel
{
    public function __construct(
        protected Environment $environment
    ) {
        parent::__construct(new EventDispatcher(), new ControllerResolver());
    }

    /**
     * @param int  $type
     * @param bool $catch
     *
     * @return Response
     *
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * The next one is triggered by preg_match. PHP doc says everything's fine with that.
     * @SuppressWarnings("PHPMD.UndefinedVariable")
     */
    #[\Override]
    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        $router = new RoxFrontRouter($this->environment);
        // The only classname ever used
        $router->classes = ['SignupController'];

        $router->session_memory = new SessionMemory('SessionMemory');
        $roxPostHandler = $router->session_memory->__get('posthandler');
        if ($roxPostHandler) {
            $roxPostHandler->setClasses([
                'SignupController',
                'MessagesController',
                'MembersController',
            ]);
            $router->session_memory->__set('posthandler', $roxPostHandler);
        }
        ob_start();

        $router->route();

        $content = ob_get_clean();

        // RoxFrontRouter::route_normal() sends a redirect without setting 301/302
        // Here we can take such redirect and do it with RedirectResponse
        if ('' === $content && !headers_sent()) {
            foreach (headers_list() as $header) {
                if (preg_match('/^Location: (.*)$/', $header, $matches)) {
                    return new RedirectResponse($matches[1]);
                }
            }
        }

        return new Response($content);
    }
}
