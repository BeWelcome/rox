<?php

namespace App\Tests\Model;

use App\Entity\SignupPattern;
use App\Model\ShadowBanModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * How a sign-up pattern matches a username or e-mail address: anywhere, upper or lower case, * for any characters,
 * other characters as they are; only in the username, the address or both.
 */
final class ShadowBanMatchTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string, string, ?string}>
     */
    public static function cases(): iterable
    {
        yield 'a number added' => ['janev', 'both', 'janev12', 'x@example.org', 'username janev12'];
        yield 'upper case' => ['janev', 'both', 'JaneV7', 'x@example.org', 'username JaneV7'];
        yield 'in the address' => ['janev', 'both', 'traveller', 'janev77@mail.example', 'email janev77@mail.example'];
        yield 'a star' => ['jane*v', 'both', 'jane_doe_v', 'x@example.org', 'username jane_doe_v'];
        yield 'a domain' => ['@spam.example', 'email', 'anna', 'anna1@spam.example', 'email anna1@spam.example'];
        yield 'a dot is a dot' => ['jane.v', 'both', 'janexv', 'x@example.org', null];
        yield 'only the username' => ['janev', 'username', 'traveller', 'janev77@mail.example', null];
        yield 'only the address' => ['janev', 'email', 'janev12', 'x@example.org', null];
        yield 'no match' => ['janev', 'both', 'john', 'john@example.org', null];
    }

    #[DataProvider('cases')]
    public function testThePatternMatches(string $text, string $field, string $username, string $email, ?string $expected): void
    {
        $pattern = new SignupPattern()->setPattern($text)->setField($field);

        self::assertSame($expected, ShadowBanModel::match($pattern, $username, $email));
    }
}
