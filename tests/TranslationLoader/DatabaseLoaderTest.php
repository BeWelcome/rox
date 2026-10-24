<?php

namespace App\Tests\TranslationLoader;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Language;
use App\Entity\Word;
use App\Repository\WordRepository;
use App\TranslationLoader\DatabaseLoader;
use DateTime;
use Doctrine\DBAL\Driver\PDO\Exception as PdoDriverException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManager;
use PDOException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseLoaderTest extends TestCase
{
    public function testEmptyResult(): void
    {
        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([]);

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'en');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
        $this->assertSame('en', $result->getLocale());

        // No translations in the message catalogue so get will return the same
        $translation = $result->get('english');
        $this->assertSame('english', $translation);
    }

    public function testMissingWordTableReturnsEmptyCatalogue(): void
    {
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willThrowException(new TableNotFoundException(
                PdoDriverException::new(new PDOException("Table 'bewelcome.word' doesn't exist")),
                null,
            ));

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $english = $loader->load(null, 'en');
        $this->assertSame('en', $english->getLocale());
        $this->assertSame([], $english->all());

        $german = $loader->load(null, 'de');
        $this->assertSame('de', $german->getLocale());
        $this->assertSame([], $german->all());
    }

    // prod (no debug): a cache must not be built without the database translations
    public function testOtherDatabaseErrorsAreNotSwallowed(): void
    {
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willThrowException(new ConnectionException(
                PdoDriverException::new(new PDOException('Connection refused')),
                null,
            ));

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $this->expectException(ConnectionException::class);
        $loader->load(null, 'en');
    }

    public function testUnreachableDatabaseFallsBackToFileTranslationsInDebug(): void
    {
        $repositoryMock = $this->createMock(WordRepository::class);
        $repositoryMock
            ->expects($this->once())
            ->method('getTranslationsForLocale')
            ->willThrowException(new ConnectionException(
                PdoDriverException::new(new PDOException('getaddrinfo for db failed')),
                null,
            ));

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryMock)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $loader = new DatabaseLoader($entityManagerStub, true, $logger);

        // the second locale doesn't try the database again
        $this->assertSame([], $loader->load(null, 'en')->all());
        $this->assertSame([], $loader->load(null, 'de')->all());
    }

    public function testCheckReturnedObjectTypeEnglish(): void
    {
        $english = new Word();
        $english->setCode('english');
        $english->setSentence('English');

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([$english]);

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'en');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
        $this->assertSame('en', $result->getLocale());

        $translation = $result->get('english');
        $this->assertSame('English', $translation);
    }

    public function testCheckReturnedObjectTypeGerman(): void
    {
        $english = new Word();
        $english->setCode('deutsch');
        $english->setSentence('Deutsch');

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([$english]);

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');

        $this->assertInstanceOf(MessageCatalogue::class, $result);
        $this->assertSame('de', $result->getLocale());
    }

    public function testCheckMajorUpdate(): void
    {
        $englishEn = new Word()
            ->setCode('english')
            ->setLanguage(new Language()->setShortCode('en'))
            ->setSentence('English')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setMajorUpdate(new DateTime('2020-09-01 00:00'))
        ;

        $englishDe = new Word()
            ->setCode('english')
            ->setLanguage(new Language()->setShortCode('de'))
            ->setSentence('Englisch')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime('2020-08-31 00:00'))
        ;

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([$englishEn], [$englishDe])
        ;

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');
        $translation = $result->get('english');

        $this->assertSame('English', $translation);
    }

    public function testCheckNoMajorUpdate(): void
    {
        $englishEn = new Word()
            ->setCode('english')
            ->setLanguage(new Language()->setShortCode('en'))
            ->setSentence('English')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setMajorUpdate(new DateTime('2020-09-01 00:00'))
        ;

        $englishDe = new Word()
            ->setCode('english')
            ->setLanguage(new Language()->setShortCode('de'))
            ->setSentence('Englisch')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime('2020-09-01 00:01'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([$englishEn], [$englishDe])
        ;

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');
        $translation = $result->get('english');

        $this->assertSame('Englisch', $translation);
    }

    public function testCheckMajorUpdateComplex(): void
    {
        $englishEn = new Word();
        $englishEn->setCode('english');
        $englishEn->setLanguage(new Language()->setShortCode('en'));
        $englishEn->setSentence('English');
        $englishEn->setMajorUpdate(new DateTime('2020-09-01 00:00'));

        $englishDe = new Word();
        $englishDe->setCode('english');
        $englishDe->setLanguage(new Language()->setShortCode('de'));
        $englishDe->setSentence('Englisch');
        $englishDe->setUpdated(new DateTime('2020-09-01 00:01'));

        $germanEn = new Word();
        $germanEn->setCode('german');
        $germanEn->setLanguage(new Language()->setShortCode('en'));
        $germanEn->setSentence('German');
        $germanEn->setMajorUpdate(new DateTime('2020-09-01 00:01'));

        $germanDe = new Word();
        $germanDe->setCode('german');
        $germanDe->setLanguage(new Language()->setShortCode('de'));
        $germanDe->setSentence('Deutsch');
        $germanDe->setUpdated(new DateTime('2020-09-00 00:00'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([$englishEn, $germanEn], [$englishDe, $germanDe])
        ;

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');

        $translation = $result->get('english');
        $this->assertSame('Englisch', $translation);

        $translation = $result->get('german');
        $this->assertSame('German', $translation);
    }

    public function testCheckTranslationAllowed(): void
    {
        $englishEn = new Word()
            ->setCode('english')
            ->setLanguage(new Language()->setShortCode('en'))
            ->setSentence('English')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setMajorUpdate(new DateTime('2020-09-01 00:00'))
        ;

        $englishDe = new Word()
            ->setCode('english')
            ->setLanguage(new Language()->setShortCode('de'))
            ->setSentence('Englisch')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime('2020-09-01 00:01'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([$englishEn], [$englishDe])
        ;

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');
        $translation = $result->get('english');

        $this->assertSame('Englisch', $translation);
    }

    public function testCheckTranslationNotAllowed(): void
    {
        $englishEn = new Word()
            ->setCode('english')
            ->setLanguage(new Language()->setShortCode('en'))
            ->setSentence('English')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_NOT_ALLOWED)
            ->setMajorUpdate(new DateTime('2020-09-01 00:00'))
        ;

        $englishDe = new Word()
            ->setCode('english')
            ->setLanguage(new Language()->setShortCode('de'))
            ->setSentence('Englisch')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime('2020-09-01 00:01'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->willReturn([$englishEn], [$englishDe])
        ;

        $entityManagerStub = $this->createStub(EntityManager::class);
        $entityManagerStub
            ->method('getRepository')
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');
        $translation = $result->get('english');

        $this->assertSame('English', $translation);
    }
}
