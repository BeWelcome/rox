<?php


namespace App\Form\DataTransformer;


use App\Doctrine\SubtripOptionsType;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SubtripOptionsTypeTransformer implements \Symfony\Component\Form\DataTransformerInterface
{

    /**
     * @inheritDoc
     *
     * @param SubtripOptionsType|null
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
     * @inheritDoc
     *
     *
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return '';
        }

        return $value;
    }
}
