<?php

namespace App\TranslationLoader;

use App\Entity\Language;
use App\Entity\Word;
use App\Repository\WordRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DatabaseLoaderTest extends TestCase
{
    /**
     * @test
     */
    public function emptyResult()
    {
        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([ ]);

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn( $repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'en');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
        $this->assertSame('en', $result->getLocale());

        // No translations in the message catalogue so get will return the same
        $translation = $result->get('english');
        $this->assertSame('english', $translation);
    }

    /**
     * @test
     */
    public function checkReturnedObjectTypeEnglish()
    {
        $english = new Word();
        $english->setCode('english');
        $english->setSentence('English');

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([ $english ]);

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn( $repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'en');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
        $this->assertSame('en', $result->getLocale());

        $translation = $result->get('english');
        $this->assertSame('English', $translation);
    }

    /**
     * @test
     */
    public function checkReturnedObjectTypeGerman()
    {
        $english = new Word();
        $english->setCode('deutsch');
        $english->setSentence('Deutsch');

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([ $english ]);

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn( $repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
        $this->assertSame('de', $result->getLocale());
    }

    /**
     * @test
     */
    public function checkMajorUpdate()
    {
        $englishEn = new Word();
        $englishEn->setCode('english');
        $englishEn->setLanguage( (new Language())->setShortcode('en'));
        $englishEn->setSentence('English');
        $englishEn->setMajorUpdate(new DateTime('2020-09-01 00:00'));

        $englishDe = new Word();
        $englishDe->setCode('english');
        $englishDe->setLanguage( (new Language())->setShortcode('de'));
        $englishDe->setSentence('Englisch');
        $englishDe->setUpdated(new DateTime('2020-08-31 00:00'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->will($this->onConsecutiveCalls([$englishEn], [$englishDe]));

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn( $repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');
        $translation = $result->get('english');

        $this->assertSame('English', $translation);
    }

    /**
     * @test
     */
    public function checkNoMajorUpdate()
    {
        $englishEn = new Word();
        $englishEn->setCode('english');
        $englishEn->setLanguage( (new Language())->setShortcode('en'));
        $englishEn->setSentence('English');
        $englishEn->setMajorUpdate(new DateTime('2020-09-01 00:00'));

        $englishDe = new Word();
        $englishDe->setCode('english');
        $englishDe->setLanguage( (new Language())->setShortcode('de'));
        $englishDe->setSentence('Englisch');
        $englishDe->setUpdated(new DateTime('2020-09-01 00:01'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->will($this->onConsecutiveCalls([$englishEn], [$englishDe]));

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn( $repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');
        $translation = $result->get('english');

        $this->assertSame('Englisch', $translation);
    }

    /**
     * @test
     */
    public function checkMajorUpdateComplex()
    {
        $englishEn = new Word();
        $englishEn->setCode('english');
        $englishEn->setLanguage( (new Language())->setShortcode('en'));
        $englishEn->setSentence('English');
        $englishEn->setMajorUpdate(new DateTime('2020-09-01 00:00'));

        $englishDe = new Word();
        $englishDe->setCode('english');
        $englishDe->setLanguage( (new Language())->setShortcode('de'));
        $englishDe->setSentence('Englisch');
        $englishDe->setUpdated(new DateTime('2020-09-01 00:01'));

        $germanEn = new Word();
        $germanEn->setCode('german');
        $germanEn->setLanguage( (new Language())->setShortcode('en'));
        $germanEn->setSentence('German');
        $germanEn->setMajorUpdate(new DateTime('2020-09-01 00:01'));

        $germanDe = new Word();
        $germanDe->setCode('german');
        $germanDe->setLanguage( (new Language())->setShortcode('de'));
        $germanDe->setSentence('Deutsch');
        $germanDe->setUpdated(new DateTime('2020-09-00 00:00'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->will($this->onConsecutiveCalls([$englishEn, $germanEn], [$englishDe, $germanDe]));

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn( $repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');

        $translation = $result->get('english');
        $this->assertSame('Englisch', $translation);

        $translation = $result->get('german');
        $this->assertSame('German', $translation);
    }
}
