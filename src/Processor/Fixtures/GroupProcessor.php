<?php

namespace App\Processor\Fixtures;

use App\Entity\Group;
use App\Utilities\UniqueFilenameTrait;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GroupProcessor implements ProcessorInterface
{
    use UniqueFilenameTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire('%group_directory%')]
        private string $groupDirectory,
    ) {
    }

    public function preProcess(string $id, $object): void
    {
        if (!$object instanceof Group) {
            return;
        }

        if ('' !== $object->getPicture()) {
            return;
        }

        $fileName = $this->generateUniqueFileName() . '.jpg';
        $object->setPicture($fileName);

        PlaceholderImage::create($this->groupDirectory . '/' . $fileName, 240, 240);
        PlaceholderImage::create($this->groupDirectory . '/thumb' . $fileName, 80, 80);
    }

    public function postProcess(string $id, $object): void
    {
        if (!$object instanceof Group) {
            return;
        }

        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }
}
