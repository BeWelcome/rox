<?php

namespace App\Dto;

class OffersDto
{
    public function __construct(
        public bool $dinner,
        public bool $tour,
        public bool $accessible,
    ) {
    }
}
