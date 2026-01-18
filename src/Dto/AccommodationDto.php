<?php

namespace App\Dto;

class AccommodationDto
{
    public function __construct(
        public ?string $accommodation,
        public ?int $hostingInterest,
    ) {
    }
}
