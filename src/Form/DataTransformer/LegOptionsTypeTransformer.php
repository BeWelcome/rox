<?php

namespace App\Form\DataTransformer;

use App\Doctrine\LegOptionsType;
use Symfony\Component\Form\DataTransformerInterface;

class LegOptionsTypeTransformer implements DataTransformerInterface
{
    /**
     * @param ?LegOptionsType $value
     *
     * @return array
     */
    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        return $value;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (null === $value) {
            return '';
        }

        return $value;
    }
}
