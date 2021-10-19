<?php

namespace App\Command;

use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class TranslationsAddMissingCommand extends Command
{
    protected static $defaultName = 'translations:add:missing';
    protected static $defaultDescription = 'Add a short description for your command';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $finder = new Finder();
        $files = $finder->files()->name('*.yaml')->in('translations/missing');

        $count = 0;
        foreach ($files as $file) {
            $io->section(sprintf('Importing missing translations from file: %s', $file));

            $memberRepository = $this->entityManager->getRepository(Member::class);
            $admin = $memberRepository->find(1);

            $languageRepository = $this->entityManager->getRepository(Language::class);
            $english = $languageRepository->findOneBy(['shortcode' => 'en']);

            $translationRepository = $this->entityManager->getRepository(Word::class);

            $missing = Yaml::parseFile($file);

            foreach ($missing as $translationId => $missingTranslation) {
                $sentence = $missingTranslation[0];
                $description = $missingTranslation[1] ?? 'No description given.';
                $domain = $missingTranslation[2] ?? 'messages+intl-icu';

                $translation = $translationRepository->findOneBy(['code' => $translationId]);
                if (null === $translation) {
                    $count++;
                    $io->note(sprintf("Adding %s: %s", $translationId, $sentence));

                    $translation = new Word();
                    $translation->setCode($translationId);
                    $translation->setDescription($description);
                    $translation->setSentence($sentence);
                    $translation->setDomain($domain);
                    $translation->setLanguage($english);
                    $translation->setAuthor($admin);

                    $this->entityManager->persist($translation);
                } else {
                    $this->entityManager->detach($translation);
                }
            }
        }

        if ($count == 0) {
            $io->success('All translations in \'translation/missing\' were already imported.');

            return Command::SUCCESS;
        }

        $this->entityManager->flush();

        $io->success('Finished importing the translations into the database');

        $command = $this->getApplication()->find('cache:clear');

        $clearCache = new ArrayInput([]);

        $io->note('clearing cache to force update of translations');

        return $command->run($clearCache, new NullOutput());
    }
}
