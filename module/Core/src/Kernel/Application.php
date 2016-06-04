<?php

namespace Rox\Core\Kernel;

use EnvironmentExplorer;
use Illuminate\Database\Connection;
use Rox\Core\Loader\ConfigLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class Application
 *
 * @todo
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Application extends Kernel
{
    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        if ($this->isDebug()) {
            $this->disableHoaAutoload();

            Debug::enable();
        }

        define('SCRIPT_BASE', $this->getRootDir() . '/');
        define('HTDOCS_BASE', $this->getRootDir() . '/htdocs/');

        $environmentExplorer = new EnvironmentExplorer();

        $environmentExplorer->initializeGlobalState();

        parent::boot();
    }

    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            call_user_func(new ConfigLoader(), $container);
        });
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws NotFoundHttpException
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->boot();

        // TODO just need to bootstrap the connection. should find better solution
        $this->getContainer()->get(Connection::class);

        try {
            return parent::handle($request, $type, false);
        } catch (NotFoundHttpException $e) {
            if (!$e->getPrevious() instanceof ResourceNotFoundException) {
                throw $e;
            }

            // Load the Symfony session

            /** @var SessionInterface $session */
            $session = $this->getContainer()->get('session');

            $session->start();

            return $this->getLegacyHttpKernel()->handle($request, $type, $catch);
        }
    }

    public function getRootDir()
    {
        return getcwd();
    }

    /**
     * @return HttpKernelInterface
     */
    protected function getLegacyHttpKernel()
    {
        return $this->getContainer()->get(LegacyHttpKernel::class);
    }

    /**
     * PhpMetrics has a sub-dependency Hoa\Core, which registers an autoloader
     * that prevents xdebug breakpoints from working with Laravel packages.
     */
    protected function disableHoaAutoload()
    {
        if (!is_array($functions = spl_autoload_functions())) {
            return;
        }

        foreach ($functions as $function) {
            if ($function[0] === \Hoa\Core\Consistency\Consistency::class) {
                spl_autoload_unregister('\Hoa\Core\Consistency::autoload');
            }
        }
    }
}
