<?php

namespace App\Dto;

use App\Entity\Subtrip;
use DateTime;

class LegDto
{
    public ?int $id = null;
    public ?string $location = null;
    public ?DateTime $arrival = null;
    public ?DateTime $departure = null;
    public ?array $options = [];

    public static function fromEntity(Subtrip $leg): self
    {
        $dto = new self();
        $dto->id = $leg->getId();
        $dto->location = $leg->getLocation()->getGeonameId();
        $dto->arrival = $leg->getArrival();
        $dto->departure = $leg->getDeparture();
        $dto->options = $leg->getOptions();

        return $dto;
    }
}
