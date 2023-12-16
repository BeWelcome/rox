<?php

namespace App\Command;

use App\Entity\Comment;
use App\Entity\Member;
use App\Service\Mailer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SendCommentReminderCommand extends Command
{
    protected static $defaultName = 'comments:send:reminder';

    private EntityManagerInterface $entityManager;
    private Mailer $mailer;
    private int $batchSize;
    private SymfonyStyle $io;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        Mailer $mailer,
        int $batchSize
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->batchSize = $batchSize;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a batch of comment reminders everytime the command is called')
            ->addOption('first', null, InputOption::VALUE_NONE, 'Send the first comment reminder')
            ->addOption('second', null, InputOption::VALUE_NONE, 'Send the second comment reminder')
            ->addOption('host', null, InputOption::VALUE_NONE, 'Send the host comment reminder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $firstReminder = $input->getOption('first');
        $secondReminder = $input->getOption('second');
        $hostReminder = $input->getOption('host');

        if (!$firstReminder && !$secondReminder && !$hostReminder) {
            $this->io->note('You need to provide --first or --second or --host as arguments.');

            return Command::INVALID;
        }

        $firstReminderReturnCode = $secondReminderReturnCode = $hostReminderReturnCode = Command::SUCCESS;
        if ($firstReminder) {
            $firstReminderReturnCode = $this->sendFirstGuestReminders();
        }

        if ($secondReminder) {
            $secondReminderReturnCode = $this->sendSecondGuestReminders();
        }
        if ($hostReminder) {
            $hostReminderReturnCode = $this->sendHostReminders();
        }

        return $firstReminderReturnCode && $secondReminderReturnCode && $hostReminderReturnCode;
    }

    private function sendFirstGuestReminders(): int
    {
        $start  = '2 00:00:00';
        $end = '1 00:00:00';

        $mailer = $this->mailer;
        return $this->sendGuestReminders(
            $start,
            $end,
            function ($guest, $host) use ($mailer) {
                $mailer->sendCommentReminderToGuest($guest, $host, 'comment.first.reminder.guest');
            }
        );
    }

    private function sendSecondGuestReminders(): int
    {
        $start = '15 00:00:00';
        $end = '14 00:00:00';

        $mailer = $this->mailer;
        return $this->sendGuestReminders(
            $start,
            $end,
            function ($guest, $host) use ($mailer) {
                $mailer->sendCommentReminderToGuest($guest, $host, 'comment.second.reminder.guest');
            }
        );
    }

    private function sendGuestReminders(string $start, string $end, callable $sendCommentReminder): int
    {
        $guestsAndHosts = $this->getGuestsAndHosts($start, $end);

        // Send reminder if no comment given yet
        $sendReminders = $this->sendReminderIfNoCommentGivenYet($guestsAndHosts, $sendCommentReminder);

        $this->io->note("Send {$sendReminders} guest reminder(s) after two days.");
        $this->logger->info("Send {$sendReminders} guest reminder(s) after two days.");

        return Command::SUCCESS;
    }
    private function sendHostReminders(): int
    {
        $start = '22 00:00:00';
        $end = '21 00:00:00';
        $guestsAndHosts = $this->getGuestsAndHosts($start, $end);

        $mailer = $this->mailer;
        $sendReminders = $this->sendReminderIfNoCommentGivenYet(
            $guestsAndHosts,
            function ($guest, $host) use ($mailer) {
                $mailer->sendCommentReminderToHost($guest, $host);
            }
        );

        $this->io->note("Send {$sendReminders} host reminder(s).");
        $this->logger->info("Send {$sendReminders} host reminder(s) after three weeks.");

        return Command::SUCCESS;
    }

    private function sendReminderIfNoCommentGivenYet(array $guestsAndHosts, callable $method): int
    {
        $sendReminders = 0;
        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $memberRepository = $this->entityManager->getRepository(Member::class);
        foreach ($guestsAndHosts as $guestAndHost) {
            $commentGuestHost = $commentRepository->findOneBy([
                'fromMember' => $guestAndHost['guest'],
                'toMember' => $guestAndHost['host']
            ]);

            $commentHostGuest = $commentRepository->findOneBy([
                'fromMember' => $guestAndHost['host'],
                'toMember' => $guestAndHost['guest']
            ]);

            if (null === $commentGuestHost && null === $commentHostGuest) {
                // Get guest and host member objects
                $guest = $memberRepository->find($guestAndHost['guest']);
                $host = $memberRepository->find($guestAndHost['host']);

                // send reminder
                $this->io->note("Sending reminder to {$guest->getUsername()} for {$host->getUsername()}");
                $this->logger->info("Sending reminder to {$guest->getUsername()} for {$host->getUsername()}");
                $method($guest, $host);

                $sendReminders++;
            }
        }

        return $sendReminders;
    }

    private function getGuestsAndHosts(string $start, string $end): array
    {
        // Get guests and hosts of recently expired requests
        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('guest', 'guest')
            ->addScalarResult('host', 'host')
        ;
        $query = $this->entityManager->createNativeQuery("
            SELECT
                m.IdSender AS guest, m.IdReceiver AS host
            FROM
                messages m
	        INNER JOIN
	            request r ON m.request_id = r.id AND r.Status = 8 AND r.invite_for_leg IS NULL
	            AND date(r.departure) BETWEEN DATE_SUB(NOW(), INTERVAL ? DAY_SECOND) AND DATE_SUB(NOW(), INTERVAL ? DAY_SECOND)
           /*
           INNER JOIN
               membersgroups mgt ON m.IdSender = mgt.IdMember AND mgt.IdGroup = 62 AND mgt.`Status` = 'In'
           INNER JOIN
               membersgroups mgf ON m.IdReceiver = mgf.IdMember AND mgf.IdGroup = 62 AND mgf.`Status` = 'In'
            */
            WHERE
                m.IdParent IS NULL AND NOT m.request_id IS NULL
            ", $rsm);
        $query->setParameter(1, $start);
        $query->setParameter(2, $end);

        $guestsAndHosts = $query->getResult();

        return $guestsAndHosts;
    }
}
