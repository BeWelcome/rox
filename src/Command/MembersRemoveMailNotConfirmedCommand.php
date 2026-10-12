<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'members:remove:notconfirmed',
    description: 'Removes members that haven\'t confirmed their email address after two weeks',
)]
class MembersRemoveMailNotConfirmedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $members = $this->entityManager->getRepository(Member::class)->findMailNotConfirmedMembers();

        $lastYear = new DateTime('-1 year');

        foreach ($members as $member) {
            $member->setStatus(MemberStatusType::ASKED_TO_LEAVE);
            // Member will be removed with the next data:retention run
            $member->setLastLogin($lastYear);

            $this->entityManager->persist($member);
        }

        $this->entityManager->flush();

        $io->success(sprintf('members:remove:notconfirmed: Processed %d members', \count($members)));

        return Command::SUCCESS;
    }
}
