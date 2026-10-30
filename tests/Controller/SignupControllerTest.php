<?php

namespace App\Tests\Controller;

use App\Entity\Group as GroupEntity;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('integration')]
/**
 * @infection-ignore-all
 */
class SignupControllerTest extends WebTestCase
{
    public function testSignupPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/signup');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'signup'); // Translation key or fallback
    }

    public function testSignupSubmitValidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/signup');

        // Select the form using the submit button
        $button = $crawler->filter('input[type="submit"]');
        $form = $button->form();

        $form['signup_form[username]'] = 'testuser' . uniqid();
        $form['signup_form[email]'] = 'testuser' . uniqid() . '@example.com';
        $form['signup_form[password]'] = 'StrongPassword123!';
        $form['signup_form[terms_privacy]'] = '1';

        $client->submit($form);

        // Expect redirection or success message.
        // Based on controller: return $security->login($member, 'form_login');
        // This usually results in a redirect or a 200 OK on the new page.
        // If successful, it might redirect to homepage or profile.
        $this->assertResponseStatusCodeSame(302);
    }

    public function testNewMembersAreInTheBeWelcomeGroupWithNotifications(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/signup');
        $form = $crawler->filter('input[type="submit"]')->form();
        $username = 'newbie' . substr(uniqid(), -8);
        $form['signup_form[username]'] = $username;
        $form['signup_form[email]'] = $username . '@example.com';
        $form['signup_form[password]'] = 'StrongPassword123!';
        $form['signup_form[terms_privacy]'] = '1';

        $client->submit($form);

        self::assertResponseRedirects();
        $connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $membership = $connection->fetchAssociative(
            'SELECT gm.status, gm.notifications_enabled, gm.welcome_notice_sent FROM group_membership gm'
            . ' JOIN member m ON m.id = gm.member_id WHERE m.Username = ? AND gm.group_id = ?',
            [$username, GroupEntity::BEWELCOME],
        );
        self::assertSame(
            ['status' => 'In', 'notifications_enabled' => 1, 'welcome_notice_sent' => 0],
            array_map(static fn ($value) => is_numeric($value) ? (int) $value : $value, (array) $membership),
        );
    }

    public function testSignupSubmitInvalidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/signup');

        $button = $crawler->filter('input[type="submit"]');
        $form = $button->form();

        // 1. Submit invalid password (too short)
        $form['signup_form[username]'] = 'shortpassUser';
        $form['signup_form[email]'] = 'shortpass@example.com';
        $form['signup_form[password]'] = '123'; // Min 8 chars
        $form['signup_form[terms_privacy]'] = '1';

        $client->submit($form);

        // Should return 200 OK (content rendered with errors) but NOT redirect
        $this->assertResponseStatusCodeSame(200);
        // Verify we are still on the signup page
        $this->assertSelectorTextContains('h1', 'signup');
    }

    public function testSignupSubmitDuplicateData(): void
    {
        $client = static::createClient();

        // 1. Create a user
        $crawler = $client->request('GET', '/signup');
        $button = $crawler->filter('input[type="submit"]');
        $form = $button->form();

        $uniqueUser = 'duplicate' . uniqid();
        $form['signup_form[username]'] = $uniqueUser;
        $form['signup_form[email]'] = $uniqueUser . '@example.com';
        $form['signup_form[password]'] = 'StrongPassword123!';
        $form['signup_form[terms_privacy]'] = '1';

        $client->submit($form);
        $this->assertResponseStatusCodeSame(302);

        // 2. Try to create same user again
        $client->restart(); // Clear cookies/session to simulate new user
        $crawler = $client->request('GET', '/signup');
        $button = $crawler->filter('input[type="submit"]');
        $form = $button->form();

        $form['signup_form[username]'] = $uniqueUser;
        $form['signup_form[email]'] = 'other' . uniqid() . '@example.com';
        $form['signup_form[password]'] = 'StrongPassword123!';
        $form['signup_form[terms_privacy]'] = '1';

        $client->submit($form);

        // Should fail (not redirect)
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'signup');
    }
}
