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
    name: 'members:remind:login',
    description: 'Reminds members to login if they haven\'t done so for a year.',
)]
class MembersRemindLoginCommand extends Command
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

        $members = $this->entityManager->getRepository(Member::class)->findMembersToRemind($batchSize);

        foreach ($members as $member) {
            $member->setStatus(MemberStatusType::OUT_OF_REMIND);
            $member->setRemindersWithOutLogin($member->getRemindersWithOutLogin() + 1);
            $member->setUpdated(new DateTime());

            $this->entityManager->persist($member);
            $this->mailer->sendMemberLoginReminder($member);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Processed %d members', \count($members)));

        return Command::SUCCESS;
    }
}
