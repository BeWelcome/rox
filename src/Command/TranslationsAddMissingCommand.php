<?php

namespace App\Command;

use App\Model\Translation\MissingTranslation;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'translations:add:missing',
    aliases: [],
    hidden: false,
)]
class TranslationsAddMissingCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Adding missing translations');

        $finder = new Finder();
        $files = $finder->files()->name('*.yaml')->in('translations/missing');

        $count = 0;
        $lastFile = '';

        // Plain SQL with the columns the word table has: at the start of a container the texts are added before the
        // migrations of a deployment ran (docker/php/docker-entrypoint.sh), which may add columns to word.
        $connection = $this->connection;
        $hasFormat = \array_key_exists('format', $connection->createSchemaManager()->listTableColumns('word'));
        $format = $hasFormat ? ', format' : '';

        foreach ($files as $file) {
            $missing = Yaml::parseFile($file);
            foreach ($missing as $translationId => $entry) {
                // a list (text, description, domain, do not translate) or a map that can also give the format
                try {
                    $missingTranslation = MissingTranslation::fromYaml((string) $translationId, $entry);
                } catch (InvalidArgumentException $exception) {
                    $io->error(\sprintf('%s: %s', $file, $exception->getMessage()));

                    return Command::FAILURE;
                }
                $sentence = $missingTranslation->text;

                if (false !== $connection->fetchOne('SELECT 1 FROM word WHERE code = ? LIMIT 1', [$translationId])) {
                    continue;
                }

                if ($lastFile !== $file) {
                    $io->section(\sprintf('Importing missing translations from file: %s', $file));
                    $lastFile = $file;
                }

                ++$count;
                if ('@' === $sentence[0]) {
                    $offset = ('-' === $sentence[1]) ? 2 : 1;
                    $reusedTranslationId = substr((string) $sentence, $offset);
                    $io->note(\sprintf('Adding %s: Reusing %s', $translationId, $reusedTranslationId));
                    $connection->executeStatement(
                        'INSERT INTO word (code, domain, ShortCode, Sentence, donottranslate, Description, author_id, updated, created, TranslationPriority, isarchived, majorupdate' . $format . ')
                         SELECT :translationId, domain, ShortCode, Sentence, donottranslate, Description, NULL, updated, created, TranslationPriority, isarchived, majorupdate' . $format . '
                         FROM word
                         WHERE code = :reusedTranslationId',
                        ['translationId' => $translationId, 'reusedTranslationId' => $reusedTranslationId]
                    );

                    // We had a translation id that started with @-.
                    if (2 === $offset) {
                        $io->note(\sprintf('Deleting %s.', $reusedTranslationId));
                        // Delete existing translations, so that missing translations can be found
                        $connection->executeStatement('DELETE FROM word WHERE code = ?', [$reusedTranslationId]);
                    }
                } else {
                    $io->note(\sprintf('Adding %s: %s', $translationId, $sentence));

                    $values = [
                        'code' => $translationId,
                        'ShortCode' => 'en',
                        'Sentence' => $sentence,
                        'Description' => $missingTranslation->description,
                        'domain' => $missingTranslation->domain,
                        'donottranslate' => $missingTranslation->translationAllowed,
                        // no author: the system added it (the statistics show it as such)
                        'author_id' => null,
                        'created' => new DateTime(),
                        'updated' => new DateTime(),
                        'TranslationPriority' => 5,
                        'isarchived' => 0,
                    ];
                    if ($hasFormat) {
                        // null: detected from the text
                        $values['format'] = $missingTranslation->format?->value;
                    }
                    $connection->insert('word', $values, ['created' => Types::DATETIME_MUTABLE, 'updated' => Types::DATETIME_MUTABLE]);
                }
            }
        }

        if (0 === $count) {
            $io->success('All translations in \'translation/missing\' were already imported.');

            return Command::SUCCESS;
        }

        $io->success('Finished importing the translations into the database');

        $command = $this->getApplication()->find('cache:clear');

        $clearCache = new ArrayInput([]);

        $io->note('clearing cache to force update of translations');
        $result = $command->run($clearCache, new NullOutput());

        if (!filter_var(\ini_get('opcache.validate_timestamps'), \FILTER_VALIDATE_BOOL) && filter_var(\ini_get('opcache.enable'), \FILTER_VALIDATE_BOOL)) {
            // PHP-FPM keeps the catalogues it compiled; it doesn't look at the new files (production)
            $io->warning('PHP does not check its compiled files for changes here (opcache.validate_timestamps is off): restart the php container so that the site shows the new texts.');
        }

        return $result;
    }
}
