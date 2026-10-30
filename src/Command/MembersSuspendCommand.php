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

/**
 * Members who didn't come back for five years despite the reminders are suspended and told that their account
 * will be deleted in two months. Two months after the suspension the data retention takes them. Run it daily.
 */
#[AsCommand(
    name: 'members:suspend:notification',
    description: 'Suspends members away for five years despite reminders, schedules earlier ones for deletion',
)]
class MembersSuspendCommand extends Command
{
    /** Marks the members as suspended after the reminders (instead of counting them). */
    public const int REMINDERS_SUSPENDED = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MemberRepository $memberRepository,
        private readonly Mailer $mailer,
        #[Autowire('%member_batch_size%')]
        private readonly int $batchSize,
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

        return Command::SUCCESS;
    }
}
