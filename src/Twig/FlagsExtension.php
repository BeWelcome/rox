<?php

namespace App\Twig;

use App\Entity\FlagMember;
use App\Entity\Member;
use App\Model\Admin\FlagsModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * forum_posting_block(member): the state of the flag switch in the Admin box of the profile
 * (templates/profile/_admin_tools.html.twig).
 */
class FlagsExtension extends AbstractExtension
{
    public function __construct(private readonly FlagsModel $model)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('forum_posting_block', $this->getForumPostingBlock(...)),
        ];
    }

    /**
     * @return array{block: ?FlagMember, earlier: int}|null the current block and how often the member was blocked
     *                                                      before; null where there is no such flag
     */
    public function getForumPostingBlock(Member $member): ?array
    {
        $flag = $this->model->getForumPostingFlag();
        if (null === $flag) {
            return null;
        }

        return [
            'block' => $this->model->findBlock($member, $flag),
            'earlier' => $this->model->countRemoved($member, $flag),
        ];
    }
}
