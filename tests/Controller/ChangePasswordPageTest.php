<?php

namespace App\Tests\Controller;

use App\Repository\MemberRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The change password page: the current and the new password, each with its eye, the strength bars under the new one.
 */
#[Group('integration')]
class ChangePasswordPageTest extends WebTestCase
{
    public function testThePageShowsBothFieldsWithTheirEyeAndTheStrengthBars(): void
    {
        $client = static::createClient();
        $client->loginUser(static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2'));

        $crawler = $client->request('GET', '/members/member-2/password/change');

        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form.p-pw-form');
        self::assertCount(1, $form);
        self::assertCount(2, $form->filter('input[type="password"]'));
        self::assertCount(2, $form->filter('.c-login-form__password .js-password-show-hide'));
        self::assertCount(1, $form->filter('input.js-password-input'));
        self::assertCount(5, $form->filter('.js-password-strength > div'));
    }
}
