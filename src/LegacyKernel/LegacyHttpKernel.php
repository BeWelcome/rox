<?php

namespace App\LegacyKernel;

use RoxFrontRouter;
use SessionMemory;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

/**
 * Fallback dispatcher for requests that Symfony couldn't match.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LegacyHttpKernel extends HttpKernel
{
    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var Container
     */
    private $container;

    public function __construct(
        Environment $environment,
        EventDispatcherInterface $dispatcher,
        ControllerResolverInterface $resolver,
        RequestStack $requestStack,
        ArgumentResolverInterface $argumentResolver,
        ContainerInterface $container
    ) {
        $this->environment = $environment;
        $this->container = $container;

        parent::__construct($dispatcher, $resolver, $requestStack, $argumentResolver);
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param int  $type
     * @param bool $catch
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * The next one is triggered by preg_match. PHP doc says everything's fine with that.
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
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
