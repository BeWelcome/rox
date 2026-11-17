<?php

namespace App\Tests\Controller;

use App\Repository\MemberRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The trips card of the home page: its distance is a pill that opens a dropdown of distances, the current one checked,
 * and the card has no footer row: All visitors sits under the trip, next to the travellers' faces.
 */
#[Group('integration')]
class HomeTripsCardTest extends WebTestCase
{
    public function testTheCardHasADistanceMenuAndSeeAllInItsHead(): void
    {
        $client = static::createClient();
        $client->loginUser(static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2'));

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $card = $crawler->filter('.js-home-trips');
        self::assertCount(1, $card->filter('button.js-home-trips-radius[data-bs-toggle="dropdown"]'));
        self::assertCount(0, $card->filter('select'));
        $options = $card->filter('.js-home-trips-radius-option')->each(static fn ($option): string => (string) $option->attr('data-value'));
        self::assertSame(['20', '50', '100'], array_values(array_intersect($options, ['20', '50', '100'])));
        // the current distance is checked and in the pill
        $current = (string) $card->filter('#trips_radius')->attr('value');
        if ('' !== $current) {
            $active = $card->filter('.js-home-trips-radius-option.is-active');
            self::assertSame($current, $active->attr('data-value'));
            self::assertSame(trim($active->text()), trim($card->filter('.js-home-trips-radius-label')->text()));
        }
        // See all is in the head, as on the other cards, with the distance; no footer row
        self::assertCount(1, $card->filter('.c-home-dash__head a.c-home-dash__seeall'));
        self::assertCount(1, $card->filter('.c-home-dash__head .js-home-trips-radius'));
        self::assertCount(0, $card->filter('.c-home-pane__footer--more'));
    }
}
