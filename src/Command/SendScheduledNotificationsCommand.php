<?php

namespace App\Command;

use DateInterval;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The daily, weekly or monthly mails about what was created near the members who asked for them in their preferences
 * (trips, activities). A command per kind gives its preference values and sends; the servers' scheduler starts them.
 */
abstract class SendScheduledNotificationsCommand extends Command
{
    private const array PERIODS = ['daily' => 'P1D', 'weekly' => 'P7D', 'monthly' => 'P31D'];

    protected function configure(): void
    {
        $this->addArgument('frequency', InputArgument::REQUIRED, 'daily, weekly or monthly');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $frequency = strtolower((string) $input->getArgument('frequency'));
        $preferenceValues = $this->preferenceValues();

        if (!isset(self::PERIODS[$frequency], $preferenceValues[$frequency])) {
            $io->error('Frequency must be daily, weekly or monthly.');

            return Command::INVALID;
        }

        $createdUntil = new DateTimeImmutable();
        $createdSince = $createdUntil->sub(new DateInterval(self::PERIODS[$frequency]));
        $sent = $this->send($preferenceValues[$frequency], $createdSince, $createdUntil);

        $io->success(\sprintf('Sent %d %s notification emails.', $sent, $this->kind()));

        return Command::SUCCESS;
    }

    /**
     * @return array{daily: string, weekly: string, monthly: string} the preference value of each frequency
     */
    abstract protected function preferenceValues(): array;

    /**
     * Sends the mails about what was created in the period; returns how many were sent.
     */
    abstract protected function send(string $preferenceValue, DateTimeImmutable $createdSince, DateTimeImmutable $createdUntil): int;

    /**
     * What the mails are about, for the command's output (trip, activity).
     */
    abstract protected function kind(): string;
}
