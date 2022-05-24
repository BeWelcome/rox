<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\MembersPhoto;
use Symfony\Component\Filesystem\Filesystem;

final class ProfilePicturesExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        $pictures = [];
        $variants = ['_xs', '_30_30', '_75_75', '_150', '_200', '_500', '_original'];

        // Copy all profile pictures
        $filesystem = new Filesystem();
        $pictureDir = $tempDir . 'pictures/';
        $filesystem->mkdir($pictureDir);
        $photoRepository = $this->getRepository(MembersPhoto::class);
        /** @var MembersPhoto[] $photos */
        $photos = $photoRepository->findBy(['member' => $member]);
        foreach ($photos as $photo) {
            if (is_file($photo->getFilepath())) {
                $filesystem->copy($photo->getFilepath(), $pictureDir
                    . pathinfo($photo->getFilepath(), \PATHINFO_FILENAME)
                    . $this->imageExtension($photo->getFilepath()));
                $pictures[] =
                    pathinfo($photo->getFilepath(), \PATHINFO_FILENAME)
                    . $this->imageExtension($photo->getFilepath());
            }
            foreach ($variants as $variant) {
                $filepath = $photo->getFilepath() . $variant;
                $filename = pathinfo($filepath, \PATHINFO_FILENAME);
                if (is_file($filepath)) {
                    $filesystem->copy($filepath, $pictureDir
                        . $filename
                        . $this->imageExtension($filepath));
                    $pictures[] = $filename . $this->imageExtension($filepath);
                }
            }
        }

        return $this->writePersonalDataFile(['pictures' => $pictures], 'pictures', $tempDir . 'pictures.html');
    }
}
