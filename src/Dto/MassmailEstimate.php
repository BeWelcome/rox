<?php

namespace App\Dto;

use App\Form\Admin\MassmailRecipientsType;
use App\Model\Admin\MassmailRights;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The recipients chosen on the page to enqueue a mass mail, from the query string: how many members that are is
 * shown next to the button (admin_massmail_estimate). The fields are those of the forms (MassmailRecipientsType).
 */
readonly class MassmailEstimate
{
    public function __construct(
        #[Assert\Choice(choices: [MassmailRights::MEMBERS, MassmailRights::LOCATION, MassmailRights::GROUP, MassmailRights::TERMS_OF_USE])]
        public string $recipients = MassmailRights::MEMBERS,
        #[Assert\Choice(choices: [MassmailRecipientsType::USERNAMES, MassmailRecipientsType::ALL])]
        public string $members = MassmailRecipientsType::USERNAMES,
        #[Assert\Length(max: 10000)]
        public ?string $usernames = null,
        #[Assert\PositiveOrZero]
        public ?int $limit = null,
        #[Assert\Regex(pattern: '/^[A-Z]{2}$/')]
        public ?string $country = null,
        #[Assert\Regex(pattern: '/^[A-Za-z0-9]{1,20}$/')]
        public ?string $adminUnit = null,
        #[Assert\Positive]
        public ?int $place = null,
        #[Assert\Positive]
        public ?int $group = null,
    ) {
    }
}
