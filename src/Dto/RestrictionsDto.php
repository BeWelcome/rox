<?php

namespace App\Dto;

class RestrictionsDto
{
    public function __construct(
        public bool $noAlcohol,
        public bool $noSmoking,
        public bool $noDrugs,
        // the member whose profile is edited (admins and the safety team); without it the logged-in member
        public ?string $username = null,
    ) {
    }
}
