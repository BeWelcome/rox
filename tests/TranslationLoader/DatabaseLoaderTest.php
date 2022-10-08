<?php

namespace App\Tests\TranslationLoader;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Language;
use App\Entity\Word;
use App\Logger\Logger;
use App\Repository\WordRepository;
use App\TranslationLoader\DatabaseLoader;
use DateTime;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DatabaseLoaderTest extends TestCase
{
    public function testEmptyResult()
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

    public function testCheckReturnedObjectTypeEnglish()
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

    public function testCheckReturnedObjectTypeGerman()
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

    public function testCheckMajorUpdate()
    {
        $englishEn = (new Word())
            ->setCode('english')
            ->setLanguage((new Language())->setShortCode('en'))
            ->setSentence('English')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setMajorUpdate(new DateTime('2020-09-01 00:00'))
        ;

        $englishDe = (new Word())
            ->setCode('english')
            ->setLanguage((new Language())->setShortCode('de'))
            ->setSentence('Englisch')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime('2020-08-31 00:00'))
        ;

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->will($this->onConsecutiveCalls([$englishEn], [$englishDe]));

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

    public function testCheckNoMajorUpdate()
    {
        $englishEn = (new Word())
            ->setCode('english')
            ->setLanguage((new Language())->setShortCode('en'))
            ->setSentence('English')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setMajorUpdate(new DateTime('2020-09-01 00:00'))
        ;

        $englishDe = (new Word())
            ->setCode('english')
            ->setLanguage((new Language())->setShortCode('de'))
            ->setSentence('Englisch')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime('2020-09-01 00:01'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->will($this->onConsecutiveCalls([$englishEn], [$englishDe]));

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

    public function testCheckMajorUpdateComplex()
    {
        $englishEn = new Word();
        $englishEn->setCode('english');
        $englishEn->setLanguage((new Language())->setShortCode('en'));
        $englishEn->setSentence('English');
        $englishEn->setMajorUpdate(new DateTime('2020-09-01 00:00'));

        $englishDe = new Word();
        $englishDe->setCode('english');
        $englishDe->setLanguage((new Language())->setShortCode('de'));
        $englishDe->setSentence('Englisch');
        $englishDe->setUpdated(new DateTime('2020-09-01 00:01'));

        $germanEn = new Word();
        $germanEn->setCode('german');
        $germanEn->setLanguage((new Language())->setShortCode('en'));
        $germanEn->setSentence('German');
        $germanEn->setMajorUpdate(new DateTime('2020-09-01 00:01'));

        $germanDe = new Word();
        $germanDe->setCode('german');
        $germanDe->setLanguage((new Language())->setShortCode('de'));
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
            ->willReturn($repositoryStub)
        ;

        $loader = new DatabaseLoader($entityManagerStub);

        $result = $loader->load(null, 'de');

        $translation = $result->get('english');
        $this->assertSame('Englisch', $translation);

        $translation = $result->get('german');
        $this->assertSame('German', $translation);
    }


    public function testCheckTranslationAllowed()
    {
        $englishEn = (new Word())
            ->setCode('english')
            ->setLanguage((new Language())->setShortCode('en'))
            ->setSentence('English')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setMajorUpdate(new DateTime('2020-09-01 00:00'))
        ;

        $englishDe = (new Word())
            ->setCode('english')
            ->setLanguage((new Language())->setShortCode('de'))
            ->setSentence('Englisch')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime('2020-09-01 00:01'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->will($this->onConsecutiveCalls([$englishEn], [$englishDe]));

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

    public function testCheckTranslationNotAllowed()
    {
        $englishEn = (new Word())
            ->setCode('english')
            ->setLanguage((new Language())->setShortCode('en'))
            ->setSentence('English')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_NOT_ALLOWED)
            ->setMajorUpdate(new DateTime('2020-09-01 00:00'))
        ;

        $englishDe = (new Word())
            ->setCode('english')
            ->setLanguage((new Language())->setShortCode('de'))
            ->setSentence('Englisch')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime('2020-09-01 00:01'));

        // Create a stub for the Word repository class.
        $repositoryStub = $this->createStub(WordRepository::class);
        $repositoryStub
            ->method('getTranslationsForLocale')
            ->will($this->onConsecutiveCalls([$englishEn], [$englishDe]));

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
