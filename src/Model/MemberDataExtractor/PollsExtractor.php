<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Poll;
use App\Entity\PollContribution;
use App\Entity\PollRecordOfChoice;

final class PollsExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        $pollsDir = $tempDir . 'polls/';
        @mkdir($pollsDir);

        $pollsRepository = $this->getRepository(Poll::class);
        $polls = $pollsRepository->findBy(['creator' => $member]);
        $this->writePersonalDataFileSubDirectory(['polls' => $polls], 'polls_created', $tempDir . 'polls');

        $contributionsRepository = $this->getRepository(PollContribution::class);
        $contributions = $contributionsRepository->findBy(['member' => $member]);
        $this->writePersonalDataFileSubDirectory(['contributions' => $contributions], 'polls_contributed', $tempDir . 'polls');

        $votesRepository = $this->getRepository(PollRecordOfChoice::class);
        $votes = $votesRepository->findBy(['member' => $member], ['poll' => 'DESC', 'pollChoice' => 'DESC']);
        $this->writePersonalDataFileSubDirectory(['votes' => $votes], 'polls_voted', $tempDir . 'polls');

        return $this->writePersonalDataFile([], 'polls');
    }
}
