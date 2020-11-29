<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Member;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class MemberNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Member $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if ('No' === $object->getHidebirthdate()) {
            unset($data['age']);
        }
        if ('No' === $object->getHidegender()) {
            unset($data['gender']);
        }
        $hideAttribute = $object->getHideAttribute();
        if ($hideAttribute & Member::MEMBER_FIRSTNAME_HIDDEN) {
            unset($data['firstName']);
        }
        if ($hideAttribute & Member::MEMBER_SECONDNAME_HIDDEN) {
            unset($data['secondName']);
        }
        if ($hideAttribute & Member::MEMBER_LASTNAME_HIDDEN) {
            unset($data['lastName']);
        }

        $addresses = $object->getAddresses();
        if ('No' !== $object->getAdresshidden() && $addresses->count()) {
            $data['address'] = $this->normalizer->normalize(
                $addresses->first(),
                str_ireplace('jsonld', 'json', $format),
                $context
            );
        }

        foreach (['homePhoneNumber', 'cellPhoneNumber', 'workPhoneNumber'] as $property) {
            $data[$property] = $object->getCryptedField(ucfirst($property), false);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Member && true !== ($context[__CLASS__] ?? false);
    }
}
