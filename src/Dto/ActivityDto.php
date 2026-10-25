<?php

namespace App\Dto;

use App\Entity\Activity;
use App\Entity\Location;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The data of the create and edit activity form (App\Form\ActivityType).
 */
class ActivityDto
{
    #[Assert\NotBlank(message: 'activity.title.empty')]
    #[Assert\Length(max: 80, maxMessage: 'activity.title.too.long')]
    public ?string $title = null;

    #[Assert\NotNull(message: 'location.none.given')]
    public ?Location $location = null;

    #[Assert\Length(max: 320, maxMessage: 'activity.address.too.long')]
    public ?string $address = null;

    #[Assert\NotNull(message: 'activity.start.empty')]
    public ?DateTimeInterface $starts = null;

    #[Assert\NotNull(message: 'activity.end.empty')]
    #[Assert\GreaterThanOrEqual(propertyPath: 'starts', message: 'activity.end.before.start')]
    public ?DateTimeInterface $ends = null;

    #[Assert\NotBlank(message: 'activity.description.empty')]
    #[Assert\Length(max: 65535, maxMessage: 'activity.description.too.long')]
    public ?string $description = null;

    public bool $online = false;

    public static function fromEntity(Activity $activity): self
    {
        $dto = new self();
        $dto->title = $activity->getTitle();
        $dto->location = $activity->getLocation();
        $dto->address = $activity->getAddress();
        $dto->starts = $activity->getStarts()->toDateTime();
        $dto->ends = $activity->getEnds()->toDateTime();
        $dto->description = $activity->getDescription();
        $dto->online = $activity->isOnline();

        return $dto;
    }

    public function toEntity(Activity $activity): void
    {
        $activity
            ->setTitle((string) $this->title)
            ->setLocation($this->location)
            ->setAddress('' === $this->address ? null : $this->address)
            ->setStarts(DateTime::createFromInterface($this->starts))
            ->setEnds(DateTime::createFromInterface($this->ends))
            ->setDescription((string) $this->description)
            ->setOnline($this->online ? 1 : 0)
        ;
    }
}
