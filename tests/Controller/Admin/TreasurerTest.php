<?php

namespace App\Tests\Controller\Admin;

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
 * The treasurer tool as the treasurer uses it: the campaign, the list of donations with its three kinds of donors,
 * adding and editing a donation, starting and stopping the campaign.
 */
#[Group('integration')]
final class TreasurerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testOnlyTheTreasurerUsesTheTool(): void
    {
        $this->client->loginUser($this->member('member-2'));
        foreach (['/admin/treasurer', '/admin/treasurer/add', '/admin/treasurer/campaign/start'] as $page) {
            $this->client->request('GET', $page);
            self::assertResponseStatusCodeSame(403, $page);
        }
    }

    public function testTheListShowsMembersNamesAndUnknownDonors(): void
    {
        $this->donation('25.00', '2026-11-10', 'zztest via PayPal', donor: $this->member('member-3'));
        $this->donation('50.00', '2026-11-09', 'zztest bank transfer', name: 'Zztest Kowalski');
        $this->donation('10.00', '2026-11-08', 'zztest unknown');
        $this->client->loginUser($this->member('treasurer'));

        $crawler = $this->client->request('GET', '/admin/treasurer');

        self::assertResponseIsSuccessful();
        self::assertSame($this->trans('admin.treasurer.overview'), $crawler->filter('h1.c-page-head__title')->text());
        $rows = $crawler->filter('.c-donate-table__row:not(.c-donate-table__row--head)');
        // newest first
        $member = $rows->eq(0);
        self::assertSame('/members/member-3', $member->filter('a.c-treasurer__member')->attr('href'));
        self::assertSame('€25.00', $member->filter('.c-donate-table__amount')->text());
        self::assertStringEndsWith('/admin/treasurer/donation/edit/' . $this->donationId('zztest via PayPal'), $member->filter('a.c-radmin-icon')->attr('href'));
        $named = $rows->eq(1);
        self::assertSame('Zztest Kowalski', $named->filter('.c-donate-table__name')->text());
        self::assertSame($this->trans('admin.treasurer.donor.type.other'), $named->filter('.c-treasurer-pill')->text());
        self::assertSame($this->trans('admin.treasurer.donor.type.unknown'), $rows->eq(2)->filter('.c-treasurer__unknown')->text());
    }

    public function testAMemberDonationIsAddedWithItsDay(): void
    {
        $this->client->loginUser($this->member('treasurer'));
        $crawler = $this->client->request('GET', '/admin/treasurer/add');

        self::assertResponseIsSuccessful();
        // a new donation: by a member, today
        self::assertSame('member', $crawler->filter('input[name="donation[donorType]"]:checked')->attr('value'));
        self::assertSame(new DateTime('today')->format('Y-m-d'), $crawler->filter('#donation_created')->attr('value'));
        self::assertCount(1, $crawler->filter('[data-donor-type="other"][hidden]'));
        self::assertCount(1, $crawler->filter('input#donation_donor[data-member-suggest="fill"]'));

        $this->client->submit($crawler->filter('form.js-treasurer-donation')->form(), [
            'donation[donorType]' => 'member',
            'donation[donor]' => 'member-3',
            // left in the hidden field
            'donation[nameGiven]' => 'zztest stale name',
            'donation[amount]' => '42.50',
            'donation[created]' => '2026-03-14',
            'donation[country]' => (string) $this->country()->getGeonameId(),
            'donation[systemComment]' => 'zztest added',
        ]);

        self::assertResponseRedirects('/admin/treasurer');
        $donation = $this->findDonation('zztest added');
        self::assertSame('member-3', $donation->getDonor()?->getUsername());
        self::assertSame('', $donation->getNameGiven());
        self::assertSame('42.50', $donation->getAmount());
        self::assertSame('2026-03-14', $donation->getCreated()->format('Y-m-d'));
        self::assertSame('EUR', $donation->getMoney());
        self::assertSame($this->country()->getGeonameId(), $donation->getCountry()?->getGeonameId());

        $crawler = $this->client->followRedirect();
        self::assertSame($this->trans('admin.treasurer.donation.saved'), $crawler->filter('.c-inline-flash__text')->text());
    }

    public function testADonorWhoIsNotAMemberKeepsOnlyTheirName(): void
    {
        $this->client->loginUser($this->member('treasurer'));
        $crawler = $this->client->request('GET', '/admin/treasurer/add');

        $this->client->submit($crawler->filter('form.js-treasurer-donation')->form(), [
            'donation[donorType]' => 'other',
            // a username left in the hidden field isn't checked or saved
            'donation[donor]' => 'zztest-nobody',
            'donation[nameGiven]' => 'Zztest Kowalski',
            'donation[amount]' => '50',
            'donation[created]' => '2026-11-09',
            'donation[country]' => '',
            'donation[systemComment]' => 'zztest not a member',
        ]);

        self::assertResponseRedirects('/admin/treasurer');
        $donation = $this->findDonation('zztest not a member');
        self::assertNull($donation->getDonor());
        self::assertSame('Zztest Kowalski', $donation->getNameGiven());
        self::assertNull($donation->getCountry());
    }

    public function testEditingADonationChoosesItsDonorType(): void
    {
        $this->donation('50.00', '2026-11-09', 'zztest named', name: 'Zztest Kowalski');
        $this->donation('10.00', '2026-11-08', 'zztest unknown');
        $this->client->loginUser($this->member('treasurer'));

        $crawler = $this->client->request('GET', '/admin/treasurer/donation/edit/' . $this->donationId('zztest named'));
        self::assertSame($this->trans('admin.treasurer.donation.edit'), $crawler->filter('h1.c-page-head__title')->text());
        self::assertSame('other', $crawler->filter('input[name="donation[donorType]"]:checked')->attr('value'));
        self::assertSame('Zztest Kowalski', $crawler->filter('#donation_nameGiven')->attr('value'));
        self::assertCount(1, $crawler->filter('[data-donor-type="member"][hidden]'));

        $crawler = $this->client->request('GET', '/admin/treasurer/donation/edit/' . $this->donationId('zztest unknown'));
        self::assertSame('unknown', $crawler->filter('input[name="donation[donorType]"]:checked')->attr('value'));

        // unknown: the name goes
        $this->client->submit($this->client->request('GET', '/admin/treasurer/donation/edit/' . $this->donationId('zztest named'))
            ->filter('form.js-treasurer-donation')->form(), ['donation[donorType]' => 'unknown']);
        self::assertResponseRedirects('/admin/treasurer');
        self::assertSame('', $this->findDonation('zztest named')->getNameGiven());
        self::assertSame('2026-11-09', $this->findDonation('zztest named')->getCreated()->format('Y-m-d'));
    }

    public function testTheCampaignIsStartedAndStopped(): void
    {
        $this->donation('30.00', 'now', 'zztest this year');
        $this->client->loginUser($this->member('treasurer'));

        $crawler = $this->client->request('GET', '/admin/treasurer');
        self::assertSame($this->trans('admin.treasurer.campaign.stopped.state'), $crawler->filter('.c-treasurer-state')->text());
        $crawler = $this->client->click($crawler->filter('.c-donate-next a[href="/admin/treasurer/campaign/start"]')->link());

        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form.c-home-card')->form(), [
            'campaign[neededperyear]' => '1200',
            'campaign[campaignstartdate]' => new DateTime('-1 month')->format('Y-m-d'),
        ]);
        self::assertResponseRedirects('/admin/treasurer');
        $crawler = $this->client->followRedirect();

        self::assertSame($this->trans('admin.treasurer.campaign.started'), $crawler->filter('.c-inline-flash__text')->text());
        self::assertSame($this->trans('admin.treasurer.campaign.running'), $crawler->filter('.c-treasurer-state--running')->text());
        self::assertSame($this->trans('admin.treasurer.of', ['amount' => '€1,200']), $crawler->filter('.c-donate-step__note')->eq(1)->text());
        self::assertSame(1, $this->params()->getToggledonatebar());

        // stopping is a form with a token, not a link
        $stop = $crawler->filter('form[action="/admin/treasurer/campaign/stop"]');
        self::assertCount(1, $stop->filter('input[name="_token"]'));
        $this->client->request('GET', '/admin/treasurer/campaign/stop');
        self::assertResponseStatusCodeSame(405);
        $this->client->request('POST', '/admin/treasurer/campaign/stop', ['_token' => 'wrong']);
        self::assertSame(1, $this->params()->getToggledonatebar());

        $this->client->submit($stop->form());
        self::assertResponseRedirects('/admin/treasurer');
        $crawler = $this->client->followRedirect();
        self::assertSame($this->trans('admin.treasurer.campaign.stopped'), $crawler->filter('.c-inline-flash__text')->text());
        self::assertSame(0, $this->params()->getToggledonatebar());
    }

    public function testTheCountriesAreNamedAndSortedInTheLanguageOfThePage(): void
    {
        $this->entityManager->getConnection()->executeStatement(
            "INSERT INTO location_translation (locale, field, content, object_id) VALUES ('de', 'name', 'Zzdeutschland', 2921044)",
        );
        $this->donation('25.00', '2026-11-10', 'zztest Germany', donor: $this->member('member-3'));
        $this->client->loginUser($this->member('treasurer'));
        $this->client->request('GET', '/rox/in/de');

        $crawler = $this->client->request('GET', '/admin/treasurer');
        self::assertSame('Zzdeutschland', $crawler->filter('.c-donate-table__row:not(.c-donate-table__row--head) .c-donate-table__text')->eq(1)->text());

        $crawler = $this->client->request('GET', '/admin/treasurer/add');
        $countries = $crawler->filter('#donation_country option')->each(static fn (Crawler $option): string => $option->text());
        // the German name, sorted as such: last
        self::assertSame('Zzdeutschland', end($countries));
        self::assertNotContains('Germany', $countries);
        self::assertSame('2921044', $crawler->filter('#donation_country option')->last()->attr('value'));
    }

    private function donation(string $amount, string $date, string $comment, ?Member $donor = null, string $name = ''): void
    {
        $donation = new Donation()
            ->setAmount($amount)
            ->setCreated(new DateTime($date))
            ->setSystemComment($comment)
            ->setDonor($donor)
            ->setNameGiven($name)
            ->setCountry($this->country());
        $this->entityManager->persist($donation);
        $this->entityManager->flush();
    }

    private function findDonation(string $comment): Donation
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(Donation::class)->findOneBy(['systemComment' => $comment]);
    }

    private function donationId(string $comment): int
    {
        return $this->entityManager->getRepository(Donation::class)->findOneBy(['systemComment' => $comment])->getId();
    }

    private function params(): Params
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(Params::class)->findOneBy([]);
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
