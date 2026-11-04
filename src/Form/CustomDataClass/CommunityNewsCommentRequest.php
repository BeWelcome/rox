<?php

namespace App\Form\CustomDataClass;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A comment on a community news, or a reply to one of its comments (parent: that comment's id).
 */
class CommunityNewsCommentRequest
{
    #[Assert\NotBlank(message: 'communitynews.comment.error.empty')]
    #[Assert\Length(max: 5000, maxMessage: 'communitynews.comment.error.long')]
    public ?string $text = null;

    public ?int $parent = null;
}
