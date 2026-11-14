<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\MemberTranslation;
use App\Service\LanguageNames;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The language switch at the top of the profile pages: other members see the profile's languages that have text;
 * the member sees all of their profile languages, also one added without text yet. The edit page has its own.
 */
#[Group('integration')]
class ProfileLanguageSwitchTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // member-2 with an English text and a Finnish profile language that has no text yet (only its marker)
        $entityManager = $this->entityManager();
        $member = $this->member('member-2');
        foreach ([['en', 'ProfileLanguage', 'en'], ['en', 'Occupation', 'Tester'], ['fi', 'ProfileLanguage', 'fi']] as [$locale, $field, $content]) {
            $translation = $entityManager->getRepository(MemberTranslation::class)
                ->findOneBy(['object' => $member, 'locale' => $locale, 'field' => $field]);
            if (null === $translation) {
                $translation = new MemberTranslation($locale, $field, $content);
                $translation->setObject($member);
                $entityManager->persist($translation);
            }
        }
        $entityManager->flush();
        $entityManager->clear();
    }

    public function testTheMemberSeesAllTheirProfileLanguages(): void
    {
        $this->client->loginUser($this->member('member-2'));

        foreach (['/members/member-2', '/members/member-2/comments'] as $url) {
            $crawler = $this->client->request('GET', $url);

            self::assertResponseIsSuccessful();
            $languages = $crawler->filter('.p-lang-switch .p-lang-option')->each(static fn ($option) => $option->attr('data-lang'));
            self::assertContains('fi', $languages, 'the profile language without text is missing on ' . $url);
            self::assertContains('en', $languages);
        }
        $option = $crawler->filter('.p-lang-switch .p-lang-option[data-lang=fi]');
        self::assertSame(static::getContainer()->get(LanguageNames::class)->native('fi'), trim($option->text()));
    }

    public function testOthersSeeOnlyLanguagesWithText(): void
    {
        $this->client->loginUser($this->member('member-3'));

        $crawler = $this->client->request('GET', '/members/member-2');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.p-lang-switch .p-lang-option[data-lang=fi]'));
    }

    public function testTheEditPageHasNoSwitch(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/members/member-2/edit');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.p-lang-switch'));
    }

    private function member(string $username): Member
    {
        return $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
