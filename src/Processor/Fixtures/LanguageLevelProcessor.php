<?php

namespace App\Processor\Fixtures;

use App\Doctrine\LanguageLevelType;
use App\Entity\MemberLanguageLevel;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;

final readonly class LanguageLevelProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function preProcess(string $id, $object): void
    {
        if (!$object instanceof MemberLanguageLevel) {
            return;
        }

        $object->setLevel(LanguageLevelType::MOTHER_TONGUE);
    }

    public function postProcess(string $id, $object): void
    {
        if (!$object instanceof MemberLanguageLevel) {
            return;
        }

        $languages = array_keys($object->getMember()->getTranslations());

        $object->setLevel(LanguageLevelType::MOTHER_TONGUE);
    }
}
