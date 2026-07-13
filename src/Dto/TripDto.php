<?php

namespace App\Dto;

use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Member;
use App\Entity\Trip;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class TripDto
{
    public ?int $id = null;

    #[Assert\NotBlank]
    public ?string $summary = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    public int $countOfTravellers = 1;

    public int $invitationRadius = 20;

    public string $additionalInfo = TripAdditionalInfoType::NONE;

    public ?Member $creator = null;

    public Collection $legs;

    public ?DateTime $created = null;

    public function __construct()
    {
        $this->legs = new ArrayCollection();
    }

    public static function fromEntity(Trip $trip): self
    {
        $dto = new self();
        $dto->id = $trip->getId();
        $dto->summary = $trip->getSummary();
        $dto->description = $trip->getDescription();
        $dto->countOfTravellers = $trip->getCountOfTravellers();
        $dto->invitationRadius = $trip->getInvitationRadius();
        $dto->additionalInfo = $trip->getAdditionalInfo();
        $dto->creator = $trip->getCreator();
        $dto->created = $trip->getCreated();

        foreach ($trip->getLegs() as $leg) {
            $dto->legs->add(LegDto::fromEntity($leg));
        }

        return $dto;
    }

    public function toEntity(Trip $trip): void
    {
        $trip->setSummary($this->summary);
        $trip->setDescription($this->description);
        $trip->setCountOfTravellers($this->countOfTravellers);
        $trip->setInvitationRadius($this->invitationRadius);
        $trip->setAdditionalInfo($this->additionalInfo);
    }
}
