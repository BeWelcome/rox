<?php

namespace App\Dto;

class RestrictionsDto
{
    public function __construct(
        public bool $noAlcohol,
        public bool $noSmoking,
        public bool $noDrugs,
    ) {
    }
}
