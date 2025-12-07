<?php

namespace App\Tests\Model;

use App\Entity\Word;
use App\Model\TranslationModel;
use App\Pagerfanta\MissingTranslationAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationModelTest extends TestCase
{
    private $entityManager;
    private $filesystem;
    private $translator;
    private $model;
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->translator = $this->createStub(TranslatorInterface::class);
        $this->cacheDir = sys_get_temp_dir() . '/bw_test_cache';

        if (!is_dir($this->cacheDir . '/translations')) {
            mkdir($this->cacheDir . '/translations', 0o777, true);
        }

        $this->model = new TranslationModel(
            $this->translator,
            $this->entityManager,
            $this->filesystem,
            $this->cacheDir,
            ['en']
        );
    }

    public function testGetAdapterReturnsCorrectType(): void
    {
        // Behavioral test: Input 'missing' -> Output MissingTranslationAdapter
        $this->entityManager->method('getConnection')->willReturn($this->createStub(Connection::class));

        $adapter = $this->model->getAdapter('missing', 'en', 'code');
        $this->assertInstanceOf(MissingTranslationAdapter::class, $adapter);
    }

    public function testRefreshTranslationsCacheTriggersFilesystem(): void
    {
        // Behavioral side-effect: MUST create directory and remove old files.
        // We verify critical side-effects, but allow flexibility in how many times or exact order if possible.
        // Here we just check 'mkdir' is called.
        $this->filesystem->expects($this->atLeastOnce())->method('mkdir');

        // We also want to ensure the translator might be warmed up, but that depends on interface.
        // If our stub doesn't implement WarmableInterface, it won't be called.
        // We accept that behavior for this test to keep it simple.

        $this->model->refreshTranslationsCacheForLocale('en');
    }

    public function testUpdateDomainOfTranslationsPersistsData(): void
    {
        // Behavioral side-effect: Data must be saved.
        // We use createMock because we explicitly want to verify the 'persist' and 'flush' calls.
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Re-inject the mock into the model (since the model was created in setUp with a stub)
        $this->model = new TranslationModel(
            $this->translator,
            $this->entityManager,
            $this->filesystem,
            $this->cacheDir,
            ['en']
        );

        $repo = $this->createStub(EntityRepository::class);
        $wordToUpdate = new Word();
        $wordToUpdate->setDomain('old');
        // Retrieve existing
        $repo->method('findBy')->willReturn([$wordToUpdate]);

        $this->entityManager->method('getRepository')->willReturn($repo);

        // Assert persist and flush are called
        $this->entityManager->expects($this->atLeastOnce())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $inputWord = new Word();
        $inputWord->setCode('test');
        $inputWord->setDomain('new');

        $this->model->updateDomainOfTranslations($inputWord);

        // State verification: did the object update?
        $this->assertEquals('new', $wordToUpdate->getDomain());
    }
}
