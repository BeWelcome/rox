<?php

namespace App\Command;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Repository\MemberRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * New members who didn't confirm their email address or didn't finish the setup in time leave; the next
 * data:retention run removes their data. Run it daily.
 */
#[AsCommand(
    name: 'members:remove:notconfirmed',
    description: 'Removes new members who didn\'t confirm their email address or finish the setup in time',
)]
class MembersRemoveNotConfirmedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MemberRepository $memberRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $members = $this->memberRepository->findSignupsNotFinished();
        // a year back: data:retention takes members who left more than a year ago
        $leftLongAgo = new DateTime('-1 year -1 day');
        foreach ($members as $member) {
            $member
                ->setStatus(MemberStatusType::ASKED_TO_LEAVE)
                ->setLastActive($leftLongAgo);
        }
        $this->entityManager->flush();

        new SymfonyStyle($input, $output)->success(\sprintf(
            'Removed %d members who didn\'t finish the signup within %d days.',
            \count($members),
            Member::DAYS_TO_FINISH_SIGNUP,
        ));

        return Command::SUCCESS;
    }
}
