<?php

namespace App\Form\DataTransformer;

use App\Entity\Location;
use App\Form\CustomDataClass\LocationRequest;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateTimeTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value->format('Y-m-d');
    }

    public function reverseTransform($value): ?DateTime
    {
        if (null === $value) {
            return null;
        }

        return new DateTime($value);
    }
}
