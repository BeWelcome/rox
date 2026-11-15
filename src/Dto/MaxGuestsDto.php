<?php

namespace App\Dto;

class MaxGuestsDto
{
    public function __construct(
        public ?int $maxGuests,
        // the member whose profile is edited (admins and the safety team); without it the logged-in member
        public ?string $username = null,
    ) {
    }
}
