<?php

namespace App\Dto;

class MaxGuestsDto
{
    public function __construct(
        public ?int $maxGuests,
    ) {
    }
}
