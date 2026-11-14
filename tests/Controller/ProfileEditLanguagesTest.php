<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Service\LanguageNames;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The languages edit page: another member's is sent to the own one, in the same language; the languages show their
 * own names, found also by the name in the member's language, spoken languages first (as on the finish setup page).
 */
#[Group('integration')]
class ProfileEditLanguagesTest extends WebTestCase
{
    public function testAnotherMembersLanguagesPageRedirectsToTheOwnOne(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $client->loginUser($member);

        $client->request('GET', '/members/member-5/edit/de/languages');

        $this->assertResponseRedirects('/members/member-2/edit/de/languages');
    }

    public function testTheLanguagesHaveTheirOwnNames(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $client->loginUser($member);

        $crawler = $client->request('GET', '/members/member-2/edit/en/languages');

        $this->assertResponseIsSuccessful();
        $names = static::getContainer()->get(LanguageNames::class);
        $translator = static::getContainer()->get(TranslatorInterface::class);
        $languages = json_decode((string) $crawler->filter('.js-lang-add')->attr('data-languages'), true);
        self::assertTrue(array_is_list($languages));
        $french = array_column($languages, null, 'code')['fr'];
        self::assertSame(['code' => 'fr', 'name' => $names->native('fr'), 'alt' => $translator->trans('lang_fr'), 'group' => 'spoken'], $french);
        $groups = array_column($languages, 'group');
        self::assertSame($groups, array_merge(
            array_filter($groups, static fn ($group) => 'spoken' === $group),
            array_filter($groups, static fn ($group) => 'signed' === $group),
        ));
        self::assertSame(['spoken', 'signed'], array_keys(json_decode((string) $crawler->filter('.js-lang-add')->attr('data-groups'), true)));
        self::assertSame($names->native('fr'), trim($crawler->filter('.js-lang-common[data-code=fr]')->text()));
        // the member's languages: the own name on each card
        $crawler->filter('.js-lang-list .js-lang-card')->each(static function ($card) use ($names) {
            self::assertSame($names->native($card->attr('data-language')), trim($card->filter('.js-lang-card-name')->text()));
        });
    }

    public function testTheEditPageListsTheSpokenLanguagesByTheirOwnNames(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $client->loginUser($member);

        $crawler = $client->request('GET', '/members/member-2/edit');

        $this->assertResponseIsSuccessful();
        $names = static::getContainer()->get(LanguageNames::class);
        $items = $crawler->filter('.p-pe-langs__name');
        self::assertGreaterThan(0, $items->count(), 'member-2 has no spoken languages in the fixtures');
        $items->each(static function ($item) use ($names) {
            self::assertSame($names->native((string) $item->attr('lang')), trim($item->text()));
        });
    }

    public function testTheAddLanguagePageNamesTheLanguagesByTheirOwnNames(): void
    {
        // a new profile language: the written languages by their own names, without the HTML entities of language.Name
        $client = static::createClient();
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $client->loginUser($member);

        $crawler = $client->request('GET', '/members/member-2/add/language');

        $this->assertResponseIsSuccessful();
        $names = static::getContainer()->get(LanguageNames::class);
        $options = $crawler->filter('select[name="add_language_form[language]"] option[value!=""]');
        self::assertGreaterThan(0, $options->count());
        $labels = $options->each(static function ($option) use ($names) {
            self::assertSame($names->native((string) $option->attr('value')), trim($option->text()));
            self::assertStringNotContainsString('&#', $option->html());

            return trim($option->text());
        });
        $sorted = $labels;
        sort($sorted);
        self::assertSame($sorted, $labels);
        self::assertSame(
            static::getContainer()->get(TranslatorInterface::class)->trans('profile.edit.add.translation'),
            trim($crawler->filter('.p-edit-subpage__title, h1')->first()->text()),
        );
    }
}
