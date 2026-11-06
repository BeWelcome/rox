<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * What moved out of the footer, which phones show only on the front page and the members' home: the language to the
 * preferences page, translation mode to the volunteer menu (an orange dot on its icon while it is on) and to the
 * header of the page's translation section. bwadmin has the Words right in the fixtures, member-6 no rights.
 */
#[Group('integration')]
final class TopbarMenusTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testThePreferencesHaveTheLanguage(): void
    {
        $this->client->loginUser($this->member('member-6'));

        $crawler = $this->client->request('GET', '/members/member-6/preferences');

        $this->assertResponseIsSuccessful();
        self::assertGreaterThan(1, $crawler->filter('#preference_language option[value^="/rox/in/"]')->count());
        self::assertCount(1, $crawler->filter('#preference_language option[selected][value="/rox/in/en"]'));
        // not in the footer any more, and no translation mode for members without the Words right
        self::assertCount(0, $crawler->filter('footer select#language'));
        self::assertCount(0, $crawler->filter('.c-translation-mode'));
    }

    public function testPhonesShowTheFooterOnlyOnTheHomePages(): void
    {
        $this->client->loginUser($this->member('member-6'));

        $crawler = $this->client->request('GET', '/safety');
        self::assertCount(1, $crawler->filter('footer.footer--no-mobile'));

        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('footer.footer--no-mobile'));
        self::assertCount(1, $crawler->filter('footer #footer_content'));
    }

    public function testATranslatorTurnsTranslationModeOnAndOff(): void
    {
        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/safety');
        self::assertCount(0, $crawler->filter('.topbar-icon-dropdown__dot'));
        $form = $crawler->filter('#volunteer_menu form.c-translation-mode');
        self::assertCount(1, $form);
        self::assertStringEndsWith('/admin/translations/mode/on', (string) $form->attr('action'));

        $this->client->submit($form->form());

        $this->assertResponseRedirects();
        $crawler = $this->client->request('GET', '/safety');
        self::assertCount(1, $crawler->filter('.topbar-icon-dropdown__dot'));
        self::assertCount(1, $crawler->filter('#volunteer_menu .c-translation-mode__button.is-on'));

        // the translation section of the page turns it off again
        self::assertCount(1, $crawler->filter('#translations .c-in-place__head form.c-translation-mode'));
        $form = $crawler->filter('#translations .c-in-place__head form.c-translation-mode');
        self::assertStringEndsWith('/admin/translations/mode/off', (string) $form->attr('action'));
        $this->client->submit($form->form());
        $crawler = $this->client->request('GET', '/safety');
        self::assertCount(0, $crawler->filter('.topbar-icon-dropdown__dot'));
    }

    public function testTheSwitchIsAPostWithTheToken(): void
    {
        $this->client->loginUser($this->member('bwadmin'));
        $this->client->request('GET', '/admin/translations/mode/on');
        $this->assertResponseStatusCodeSame(405);
        $this->client->request('POST', '/admin/translations/mode/on', ['_token' => 'wrong']);
        $this->assertResponseStatusCodeSame(403);
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
