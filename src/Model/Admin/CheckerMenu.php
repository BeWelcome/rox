<?php

namespace App\Model\Admin;

use App\Model\ActivityModel;
use App\Model\CommunityNewsModel;
use App\Model\ShadowBanModel;

/**
 * What is open in the sections of the checkers' menu (admin/checker/_menu.html.twig), for every page of it.
 */
readonly class CheckerMenu
{
    public function __construct(
        private CheckerModel $checkerModel,
        private ActivityModel $activityModel,
        private CommunityNewsModel $communityNewsModel,
        private ShadowBanModel $shadowBanModel,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        return [
            'messages' => $this->checkerModel->getReportedMessages(1, 1)->getNbResults(),
            'blocked_words' => $this->checkerModel->getBlockWordsMessages(1, 1)->getNbResults(),
            'flagged' => $this->shadowBanModel->countOpenFlags(),
            'activities' => $this->activityModel->getProblematicActivities(1, 1)->getNbResults(),
            'community_news' => $this->communityNewsModel->countReportedComments(),
        ];
    }
}
