<?php

namespace App\Form\DataTransformer;

use App\Entity\Subtrip;
use Symfony\Component\Form\DataTransformerInterface;

class LegTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value->getId();
    }

    public function reverseTransform($value): ?Subtrip
    {
        if (null === $value) {
            return null;
        }

        return new Subtrip($value);
    }
}
