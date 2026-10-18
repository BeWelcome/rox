<?php

namespace App\Processor\Fixtures;

use App\Entity\GalleryImage;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GalleryImageProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire('%gallery_directory%')]
        private string $galleryDirectory,
    ) {
    }

    public function preProcess(string $id, $object): void
    {
    }

    public function postProcess(string $id, $object): void
    {
        if (!$object instanceof GalleryImage) {
            return;
        }

        // Matches GalleryModel::createThumbnails(): a 100x100 `thumb` is always created, plus `thumb1`
        // (240x240, requested via ?t=1 on the profile page widget) and `thumb2` (500x500) whenever the
        // original exceeds those bounds - which a 1024x768 placeholder always does.
        $memberDirectory = $this->galleryDirectory . '/member' . $object->getOwner()->getId();
        $file = $object->getFile();
        PlaceholderImage::create($memberDirectory . '/' . $file, $object->getWidth(), $object->getHeight());
        PlaceholderImage::create($memberDirectory . '/thumb' . $file, 100, 100);
        PlaceholderImage::create($memberDirectory . '/thumb1' . $file, 240, 180);
        PlaceholderImage::create($memberDirectory . '/thumb2' . $file, 500, 375);

        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }
}
