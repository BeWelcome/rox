<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Model\Translation\MockupPreview;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * The translators' previews of mails and pages (/admin/translations/mockups) that failed on develop: pages without a
 * content block, macros imported outside it, the data export's profile, mocks that didn't fit the entities, and
 * newsletter types the database has no newsletter of. They are found and requested as the mockup pages offer them,
 * rendered whole (without Twig's strict_variables, as on the live site) and shown inline, their texts listed for
 * translation.
 */
#[Group('integration')]
class MockupPreviewsTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        // only a regular newsletter: the local events and terms of use previews borrow it
        $newsletter = new Newsletter();
        $newsletter->setName('zztest_preview')->setType(Newsletter::REGULAR_NEWSLETTER)
            ->setCreatedBy($admin)->setCreated(new DateTime());
        $entityManager->persist($newsletter);
        $entityManager->flush();
        // bwadmin has the Words right in the fixtures
        $this->client->loginUser($admin);
        // the ICU text of the data export's footer, which gets the date as a parameter (the test database has no
        // translations)
        $this->client->disableReboot();
        $this->client->getContainer()->get('translator')->getCatalogue('en')->set(
            'mydata.generated',
            'Data extracted on {date_generated, date, short} at {date_generated, time, short}.',
            'messages+intl-icu',
        );
    }

    public static function previews(): iterable
    {
        yield ['landing', 'be visited|none', null];
        yield ['passwords', 'Reset Password', null];
        yield ['passwords', 'Reset Password Request', null];
        yield ['requests', 'intial request (guest)', null];
        yield ['groups', 'declined invite', null];
        yield ['signups', 'Confirm Email Address', null];
        yield ['signups', 'Finalize', null];
        yield ['my_data', 'profile', null];
        yield ['my_data', 'broadcasts', 'Count: some'];
        yield ['my_data', 'comments', 'Count: some'];
        yield ['my_data', 'donations', 'Count: some'];
        yield ['my_data', 'polls_contributed', 'Count: some'];
        yield ['newsletters', 'newsletters', 'Type: local'];
        yield ['newsletters', 'newsletters', 'Type: terms'];
    }

    #[DataProvider('previews')]
    public function testThePreviewRenders(string $feature, string $mockup, ?string $variant): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/mockups/' . $feature);
        $this->assertResponseIsSuccessful();

        // a preview is a link per mockup, or per variant (labelled "Parameter: variant")
        $name = $mockup . (null === $variant ? '' : ' / ' . $variant);
        $links = $crawler->filter('.p-tr-preview')
            ->reduce(static fn ($card) => 0 === strcasecmp(trim($card->filter('.p-tr-preview__title')->text()), $mockup))
            ->filter('a.p-tr-button')
            ->reduce(static fn ($a) => null === $variant || 0 === strcasecmp(trim($a->text()), $variant));
        self::assertCount(1, $links, $feature . '/' . $name . ' is not offered');

        $crawler = $this->client->request('GET', (string) $links->attr('href'));
        $this->assertResponseIsSuccessful();
        // the mail's body or the page's content, without the site's menu and footer
        self::assertNotSame('', trim($crawler->filter('.p-tr-mockup__stage')->html()));
        self::assertCount(0, $crawler->filter('.p-tr-mockup__stage nav.navbar, .p-tr-mockup__stage footer #translations'));
    }

    public function testTheTextsOfAMailCanBeTranslatedOnItsPreview(): void
    {
        // translation mode is off: the preview has its texts listed all the same
        $this->client->request('GET', '/admin/translations/mode/off');
        $crawler = $this->client->request('GET', '/admin/translate/mockup/signups/' . rawurlencode('Confirm Email Address'));

        $this->assertResponseIsSuccessful();
        // the mail whole: its layout with the greeting and the inlined styles, its images from the site
        self::assertGreaterThan(0, $crawler->filter('.p-tr-mockup__mail table[style]')->count());
        self::assertGreaterThan(0, $crawler->filter('.p-tr-mockup__mail img[src="/images/logo_email_2025.png"]')->count());
        // in a shadow root, with its own styles only
        self::assertCount(1, $crawler->filter('.p-tr-mockup__mail > template[shadowrootmode=open] > style'));
        // and its texts in the list for translation
        $codes = $crawler->filter('.js-in-place-item')->each(static fn ($item) => $item->attr('data-code'));
        self::assertContains('signup.thank.you', $codes);
        self::assertContains('email.greeting', $codes);
        // the tool page keeps the site's styles
        self::assertGreaterThan(0, $crawler->filter('head link[rel=stylesheet][href*="tailwind"]')->count());
    }

    public function testABrokenPreviewAsksToReportIt(): void
    {
        // a template that can't be rendered
        static::getContainer()->set(MockupPreview::class, new MockupPreview(
            new Environment(new ArrayLoader(['emails/signup.html.twig' => '{{ zztest_broken() }}'])),
            static::getContainer()->get(EntrypointLookupInterface::class),
        ));
        $crawler = $this->client->request('GET', '/admin/translate/mockup/signups/' . rawurlencode('Confirm Email Address'));

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-tr-mockup__broken[role=alert]'));
        // reported in a new topic of the translators' group, linked below the preview
        self::assertSame('/group/60/forum/new', $crawler->filter('.p-tr-mockup__report a')->attr('href'));
        // the page is whole: its header and the site's footer
        self::assertCount(1, $crawler->filter('.p-tr-tool__title'));
        self::assertCount(1, $crawler->filter('#footer_content'));
    }
}
