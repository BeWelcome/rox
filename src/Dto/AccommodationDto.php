<?php

namespace App\Dto;

class AccommodationDto
{
    public function __construct(
        public ?string $accommodation,
        public ?int $hostingInterest,
        // the member whose profile is edited (admins and the safety team); without it the logged-in member
        public ?string $username = null,
    ) {
    }
}
