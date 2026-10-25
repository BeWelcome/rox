<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Poll;
use App\Entity\PollVote;

final class PollsExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): string
    {
        $pollsDir = $tempDir . 'polls/';
        mkdir($pollsDir);

        $pollsRepository = $this->getRepository(Poll::class);
        $polls = $pollsRepository->findBy(['creator' => $member]);
        $this->writePersonalDataFileSubDirectory(['polls' => $polls], 'polls_created', $tempDir . 'polls', 'polls_created.html');

        // The votes with their comments, and (polls that are not anonymous) the choices taken
        $votes = $this->getRepository(PollVote::class)->findBy(['member' => $member], ['id' => 'DESC']);
        $contributions = $votes;
        $this->writePersonalDataFileSubDirectory(['contributions' => $contributions], 'polls_contributed', $tempDir . 'polls', 'polls_contributed.html');

        $this->writePersonalDataFileSubDirectory(['votes' => $votes], 'polls_voted', $tempDir . 'polls', 'polls_voted.html');

        return $this->writePersonalDataFile([], 'polls', $tempDir . 'polls.html');
    }
}
