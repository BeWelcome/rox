<?php

namespace App\Tests\Model\MemberDataExtractor;

use App\Doctrine\DomainType;
use App\Doctrine\TranslationAllowedType;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Word;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The newsletters page of the personal data export: the English version of each newsletter the member created, and
 * every version, of any newsletter, they updated last. The texts are words (broadcast_title_<name>,
 * broadcast_body_<name>); the page is rendered with strict variables, as in the dev environment.
 */
#[Group('integration')]
class NewslettersExtractorTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    public function testTheNewslettersPageShowsTheMembersVersions(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $this->member('member-2');
        $other = $this->member('member-3');

        // member-2's newsletter: English last updated by someone else (shown: their newsletter), French by someone
        // else (not shown), German by member-2 (shown)
        $this->newsletter('zztest_own', $member, '2026-10-01');
        $this->version('zztest_own', 'en', 'Zztest own English', $other);
        $this->version('zztest_own', 'fr', 'Zztest own French', $other);
        $this->version('zztest_own', 'de', 'Zztest own German', $member);
        // someone else's newsletter: English by them (not shown), Spanish last updated by member-2 (shown)
        $this->newsletter('zztest_other', $other, '2026-09-01');
        $this->version('zztest_other', 'en', 'Zztest other English', $other);
        $this->version('zztest_other', 'es', 'Zztest other Spanish', $member);
        $this->entityManager->flush();

        [$counts, $page] = $this->extract($member);

        self::assertSame(['newsletters' => 3], $counts);
        $titles = new Crawler($page)->filter('.c-mydata-card__title')->each(static fn (Crawler $title): string => trim($title->text()));
        self::assertSame(['Zztest own English', 'Zztest own German', 'Zztest other Spanish'], $titles);
        // without the empty paragraphs and runs of <br> of the newsletter's source
        $text = new Crawler($page)->filter('.c-mydata-card__text')->first()->html();
        self::assertStringNotContainsString('<p></p>', $text);
        self::assertStringNotContainsString('<br><br><br>', str_replace(["\n", ' '], '', $text));
    }

    private function newsletter(string $name, Member $creator, string $created): void
    {
        $this->entityManager->persist(new Newsletter()
            ->setName($name)
            ->setType(Newsletter::REGULAR_NEWSLETTER)
            ->setCreatedBy($creator)
            ->setCreated(new DateTime($created)));
    }

    private function version(string $name, string $locale, string $title, Member $author): void
    {
        $texts = [
            'Broadcast_title_' . $name => $title,
            'Broadcast_body_' . $name => '<p>' . $title . ' text.</p><p></p><p>&nbsp;</p>Line<br><br><br><br>after',
        ];
        foreach ($texts as $code => $sentence) {
            $this->entityManager->persist(new Word()
                ->setCode($code)
                ->setLanguage($this->entityManager->getRepository(Language::class)->findOneBy(['shortCode' => $locale]))
                ->setSentence($sentence)
                ->setDomain(DomainType::MESSAGES)
                ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
                ->setDescription('')
                ->setAuthor($author)
                ->setCreated(new DateTime())
                ->setUpdated(new DateTime()));
        }
    }

    /**
     * @return array{array<string, int>, string}
     */
    private function extract(Member $member): array
    {
        static::getContainer()->get('twig')->enableStrictVariables();
        static::getContainer()->get('translator')->getCatalogue('en')->set(
            'mydata.generated',
            'Data extracted on {date_generated, date, short} at {date_generated, time, short}.',
            'messages+intl-icu',
        );
        $directory = sys_get_temp_dir() . '/zztest_newsletters_' . uniqid() . '/';
        mkdir($directory);

        try {
            $counts = static::getContainer()->get('app.member_data_extractor.newsletters')->extract($member, $directory);

            return [$counts, (string) file_get_contents($directory . 'newsletters.html')];
        } finally {
            new Filesystem()->remove($directory);
        }
    }

    private function member(string $username): Member
    {
        return $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}
