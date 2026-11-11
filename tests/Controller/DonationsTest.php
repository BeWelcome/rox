<?php

namespace App\Tests\Controller;

use App\Entity\Donation;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Params;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The donation page with the progress of the campaign, the ways to donate and the latest donations, and the public
 * list of donations, whose last column only the treasurer sees.
 */
#[Group('integration')]
final class DonationsTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testTheDonationPageShowsTheWaysToDonateAndTheLatestDonations(): void
    {
        foreach (range(1, 5) as $day) {
            $this->donation('1' . $day . '.00', '2026-11-0' . $day, 'zztest ' . $day, $day < 3 ? null : $this->country());
        }

        $crawler = $this->client->request('GET', '/donations');

        self::assertResponseIsSuccessful();
        self::assertSame($this->trans('donations'), $crawler->filter('h1.c-page-head__title')->text());
        // no campaign: no progress
        self::assertCount(0, $crawler->filter('.c-donate-status'));
        self::assertSame(
            ['#bank', '#hello_asso', '#paypal', '#time'],
            $crawler->filter('.c-home-tabs__link')->each(static fn (Crawler $tab): string => (string) $tab->attr('href')),
        );
        self::assertStringContainsString('FR76 4255 9100 0008 0117 7451 302', $crawler->filter('#bank .c-donate-account')->text());
        // the reference needs a username
        self::assertCount(3, $crawler->filter('#bank .c-donate-account dt'));
        self::assertCount(1, $crawler->filter('#paypal #donate-button'));

        // the four latest, newest first
        $recent = $crawler->filter('.c-donate-recent__item');
        self::assertCount(4, $recent);
        self::assertSame('€15.00', $recent->eq(0)->filter('.c-donate-recent__amount')->text());
        self::assertSame('Germany', $recent->eq(0)->filter('.c-donate-recent__country')->text());
        self::assertSame('—', $recent->eq(3)->filter('.c-donate-recent__country')->text());
        self::assertSame('/donations/list', $crawler->filter('a.c-donate__more')->attr('href'));
    }

    public function testTheProgressOfARunningCampaignIsShown(): void
    {
        $params = $this->entityManager->getRepository(Params::class)->findOneBy([]);
        $params->setToggledonatebar(1);
        $params->setNeededperyear(1200);
        $params->setCampaignstartdate(new DateTime('-1 month'));
        $this->entityManager->flush();
        $this->donation('300.00', 'now', 'zztest campaign');
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/donations');

        self::assertSame('€300', $crawler->filter('.c-donate-status__value')->text());
        self::assertSame('25%', $crawler->filter('.c-donate-status__percent')->text());
        self::assertSame('width: 25%', $crawler->filter('.c-donate-bar__fill')->attr('style'));
        self::assertSame($this->trans('donate.campaign.monthly', ['amount' => '€100']), $crawler->filter('.c-donate-status .c-donate-next__text')->text());
        // logged in: the reference of the transfer
        self::assertSame(
            $this->trans('donate.account.reference.value', ['username' => 'member-2']),
            $crawler->filter('#bank .c-donate-account dd')->last()->text(),
        );
    }

    public function testTheListShowsNoDonorsToOthers(): void
    {
        $this->donation('25.00', '2026-11-10', 'zztest via PayPal', $this->country(), $this->member('member-3'), 'zztest-7XK21983LM');
        $this->donation('50.00', '2026-11-09', '', null, null, '', 'Zztest Kowalski');

        $crawler = $this->client->request('GET', '/donations/list');

        self::assertResponseIsSuccessful();
        self::assertSame('/donations', $crawler->filter('a.c-page-head__back')->attr('href'));
        self::assertCount(0, $crawler->filter('.c-donate-table__private'));
        self::assertCount(0, $crawler->filter('.c-donate-table__donor'));
        $rows = $crawler->filter('.c-donate-table__row:not(.c-donate-table__row--head)');
        self::assertSame(['€25.00', 'zztest via PayPal', 'Germany'], [
            $rows->eq(0)->filter('.c-donate-table__amount')->text(),
            $rows->eq(0)->filter('.c-donate-table__text')->eq(0)->text(),
            $rows->eq(0)->filter('.c-donate-table__text')->eq(1)->text(),
        ]);
        self::assertSame(['—', '—'], $rows->eq(1)->filter('.c-donate-table__text')->each(static fn (Crawler $cell): string => $cell->text()));
        self::assertSame(
            $this->trans('donations.list.showing', ['from' => 1, 'to' => $rows->count(), 'total' => $rows->count()]),
            $crawler->filter('.c-donate-table__count')->text(),
        );
    }

    public function testTheTreasurerSeesTheDonorsInTheList(): void
    {
        $this->donation('25.00', '2026-11-10', 'zztest via PayPal', $this->country(), $this->member('member-3'), 'zztest-7XK21983LM');
        $this->donation('50.00', '2026-11-09', '', null, null, '', 'Zztest Kowalski');
        $this->client->loginUser($this->member('treasurer'));

        $crawler = $this->client->request('GET', '/donations/list');

        self::assertSame($this->trans('donations.treasurer.only'), $crawler->filter('.c-donate-table__private')->text());
        $donors = $crawler->filter('.c-donate-table__donor');
        self::assertSame('member-3', $donors->eq(0)->filter('.c-donate-table__name')->text());
        self::assertSame('zztest-7XK21983LM', $donors->eq(0)->filter('.c-donate-table__reference')->text());
        self::assertSame('Zztest Kowalski', $donors->eq(1)->filter('.c-donate-table__name')->text());
    }

    public function testTheCountriesAreNamedInTheLanguageOfThePage(): void
    {
        $this->germanNames();
        $this->donation('25.00', '2026-11-10', 'zztest Germany', $this->country());
        $this->donation('20.00', '2026-11-09', 'zztest Indonesia', $this->entityManager->getRepository(Location::class)->find(1643084));
        $this->client->request('GET', '/rox/in/de');

        $crawler = $this->client->request('GET', '/donations/list');
        $rows = $crawler->filter('.c-donate-table__row:not(.c-donate-table__row--head)');
        self::assertSame('Deutschland', $rows->eq(0)->filter('.c-donate-table__text')->eq(1)->text());
        // no German name: the name of the location
        self::assertSame('Indonesia', $rows->eq(1)->filter('.c-donate-table__text')->eq(1)->text());

        $crawler = $this->client->request('GET', '/donations');
        self::assertSame('Deutschland', $crawler->filter('.c-donate-recent__country')->eq(0)->text());
    }

    private function germanNames(): void
    {
        static::getContainer()->get(EntityManagerInterface::class)->getConnection()->executeStatement(
            "INSERT INTO location_translation (locale, field, content, object_id) VALUES ('de', 'name', 'Deutschland', 2921044)",
        );
    }

    private function donation(
        string $amount,
        string $date,
        string $comment,
        ?Location $country = null,
        ?Member $donor = null,
        string $reference = '',
        string $name = '',
    ): void {
        $donation = new Donation()
            ->setAmount($amount)
            ->setCreated(new DateTime($date))
            ->setSystemComment($comment)
            ->setCountry($country)
            ->setDonor($donor)
            ->setReferencePaypal($reference)
            ->setNameGiven($name);
        $this->entityManager->persist($donation);
        $this->entityManager->flush();
    }

    private function country(): Location
    {
        return $this->entityManager->getRepository(Location::class)->find(2921044);
    }

    private function member(string $username): Member
    {
        return $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
    }

    private function trans(string $id, array $parameters = []): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters);
    }
}
