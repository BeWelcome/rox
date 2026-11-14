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
 * Editing a field of another profile language shows the same field in the member's language (else English) below
 * it, to translate from: in a card headed by that language's own name, and not at all where it has no text yet.
 */
#[Group('integration')]
class ProfileTranslationReferenceTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // member-2: "About me" in English, a Finnish version, no English "Occupation"
        $entityManager = $this->entityManager();
        $member = $this->member();
        foreach ([['en', 'AboutMe', 'This is me in English.'], ['fi', 'ProfileLanguage', 'fi'], ['fi', 'AboutMe', 'Tämä olen minä.']] as [$locale, $field, $content]) {
            $translation = $entityManager->getRepository(MemberTranslation::class)
                ->findOneBy(['object' => $member, 'locale' => $locale, 'field' => $field]);
            if (null === $translation) {
                $translation = new MemberTranslation($locale, $field, $content);
                $translation->setObject($member);
                $entityManager->persist($translation);
            }
            $translation->setContent($content);
        }
        foreach ($entityManager->getRepository(MemberTranslation::class)->findBy(['object' => $member, 'locale' => 'en', 'field' => 'Occupation']) as $occupation) {
            $entityManager->remove($occupation);
        }
        $entityManager->flush();
        $entityManager->clear();
        $this->client->loginUser($this->member());
    }

    public function testTheFieldInEnglishIsShownBelowTheFieldToTranslate(): void
    {
        $crawler = $this->client->request('GET', '/members/member-2/edit/fi/aboutme?field=about');

        self::assertResponseIsSuccessful();
        $reference = $crawler->filter('.o-field-translation:has(textarea[data-field=AboutMe]) .o-field-reference');
        self::assertCount(1, $reference);
        self::assertSame('en', $reference->attr('lang'));
        self::assertSame(static::getContainer()->get(LanguageNames::class)->native('en'), trim($reference->filter('.o-field-reference__head')->text()));
        self::assertStringContainsString('This is me in English.', $reference->filter('.o-field-reference__text')->text());
    }

    public function testAFieldWithoutEnglishTextHasNoReference(): void
    {
        $crawler = $this->client->request('GET', '/members/member-2/edit/fi/aboutme?field=occupation');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.o-field-translation:has([data-field=Occupation]) .o-field-reference'));
    }

    public function testEditingTheEnglishVersionShowsNoReference(): void
    {
        $crawler = $this->client->request('GET', '/members/member-2/edit/en/aboutme?field=about');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.o-field-reference'));
    }

    private function member(): Member
    {
        return $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
