<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

final class GalleryItemsExtractor extends AbstractExtractor implements ExtractorInterface
{
    private $projectDir;

    public function __construct(EntrypointLookupInterface $entrypointLookup, Environment $environment, ManagerRegistry $registry, string $projectDir)
    {
        parent::__construct($entrypointLookup, $environment, $registry);
        $this->projectDir = $projectDir;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        $memberId = $member->getId();

        $filesystem = new Filesystem();
        $galleryPath = sprintf('%s/data/gallery/member%s/', $this->projectDir, $memberId);

        $hrefs = [];
        if (is_dir($galleryPath)) {
            // create gallery sub directory
            $galleryDir = $tempDir . 'gallery/';
            @mkdir($galleryDir);
            $directoryHandle = opendir($galleryPath);
            if ($directoryHandle) {
                while (false !== ($file = readdir($directoryHandle))) {
                    if (!is_dir($file)) {
                        $ext = $this->imageExtension($galleryPath . $file);
                        $destination = $galleryDir . pathinfo($file, PATHINFO_FILENAME) . $ext;
                        $filesystem->copy($galleryPath . $file, $galleryDir . pathinfo($file, PATHINFO_FILENAME) . $ext);
                        $hrefs[] = str_replace($tempDir, '', $destination);
                    }
                }
                closedir($directoryHandle);
            }
        }

        return $this->writePersonalDataFile(['hrefs' => $hrefs], 'gallery', $tempDir . 'gallery.html');
    }
}
