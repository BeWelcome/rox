<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The password strength check of the signup and change password forms: their script posts username, email address
 * and password. Other requests don't fail.
 */
#[Group('integration')]
class PasswordCheckTest extends WebTestCase
{
    public function testThePostedPasswordIsScored(): void
    {
        $client = static::createClient();

        $client->request('POST', '/password/check/', [
            'username' => 'member-2',
            'email' => 'member-2@example.test',
            'password' => 'correct horse battery staple',
        ]);

        $this->assertResponseIsSuccessful();
        self::assertSame(4, json_decode((string) $client->getResponse()->getContent(), true)['score']);
    }

    public function testAPostWithoutFieldsScoresAnEmptyPassword(): void
    {
        $client = static::createClient();

        $client->request('POST', '/password/check/');

        $this->assertResponseIsSuccessful();
        self::assertSame(0, json_decode((string) $client->getResponse()->getContent(), true)['score']);
    }

    public function testOpeningTheUrlIsNotAllowed(): void
    {
        $client = static::createClient();

        $client->request('GET', '/password/check/');

        $this->assertResponseStatusCodeSame(405);
    }
}
