<?php

namespace App\Command;

use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
    protected function configure(): void
    {
        $this
            ->addArgument('filename', InputArgument::REQUIRED, 'filename containing the missing translations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('filename');

        $io->section(sprintf('Importing missing translations from file: %s', $filename));

        $memberRepository = $this->entityManager->getRepository(Member::class);
        $admin = $memberRepository->find(1);

        $languageRepository = $this->entityManager->getRepository(Language::class);
        $english = $languageRepository->findOneBy(['shortcode' => 'en']);

        $translationRepository = $this->entityManager->getRepository(Word::class);

        $yaml = Yaml::parseFile($filename);

        $missing = $yaml['translations']['missing'];
        $count = 0;

        foreach ($missing as $translationId => $value)
        {
            $sentence = $value[0];
            $description = $value[1] ?? 'No description given.';

            $translation = $translationRepository->findOneBy(['code' => $translationId]);
            if (null === $translation)
            {
                $count++;
                $io->note(sprintf("Adding %s: %s", $translationId, $sentence));

                $translation = new Word();
                $translation->setCode($translationId);
                $translation->setDescription($description);
                $translation->setSentence($sentence);
                $translation->setDomain("messages");
                $translation->setLanguage($english);
                $translation->setAuthor($admin);

                $this->entityManager->persist($translation);
            } else {
                $this->entityManager->detach($translation);
            }
        }
        if ($count == 0) {
            $io->success(sprintf('All translations in %s were already imported.', $filename));

            return Command::SUCCESS;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Finished importing the translations in %s into the database', $filename));

        $command = $this->getApplication()->find('cache:clear');

        $clearCache = new ArrayInput([]);

        $io->note('clearing cache to force update of translations');

        return $command->run($clearCache, new NullOutput());
    }
}
