<?php

namespace App\Tests\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Location;
use App\Entity\Member;
use App\Service\LanguageNames;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The finish setup page of a new member whose email address isn't confirmed yet: with the registration key from the
 * mail the setup finishes the signup, without it the details are saved and the account waits for the confirmation,
 * and the page says so.
 */
#[Group('integration')]
class FinishSetupTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testThePageTellsThatTheAccountWaitsWithoutTheKey(): void
    {
        $member = $this->signUp();

        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/finalize');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-sf-unconfirmed'));
        $button = $crawler->filter('.js-finalize-submit');
        self::assertSame($this->trans('signup.activate.later'), trim($button->text()));
        self::assertSame($this->trans('signup.activate'), $button->attr('data-finish'));
    }

    public function testTheLanguagesHaveTheirOwnNames(): void
    {
        // the language's own name from intl ("Français"), not language.Name (some with HTML entities); the name in the
        // member's language (lang_fr) for the search
        $member = $this->signUp();

        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/finalize');

        $languages = json_decode((string) $crawler->filter('.js-sf-tongues')->attr('data-languages'), true);
        // a list for the page's script, not an object
        self::assertTrue(array_is_list($languages));
        $native = static::getContainer()->get(LanguageNames::class)->native('fr');
        self::assertSame($native, array_column($languages, 'name', 'code')['fr']);
        self::assertSame($this->trans('lang_fr'), array_column($languages, 'alt', 'code')['fr']);
        // spoken languages first, then sign languages, each by name
        $groups = array_column($languages, 'group');
        self::assertSame('spoken', array_column($languages, 'group', 'code')['fr']);
        self::assertSame($groups, array_merge(
            array_filter($groups, static fn ($group) => 'spoken' === $group),
            array_filter($groups, static fn ($group) => 'signed' === $group),
        ));
        foreach (['spoken', 'signed'] as $group) {
            $names = array_column(array_filter($languages, static fn ($language) => $group === $language['group']), 'name');
            $sorted = $names;
            sort($sorted);
            self::assertSame($sorted, $names, $group . ' languages are not sorted');
        }
        $headings = json_decode((string) $crawler->filter('.js-sf-tongues')->attr('data-groups'), true);
        self::assertSame(['spoken', 'signed'], array_keys($headings));
        self::assertSame($native, trim($crawler->filter('.js-lang-common[data-code=fr]')->text()));
    }

    public function testWithoutTheKeyTheDetailsAreSavedAndTheAccountWaits(): void
    {
        $member = $this->signUp();

        $this->submitSetup($member, '');

        self::assertResponseRedirects('/members/' . $member->getUsername() . '/edit');
        self::assertSame(MemberStatusType::ACCOUNT_ACTIVATED, $this->reload($member)->getStatus());
        self::assertStringContainsString(
            $this->trans('flash.signup.saved.unconfirmed'),
            $this->client->followRedirect()->text(),
        );
    }

    public function testWithTheKeyTheSetupFinishesTheSignup(): void
    {
        $member = $this->signUp();

        $this->submitSetup($member, (string) $member->getRegistrationKey());

        self::assertResponseRedirects('/members/' . $member->getUsername() . '/edit');
        $member = $this->reload($member);
        self::assertSame(MemberStatusType::ACTIVE, $member->getStatus());
        self::assertNull($member->getRegistrationKey());
        self::assertStringNotContainsString(
            $this->trans('flash.signup.saved.unconfirmed'),
            $this->client->followRedirect()->text(),
        );
    }

    public function testAWrongKeyIsRefused(): void
    {
        $member = $this->signUp();

        $crawler = $this->submitSetup($member, 'not-the-key');

        // the form again, the key marked (as the hosting interest check: an error added after the validation)
        self::assertResponseIsSuccessful();
        self::assertStringContainsString($this->trans('error.registration.key'), $crawler->filter('form[name=signup_form_finalize]')->text());
        self::assertSame(MemberStatusType::AWAITING_MAIL_CONFIRMATION, $this->reload($member)->getStatus());
    }

    public function testAConfirmedAddressNeedsNoKey(): void
    {
        // confirmed from the mail's link before the setup: no key field, no note, the button finishes the setup
        $member = $this->signUp();
        $this->reload($member)->setRegistrationKey(null)->setStatus(MemberStatusType::MAIL_CONFIRMED);
        $this->entityManager()->flush();

        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/finalize');

        self::assertCount(0, $crawler->filter('.p-sf-unconfirmed'));
        self::assertSame($this->trans('signup.activate'), trim($crawler->filter('.js-finalize-submit')->text()));
    }

    private function submitSetup(Member $member, string $registrationKey): Crawler
    {
        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/finalize');
        $location = $this->entityManager()->getRepository(Location::class)->findOneBy([]);
        self::assertNotNull($location, 'the test database has no place');
        $form = $crawler->filter('form[name=signup_form_finalize]')->form();
        $form['signup_form_finalize[registration_key]'] = $registrationKey;
        $form['signup_form_finalize[name]'] = 'Finish Setup';
        $form['signup_form_finalize[birthdate]'] = '1990-05-17';
        $form['signup_form_finalize[gender]'] = 'other';
        $form['signup_form_finalize[mother_tongue]'] = ['en'];
        $form['signup_form_finalize[location][fullname]'] = (string) $location->getName();
        $form['signup_form_finalize[location][geoname_id]'] = (string) $location->getGeonameId();
        $form['signup_form_finalize[location][latitude]'] = (string) $location->getLatitude();
        $form['signup_form_finalize[location][longitude]'] = (string) $location->getLongitude();
        $form['signup_form_finalize[accommodation]'] = 'no';

        return $this->client->submit($form);
    }

    private function signUp(): Member
    {
        $crawler = $this->client->request('GET', '/signup');
        $form = $crawler->filter('input[type="submit"]')->form();
        $username = 'setup' . substr(uniqid(), -8);
        $form['signup_form[username]'] = $username;
        $form['signup_form[email]'] = $username . '@example.com';
        $form['signup_form[password]'] = 'StrongPassword123!';
        $form['signup_form[terms_privacy]'] = '1';
        $this->client->submit($form);
        self::assertResponseRedirects();

        return $this->reload($username);
    }

    private function reload(Member|string $member): Member
    {
        $this->entityManager()->clear();

        return $this->entityManager()->getRepository(Member::class)
            ->findOneBy(['username' => $member instanceof Member ? $member->getUsername() : $member]);
    }

    private function trans(string $id): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id);
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
