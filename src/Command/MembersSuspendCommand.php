<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Service\Mailer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'members:suspend:notification',
    description: 'Informs a member of suspension and imminent deletion. Also schedules already suspended members for deletion after two months.',
)]
class MembersSuspendCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Mailer $mailer,
        private readonly int $mailerBatchSize,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'batch-size',
                InputArgument::OPTIONAL,
                'How many members to process in this run',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $batchSize = $input->getArgument('batch-size') ?? $this->mailerBatchSize;
        if ($batchSize <= 0) {
            $io->error(sprintf('Batch size must be a positive integer. Was: %d', $batchSize));

            return Command::FAILURE;
        }

        $members = $this->entityManager->getRepository(Member::class)->findMembersToSuspend($batchSize);

        foreach ($members as $member) {
            $member->setStatus(MemberStatusType::SUSPENDED);
            $member->setRemindersWithOutLogin(100);
            $member->setUpdated(new DateTime());

            $this->entityManager->persist($member);
            $this->mailer->sendMemberSuspensionNotice($member);
        }

        $io->success(sprintf('Suspended %d members', \count($members)));

        $this->entityManager->flush();

        $query = $this->entityManager->createQuery(
            'UPDATE App\Entity\Member m
            SET m.status = :status, m.lastLogin = :lastLogin
            WHERE m.status IN (:currentStatus)
            AND DATEDIFF(CURRENT_DATE(), m.updated) > 62'
        );
        $query->setParameters([
            'status' => MemberStatusType::ASKED_TO_LEAVE,
            'lastLogin' => new DateTime('-1year'),
            'currentStatus' => [MemberStatusType::SUSPENDED],
        ]);
        $scheduledCount = $query->execute();

        $io->success(sprintf('Scheduled %d members to be deleted with data:cleanup', $scheduledCount));

        return Command::SUCCESS;
    }
}
