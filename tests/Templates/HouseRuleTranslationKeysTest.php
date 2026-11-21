<?php

namespace App\Tests\Templates;

use App\Doctrine\HostRestrictionsType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * A member's house rules (no smoking, no alcohol, no drugs) are stored as 'no.smoking' and translated with the
 * keys 'restriction.no.smoking' (translations/missing/profile.yaml). A template that builds another key, like
 * 'restriction_no.smoking', shows the key instead of the rule.
 */
final class HouseRuleTranslationKeysTest extends TestCase
{
    public function testTheTemplatesBuildTheKeysOfTheHouseRules(): void
    {
        $finder = new Finder()->files()->name('*.twig')->in(__DIR__ . '/../../templates');
        $prefixes = [];
        foreach ($finder as $file) {
            // ('restriction.' ~ restriction): the start of a key that a stored rule completes
            preg_match_all("/'(restriction[._])'\s*~/", $file->getContents(), $matches);
            foreach ($matches[1] as $prefix) {
                $prefixes[$file->getRelativePathname()] = $prefix;
            }
        }

        self::assertNotEmpty($prefixes, 'No template shows the house rules of a member any more.');
        self::assertSame([], array_filter($prefixes, static fn (string $prefix): bool => 'restriction.' !== $prefix));
    }

    public function testEveryHouseRuleHasItsKey(): void
    {
        $keys = array_keys(Yaml::parseFile(__DIR__ . '/../../translations/missing/profile.yaml'));

        foreach ([HostRestrictionsType::NO_SMOKING, HostRestrictionsType::NO_ALCOHOL, HostRestrictionsType::NO_DRUGS] as $rule) {
            self::assertContains('restriction.' . $rule, $keys);
        }
    }
}
