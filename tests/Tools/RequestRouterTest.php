<?php

namespace App\Tests\Tools;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PVars;
use RequestRouter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class RequestRouterTest extends KernelTestCase
{
    #[DataProvider('cacheModes')]
    public function testAliasRoutingRespectsCacheSetting(string $server, int $cacheEnabled, bool $expectCache): void
    {
        $directory = sys_get_temp_dir() . '/rox-routing-' . uniqid();
        $filesystem = new Filesystem();
        $filesystem->mkdir($directory . '/build/donate');
        $filesystem->dumpFile($directory . '/routes.php', '<?php');
        $filesystem->dumpFile($directory . '/build/donate/alias.ini', 'donate = donation');
        \define('SCRIPT_BASE', $directory . '/');
        $_SERVER['SERVER_NAME'] = $server;
        PVars::register('syshcvol', ['IniCache' => $cacheEnabled]);

        try {
            $router = new RequestRouter();

            self::assertSame(['DonateController', 'index', null], $router->findRoute(['donation']));
            self::assertSame($expectCache, is_file($directory . '/build/alias.cache.ini'));

            $filesystem->dumpFile($directory . '/build/donate/alias.ini', 'donate = contribute');
            $alias = $expectCache ? 'donation' : 'contribute';
            self::assertSame(['DonateController', 'index', null], $router->findRoute([$alias]));
        } finally {
            $filesystem->remove($directory);
        }
    }

    public static function cacheModes(): iterable
    {
        yield 'disabled' => ['bewelcome.org', 0, false];
        yield 'localhost' => ['localhost', 1, false];
        yield 'enabled' => ['bewelcome.org', 1, true];
    }
}
