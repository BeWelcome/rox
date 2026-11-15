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
use Symfony\Component\Filesystem\Filesystem;

/**
 * The newsletters page of the personal data export: the newsletters the member created, in each language they wrote.
 * Their texts are words (broadcast_title_<name>, broadcast_body_<name>); the page is rendered with strict variables,
 * as in the dev environment, where reading translations of the newsletter entity failed.
 */
#[Group('integration')]
class NewslettersExtractorTest extends KernelTestCase
{
    public function testTheNewslettersPageShowsTheTextsTheMemberWrote(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $author = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $translator = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-3']);
        $newsletter = new Newsletter()
            ->setName('zztest_export')
            ->setType(Newsletter::REGULAR_NEWSLETTER)
            ->setCreatedBy($author)
            ->setCreated(new DateTime('2026-10-01'));
        $entityManager->persist($newsletter);
        // the English version by the author, the French one by someone else
        $texts = [
            ['en', 'Broadcast_title_zztest_export', 'Zztest autumn news', $author],
            ['en', 'Broadcast_body_zztest_export', '<p>Zztest what happened this autumn.</p>', $author],
            ['fr', 'Broadcast_title_zztest_export', 'Zztest nouvelles', $translator],
            ['fr', 'Broadcast_body_zztest_export', '<p>Zztest ce qui est arrivé.</p>', $translator],
        ];
        foreach ($texts as [$locale, $code, $sentence, $member]) {
            $entityManager->persist(new Word()
                ->setCode($code)
                ->setLanguage($entityManager->getRepository(Language::class)->findOneBy(['shortCode' => $locale]))
                ->setSentence($sentence)
                ->setDomain(DomainType::MESSAGES)
                ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
                ->setDescription('')
                ->setAuthor($member)
                ->setCreated(new DateTime())
                ->setUpdated(new DateTime()));
        }
        $entityManager->flush();
        static::getContainer()->get('twig')->enableStrictVariables();
        static::getContainer()->get('translator')->getCatalogue('en')->set(
            'mydata.generated',
            'Data extracted on {date_generated, date, short} at {date_generated, time, short}.',
            'messages+intl-icu',
        );
        $directory = sys_get_temp_dir() . '/zztest_newsletters_' . uniqid() . '/';
        mkdir($directory);

        try {
            $counts = static::getContainer()->get('app.member_data_extractor.newsletters')->extract($author, $directory);
            $page = (string) file_get_contents($directory . 'newsletters.html');
        } finally {
            new Filesystem()->remove($directory);
        }

        self::assertSame(['newsletters' => 1], $counts);
        self::assertStringContainsString('Zztest autumn news', $page);
        self::assertStringContainsString('Zztest what happened this autumn.', $page);
        // not the translation by someone else
        self::assertStringNotContainsString('Zztest nouvelles', $page);
    }
}
