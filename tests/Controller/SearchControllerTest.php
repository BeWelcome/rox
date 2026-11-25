<?php

namespace App\Tests\Controller;

use App\Repository\MemberRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('integration')]
class SearchControllerTest extends WebTestCase
{
    public function testLoggedInSearchByLocationPageTitleNamesPageBeforeBrand(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');

        self::assertNotNull($member);

        $client->loginUser($member);
        $crawler = $client->request('GET', '/search/locations');

        $this->assertResponseIsSuccessful();
        self::assertSame('search.locations | BeWelcome', trim($crawler->filter('title')->text()));
    }

    public function testSearchByLocationPageShowsSearchBarQuickFiltersAndOptionsPanel(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);

        $crawler = $client->request('GET', '/search/locations');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('form.p-search input#search_location'));
        self::assertCount(1, $crawler->filter('.p-search__bar select#search_distance'));
        self::assertCount(4, $crawler->filter('.p-search__pills .js-search-pill'));
        self::assertCount(1, $crawler->filter('.p-search-options input#search_accommodation_yes'));
        self::assertCount(1, $crawler->filter('.p-search-options select#search_can_host'));
    }

    public function testARadiusSearchFindsTheMembersOfThePlace(): void
    {
        $client = static::createClient();
        $connection = static::getContainer()->get('doctrine.dbal.default_connection');
        $found = $connection->fetchAssociative(<<<'SQL'
                SELECT m.Username AS username, l.geoname_id, l.name, ST_Y(l.coordinates) AS latitude, ST_X(l.coordinates) AS longitude
                FROM member m
                JOIN address a ON a.member_id = m.id AND a.active = 1
                JOIN location l ON l.geoname_id = a.location
                WHERE m.Status = 'Active' AND m.Username <> 'member-2'
                ORDER BY m.id LIMIT 1
            SQL);
        self::assertNotFalse($found, 'the test fixtures contain an active member with an address');

        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);
        $crawler = $client->request('GET', '/search/locations');
        $form = $crawler->filter('.p-search__bar .js-search-find')->form([
            'search[location]' => $found['name'],
            'search[location_fullname]' => $found['name'],
            'search[location_name]' => $found['name'],
            'search[location_geoname_id]' => (string) $found['geoname_id'],
            'search[location_latitude]' => (string) $found['latitude'],
            'search[location_longitude]' => (string) $found['longitude'],
            'search[distance]' => '20',
            'search[items]' => '100',
            'search[last_active]' => '2400',
        ]);
        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('main a[href*="/members/' . $found['username'] . '"]')->count(), 'the member of the place is found');
    }

    /**
     * "Accessible" lists the members whose home is marked as accessible by wheelchair on their accommodation page.
     */
    public function testTheAccessibleOptionFindsAccessibleHomesOnly(): void
    {
        $client = static::createClient();
        $connection = static::getContainer()->get('doctrine.dbal.default_connection');
        $found = $connection->fetchAssociative(<<<'SQL'
                SELECT m.id, m.Username AS username, l.geoname_id, l.name, ST_Y(l.coordinates) AS latitude, ST_X(l.coordinates) AS longitude
                FROM member m
                JOIN address a ON a.member_id = m.id AND a.active = 1
                JOIN location l ON l.geoname_id = a.location
                WHERE m.Status = 'Active' AND m.Username <> 'member-2'
                ORDER BY m.id LIMIT 1
            SQL);
        self::assertNotFalse($found, 'the test fixtures contain an active member with an address');
        $client->loginUser(static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2'));

        $listed = function (bool $accessible, bool $ticked) use ($client, $connection, $found): int {
            $connection->executeStatement(
                'UPDATE address SET wheelChairAccessible = ? WHERE member_id = ? AND active = 1',
                [(int) $accessible, $found['id']],
            );
            $crawler = $client->request('GET', '/search/locations');
            $form = $crawler->filter('.p-search__bar .js-search-find')->form([
                'search[location]' => $found['name'],
                'search[location_fullname]' => $found['name'],
                'search[location_name]' => $found['name'],
                'search[location_geoname_id]' => (string) $found['geoname_id'],
                'search[location_latitude]' => (string) $found['latitude'],
                'search[location_longitude]' => (string) $found['longitude'],
                'search[distance]' => '20',
                'search[items]' => '100',
                'search[last_active]' => '2400',
            ]);
            if ($ticked) {
                $form->setValues(['search[accessible]' => true]);
            }
            $crawler = $client->submit($form);
            $this->assertResponseIsSuccessful();

            return $crawler->filter('main a[href*="/members/' . $found['username'] . '"]')->count();
        };

        self::assertGreaterThan(0, $listed(false, false), 'without the option the member is found');
        self::assertSame(0, $listed(false, true), 'a home that is not accessible is left out');
        self::assertGreaterThan(0, $listed(true, true), 'an accessible home is found');
    }

    /**
     * @return iterable<string, array{string, string, string, bool}> minimum age, maximum age, the member's day of
     *                                                               birth (from today), found or not
     */
    public static function ages(): iterable
    {
        yield 'at most 36: 36 today' => ['', '36', '-36 years', true];
        yield 'at most 36: 36 since yesterday' => ['', '36', '-36 years -1 day', true];
        yield 'at most 36: 37 tomorrow' => ['', '36', '-37 years +1 day', true];
        yield 'at most 36: 37 today' => ['', '36', '-37 years', false];
        yield 'at most 36: 35' => ['', '36', '-35 years -6 months', true];
        yield 'at least 36: 36 today' => ['36', '', '-36 years', true];
        yield 'at least 36: 36 tomorrow' => ['36', '', '-36 years +1 day', false];
        yield 'from 36 to 36: 36 for half a year' => ['36', '36', '-36 years -6 months', true];
    }

    /**
     * The ages of the search are the ages members have: "at most 36" includes everybody who is 36, until the day
     * they turn 37 (it left out who was 36 already).
     */
    #[DataProvider('ages')]
    public function testTheAgesOfTheSearchIncludeTheirOwnYear(string $minimum, string $maximum, string $born, bool $found): void
    {
        $client = static::createClient();
        $connection = static::getContainer()->get('doctrine.dbal.default_connection');
        $member = $connection->fetchAssociative(<<<'SQL'
                SELECT m.id, m.Username AS username, l.geoname_id, l.name, ST_Y(l.coordinates) AS latitude, ST_X(l.coordinates) AS longitude
                FROM member m
                JOIN address a ON a.member_id = m.id AND a.active = 1
                JOIN location l ON l.geoname_id = a.location
                WHERE m.Status = 'Active' AND m.Username <> 'member-2'
                ORDER BY m.id LIMIT 1
            SQL);
        self::assertNotFalse($member, 'the test fixtures contain an active member with an address');
        // the member shows their age (bit 4 of HideAttribute hides it)
        $connection->executeStatement(
            'UPDATE member SET BirthDate = ?, HideAttribute = HideAttribute & ~4 WHERE id = ?',
            [new DateTimeImmutable('today ' . $born)->format('Y-m-d'), $member['id']],
        );
        $client->loginUser(static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2'));

        $crawler = $client->request('GET', '/search/locations');
        $crawler = $client->submit($crawler->filter('.p-search__bar .js-search-find')->form([
            'search[location]' => $member['name'],
            'search[location_fullname]' => $member['name'],
            'search[location_name]' => $member['name'],
            'search[location_geoname_id]' => (string) $member['geoname_id'],
            'search[location_latitude]' => (string) $member['latitude'],
            'search[location_longitude]' => (string) $member['longitude'],
            'search[distance]' => '20',
            'search[items]' => '100',
            'search[last_active]' => '2400',
            'search[min_age]' => $minimum,
            'search[max_age]' => $maximum,
        ]));

        $this->assertResponseIsSuccessful();
        self::assertSame($found, $crawler->filter('main a[href*="/members/' . $member['username'] . '"]')->count() > 0);
    }

    public function testTheHomePageMemberSearchOpensProfiles(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);

        // the member field goes to the profile itself (landing.js); there's no search page for members
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        self::assertSame('/members/__username__', $crawler->filter('form.l-hello__member-form')->attr('data-profile-url'));

        $client->request('GET', '/search/members');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPlaceSearchFromTheHomePageOpensTheSearchForThatPlace(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('#tiny_updateMap')->form([
            'tiny[location]' => 'Berlin, Land Berlin, Germany',
            'tiny[location_fullname]' => 'Berlin, Land Berlin, Germany',
            'tiny[location_name]' => 'Berlin',
            'tiny[location_geoname_id]' => '2950159',
            'tiny[location_latitude]' => '52.52437',
            'tiny[location_longitude]' => '13.41053',
        ]);
        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        self::assertSame('Berlin, Land Berlin, Germany', $crawler->filter('form.p-search input#search_location')->attr('value'));
    }

    public function testTheHomePagesCityOpensTheSearchForTheMembersPlace(): void
    {
        // a member with an address (the front page shows the city) and another member in the same place
        $client = static::createClient();
        $connection = static::getContainer()->get('doctrine.dbal.default_connection');
        $found = $connection->fetchAssociative(<<<'SQL'
                SELECT m.Username AS username, o.Username AS other, l.name
                FROM member m
                JOIN address a ON a.member_id = m.id AND a.active = 1
                JOIN location l ON l.geoname_id = a.location
                JOIN address oa ON oa.location = a.location AND oa.active = 1 AND oa.member_id <> m.id
                JOIN member o ON o.id = oa.member_id AND o.Status = 'Active'
                WHERE m.Status = 'Active' AND m.Accommodation IS NOT NULL
                ORDER BY m.id LIMIT 1
            SQL);
        self::assertNotFalse($found, 'the test fixtures contain two active members in one place');
        // the other member passes the default filters (active in the last two years, room for a guest)
        $connection->executeStatement(
            "UPDATE member SET LastActive = NOW(), Accommodation = 'yes', MaxGuests = 2 WHERE Username = ?",
            [$found['other']],
        );
        $client->loginUser(static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier($found['username']));

        $crawler = $client->request('GET', '/');
        $form = $crawler->filter('#home_updateMap')->form();
        self::assertSame('GET', $form->getMethod());
        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        // no "pick the place from the list": the place is the member's, with its full name
        self::assertCount(0, $crawler->filter('form.p-search .invalid-feedback, form.p-search .form-error-message'));
        self::assertStringNotContainsString(
            static::getContainer()->get('translator')->trans('search.location.dropdown'),
            $crawler->filter('main')->text(),
        );
        self::assertSame($found['name'], $crawler->filter('form.p-search input#search_location')->attr('value'));
        self::assertGreaterThan(0, $crawler->filter('main a[href*="/members/' . $found['other'] . '"]')->count(), 'the other member of the place is found');
    }
}
