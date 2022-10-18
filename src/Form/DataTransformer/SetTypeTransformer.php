<?php

namespace App\Form\DataTransformer;

use DateTime;
use Symfony\Component\Form\DataTransformerInterface;

class SetTypeTransformer implements DataTransformerInterface
{
    public function transform($value): ?array
    {
        if (null === $value) {
            return null;
        }

        return explode(',', $value);
    }

    public function reverseTransform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return implode(',', $value);
    }
}
