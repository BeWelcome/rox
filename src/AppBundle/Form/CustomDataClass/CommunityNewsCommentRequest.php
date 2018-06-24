<?php

namespace AppBundle\Form\CustomDataClass;

use AppBundle\Entity\CommunityNews;
use AppBundle\Entity\CommunityNewsComment;
use Symfony\Component\Validator\Constraints as Assert;

class CommunityNewsCommentRequest
{
    /**
     * @var CommunityNews
     */
    public $communityNews;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="100")
     *
     * @var string
     */
    public $title;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $text;

    public static function fromCommunityNewsComment(CommunityNewsComment $communityNewsComment): self
    {
        $communityNewsCommentRequest = new self();
        $communityNewsCommentRequest->communityNews = $communityNewsComment->getCommunityNews();
        $communityNewsCommentRequest->title = $communityNewsComment->getTitle();
        $communityNewsCommentRequest->text = $communityNewsComment->getText();

        return $communityNewsCommentRequest;
    }
}