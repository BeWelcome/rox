<?php

namespace App\Form\DataTransformer;

use App\Doctrine\SubtripOptionsType;
use Symfony\Component\Form\DataTransformerInterface;

class SubtripOptionsTypeTransformer implements DataTransformerInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ?SubtripOptionsType $value
     *
     * @return array
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return '';
        }

        return $value;
    }
}
