<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Repository\MemberRepository;
use App\Service\Mailer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;

/**
 * Members who didn't come back for five years despite the reminders are suspended, a year after the last reminder at
 * the earliest, and told that their account will be deleted in two months. Two months after the suspension the data
 * retention takes them. Members who got or wrote a comment aren't suspended. The cron runs it several times a day
 * (member_batch_size members a run); it shares a lock with members:remind:login, so only one of the two runs at a time.
 */
#[AsCommand(
    name: 'members:suspend:notification',
    description: 'Suspends members away for five years despite reminders, schedules earlier ones for deletion',
)]
class MembersSuspendCommand extends Command
{
    /** Marks the members as suspended after the reminders (instead of counting them). */
    public const int REMINDERS_SUSPENDED = 100;

    /** Taken by this command and members:remind:login: one of the two runs at a time. */
    public const string LOCK = 'members:lifecycle';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MemberRepository $memberRepository,
        private readonly Mailer $mailer,
        #[Autowire('%member_batch_size%')]
        private readonly int $batchSize,
        private readonly LockFactory $lockFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('batch-size', InputArgument::OPTIONAL, 'How many members to suspend in this run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = (int) ($input->getArgument('batch-size') ?? $this->batchSize);
        if ($batchSize <= 0) {
            $io->error('The batch size must be a positive number.');

            return Command::FAILURE;
        }

        // one of the two at a time: they both change the members waiting for a reminder (OutOfRemind)
        $lock = $this->lockFactory->createLock(self::LOCK, null);
        if (!$lock->acquire()) {
            $io->note('members:remind:login or members:suspend:notification is at work; this run ends.');

            return Command::SUCCESS;
        }
        try {
            $this->suspend($io, $batchSize);
        } finally {
            $lock->release();
        }

        return Command::SUCCESS;
    }

    private function suspend(SymfonyStyle $io, int $batchSize): void
    {
        // the ones suspended in earlier runs first, so that a member suspended now gets the two months
        $scheduled = $this->memberRepository->scheduleSuspendedMembersForDeletion();

        $members = $this->memberRepository->findMembersToSuspend($batchSize);
        $deletionDate = new DateTime('+2 months');
        foreach ($members as $member) {
            $member
                ->setStatus(MemberStatusType::SUSPENDED)
                ->setRemindersWithOutLogin(self::REMINDERS_SUSPENDED)
                ->setUpdated(new DateTime());
            $this->mailer->sendMemberSuspensionNotice($member, $deletionDate);
        }
        $this->entityManager->flush();

        $io->success(\sprintf('Suspended %d members.', \count($members)));
        $io->success(\sprintf('Scheduled %d members for deletion by data:retention.', $scheduled));
    }
}
