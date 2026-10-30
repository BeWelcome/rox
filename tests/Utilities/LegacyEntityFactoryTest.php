<?php

namespace App\Tests\Utilities;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * The legacy entity factory (tools/mvc-base/entityfactory.php) returns false for names it doesn't list, so a call
 * like createEntity('ProfileVisit')->recordVisit() failed with "Call to a member function on false".
 */
final class LegacyEntityFactoryTest extends TestCase
{
    public function testEveryEntityTheLegacyCodeCreatesIsListed(): void
    {
        $root = \dirname(__DIR__, 2);
        preg_match_all("/'(\\w+)'\\s*=>\\s*'(build\\/[\\w\\/]+\\.entity)'/", (string) file_get_contents($root . '/tools/mvc-base/entityfactory.php'), $listed);
        $listed = array_combine($listed[1], $listed[2]);

        $missing = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/build'));
        foreach ($files as $file) {
            if ('php' !== $file->getExtension()) {
                continue;
            }
            preg_match_all("/createEntity\\('(\\w+)'/", (string) file_get_contents($file->getPathname()), $used);
            foreach ($used[1] as $name) {
                if (!isset($listed[$name])) {
                    $missing[$name] = $name;
                }
            }
        }
        // only used by getComingPosts(), which nothing calls; build/blog is gone
        unset($missing['BlogEntity']);

        self::assertSame([], array_values($missing));
        foreach (['ProfileNote', 'ProfileVisit'] as $name) {
            self::assertFileExists($root . '/' . $listed[$name] . '.php', $name);
        }
    }
}
