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
 * Members who haven't logged in for a year get a reminder, at most one a year, and leave the search results until
 * they log in again (OutOfRemind). Run it daily.
 */
#[AsCommand(
    name: 'members:remind:login',
    description: 'Reminds members who haven\'t logged in for a year',
)]
class MembersRemindLoginCommand extends Command
{
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
        $this->addArgument('batch-size', InputArgument::OPTIONAL, 'How many members to remind in this run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = (int) ($input->getArgument('batch-size') ?? $this->batchSize);
        if ($batchSize <= 0) {
            $io->error('The batch size must be a positive number.');

            return Command::FAILURE;
        }

        $members = $this->memberRepository->findMembersToRemind($batchSize);
        foreach ($members as $member) {
            $member
                ->setStatus(MemberStatusType::OUT_OF_REMIND)
                ->setRemindersWithOutLogin($member->getRemindersWithOutLogin() + 1)
                ->setUpdated(new DateTime());
            $this->mailer->sendMemberLoginReminder($member);
        }
        $this->entityManager->flush();

        $io->success(\sprintf('Reminded %d members.', \count($members)));

        return Command::SUCCESS;
    }
}
