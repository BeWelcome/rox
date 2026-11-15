<?php

namespace App\Dto;

class OffersDto
{
    public function __construct(
        public bool $dinner,
        public bool $tour,
        public bool $accessible,
        // the member whose profile is edited (admins and the safety team); without it the logged-in member
        public ?string $username = null,
    ) {
    }
}
