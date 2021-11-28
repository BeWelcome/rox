<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class FlexibleTransformer implements DataTransformerInterface
{
    public function transform($value): bool
    {
        if (null === $value) {
            return false;
        }

        return $value;
    }

    public function reverseTransform($value): bool
    {
        if (null === $value) {
            return false;
        }

        return $value;
    }
}
