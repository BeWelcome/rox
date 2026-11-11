<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The login page and the pages to get a new password in the front page's look: the visitors' top bar, the form of
 * the front page's login panel, a wrong password explained above the form.
 */
#[Group('integration')]
final class LoginPageTest extends WebTestCase
{
    public function testTheLoginPageHasTheFormOfTheFrontPage(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSame($this->trans('login.title'), $crawler->filter('h1.p-login__title')->text());
        self::assertSame($this->trans('landing.welcomeback'), $crawler->filter('.p-login__eyebrow')->text());
        // the visitors' top bar of the front page: Log in goes to this page, there's no panel to open
        self::assertCount(1, $crawler->filter('.fp-top a.fp-top__link[href$="/login"]:not(.js-home-login-open)'));
        self::assertCount(1, $crawler->filter('.fp-top a.fp-top__join[href$="/signup"]'));

        $form = $crawler->filter('form#main-login-form.c-login-form');
        self::assertStringEndsWith('/login_check', (string) $form->attr('action'));
        self::assertCount(1, $form->filter('input#login_username[name="_username"][autofocus]'));
        self::assertCount(1, $form->filter('input#login_password[name="_password"]'));
        self::assertCount(1, $form->filter('input[name="_remember_me"]'));
        self::assertCount(1, $form->filter('input[name="_csrf_token"]'));
        self::assertStringEndsWith('/resetpassword', (string) $form->filter('a.c-login-form__forgot')->attr('href'));
        $toggle = $form->filter('button.js-password-show-hide');
        self::assertSame($this->trans('password.show'), $toggle->attr('aria-label'));
        self::assertSame($this->trans('password.hide'), $toggle->attr('data-label-hide'));

        self::assertStringEndsWith('/signup', (string) $crawler->filter('.p-login__switch a')->attr('href'));
        self::assertCount(1, $crawler->filter('img.p-login__photo[alt=""]'));
        self::assertCount(0, $crawler->filter('.p-login__error'));
    }

    public function testAWrongPasswordIsExplainedAboveTheForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $client->submit($crawler->filter('#main-login-form')->form(), ['_username' => 'member-2', '_password' => 'zztest-wrong']);
        $crawler = $client->followRedirect();

        $error = $crawler->filter('.p-login__error');
        self::assertCount(1, $error);
        self::assertSame($this->trans('Invalid credentials.'), $error->filter('.p-login__error-title')->text());
        self::assertStringEndsWith('/resetpassword', (string) $error->filter('a')->first()->attr('href'));
        // the username stays
        self::assertSame('member-2', $crawler->filter('#login_username')->attr('value'));
    }

    public function testTheFrontPagePanelUsesTheSameForm(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->filter('#home-login form#home-login-form.c-login-form');
        self::assertCount(1, $form);
        self::assertSame(
            ['_username', '_password', '_remember_me', '_csrf_token'],
            $form->filter('input')->each(static fn (Crawler $input): string => (string) $input->attr('name')),
        );
        self::assertCount(1, $form->filter('a.c-login-form__forgot'));
    }

    public function testThePasswordPagesHaveTheSameLook(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/resetpassword');

        self::assertResponseIsSuccessful();
        self::assertSame($this->trans('password.request.headline'), $crawler->filter('h1.p-login__title')->text());
        self::assertCount(1, $crawler->filter('form#reset-password-form.c-login-form input.c-login-form__input[autofocus]'));
        self::assertStringEndsWith('/login', (string) $crawler->filter('.p-login__switch a')->attr('href'));
    }

    private function trans(string $id, array $parameters = []): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters);
    }
}
