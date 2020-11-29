<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Address;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class AddressNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Address $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $data['houseNumber'] = $object->getMember()->getCryptedField('HouseNumber', false, 'addresses');
        $data['streetName'] = $object->getMember()->getCryptedField('StreetName', false, 'addresses');
        $data['zip'] = $object->getMember()->getCryptedField('Zip', false, 'addresses');

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return is_a($data, Address::class, true) && true !== ($context[__CLASS__] ?? false);
    }
}
