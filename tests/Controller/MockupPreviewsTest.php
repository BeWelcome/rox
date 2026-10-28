<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\Newsletter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The translators' previews of mails and pages (/admin/translations/mockups) that failed on develop: pages without a
 * content block, macros imported outside it, the data export's profile, mocks that didn't fit the entities, and
 * newsletter types the database has no newsletter of. They are found and requested as the mockup pages offer them.
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
        // as translators see them on stage and prod: Twig without strict_variables (the test environment has it on,
        // and several mails read fields their mocks or entities don't have, shown empty in production)
        $this->client->disableReboot();
        $this->client->getContainer()->get('twig')->disableStrictVariables();
    }

    public static function previews(): iterable
    {
        yield ['landing', 'be visited|none'];
        yield ['passwords', 'Reset Password'];
        yield ['passwords', 'Reset Password Request'];
        yield ['requests', 'intial request (guest)'];
        yield ['groups', 'declined invite'];
        yield ['signups', 'Confirm Email Address'];
        yield ['signups', 'Finalize'];
        yield ['my_data', 'profile'];
        yield ['my_data', 'Broadcasts: Count - some'];
        yield ['my_data', 'Comments: Count - some'];
        yield ['my_data', 'Donations: Count - some'];
        yield ['my_data', 'Polls_contributed: Count - some'];
        yield ['newsletters', 'Newsletters: Type - local'];
        yield ['newsletters', 'Newsletters: Type - terms'];
    }

    #[DataProvider('previews')]
    public function testThePreviewRenders(string $feature, string $name): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/mockups/' . $feature);
        $this->assertResponseIsSuccessful();

        // a preview is a link, or a form posting its parameters (named by 'fullname' or 'name')
        $link = $crawler->filter('a[href*="/admin/translate/mockup/"]')
            ->reduce(static fn ($a) => rawurldecode(basename((string) $a->attr('href'))) === $name);
        if (\count($link) > 0) {
            $this->client->request('GET', (string) $link->first()->attr('href'));
        } else {
            $form = $crawler->filter('form[action*="/with_params"]')->reduce(static function ($form) use ($name) {
                $fullname = $form->filter('input[name=fullname]');
                $label = \count($fullname) > 0 ? $fullname->attr('value') : $form->filter('input[name=name]')->attr('value');

                return $label === $name;
            });
            self::assertCount(1, $form, $feature . '/' . $name . ' is not offered');
            $this->client->submit($form->form());
        }

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#mockup, div[style*="background-color:#ccc"]');
    }
}
