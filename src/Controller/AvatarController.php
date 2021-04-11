<?php

namespace App\Controller;

use App\Entity\Member;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AvatarController.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class AvatarController extends AbstractController
{
    private const EXPIRY = 60 * 60 * 24; // One day
    private const AVATAR_PATH = '../data/user/avatars/';
    private const EMPTY_AVATAR_PATH = 'images/';

    /**
     * @Route("/members/changeavatar")
     */
    public function changeAvatar() {
        return $this->render('avatar/changeavatar.html.twig');
    }

    /**
     * @Route("/members/avatar/{username}/{size}", name="avatar",
     *     requirements={"username" : "(?i:[a-z][a-z0-9-._ ]{1,30}[a-z0-9-._])",
     *          "size" : "\d+|original" },
     *     _defaults={"size": "50"})
     */
    public function showAvatar(string $username, string $size): BinaryFileResponse
    {
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->emptyAvatar($size);
        }

        /** @var Member $member */
        $member = $this->getDoctrine()->getRepository(Member::class)->findOneBy(['username' => $username]);
        if (!$member) {
            return $this->emptyAvatar($size);
        }

        $isBrowseable = $member->isBrowseable();
        if (!$isBrowseable) {
            return $this->emptyAvatar($size);
        }

        if (!$this->avatarImageExists($member, $size)) {
            try {
                $this->createAvatarImage($member, $size);
            } catch (\InvalidArgumentException $e) {
                return $this->emptyAvatar($size);
            }
        }

        $filename = $this->getAvatarImageFilename($member, $size);
        return $this->createCacheableResponse($filename);
    }

    private function emptyAvatar($size): BinaryFileResponse
    {
        $filename = self::EMPTY_AVATAR_PATH . '/empty_avatar_' . $size . '_' . $size . '.png';

        if (!file_exists($filename)) {
            $filename = $this->createEmptyAvatarImage($size);
        }

        return $this->createCacheableResponse($filename, self::EXPIRY);
    }

    private function createCacheableResponse(string $filename, $expiry = self::EXPIRY): BinaryFileResponse
    {
        $response = new BinaryFileResponse($filename);
        $response->setSharedMaxAge($expiry);

        return $response;
    }

    private function getAvatarImageFilename(Member $member, string $size): string
    {
        $filename = self::AVATAR_PATH . $member->getId() . '_' . $size;
        if ($size !== 'original') {
            $filename .= '_' . $size;
        }

        return $filename;
    }

    private function avatarImageExists(Member $member, $size): bool
    {

        return file_exists($this->getAvatarImageFilename($member, $size));
    }

    private function createAvatarImage(Member $member, $size)
    {
        // creates a thumb nail for the current image (if we have an original that is)
        $original = self::AVATAR_PATH . $member->getId() . '_original';
        if (!file_exists($original)) {
            $message = 'No original avatar image exists for member ' . $member->getUsername();
            throw new \InvalidArgumentException($message);
        }

        $filename = self::AVATAR_PATH . $member->getId() . '_' . $size . '_' . $size;
        $imageManager = new ImageManager();
        $img = $imageManager->make($original);
        $img->resize($size, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($filename);
    }

    private function createEmptyAvatarImage(int $size): string
    {
        // creates a thumbnail of the empty avatar
        $original = self::EMPTY_AVATAR_PATH . 'empty_avatar_original.png';
        $filename = self::AVATAR_PATH . 'empty_avatar_' . $size . '_' . $size;

        $imageManager = new ImageManager();
        $img = $imageManager->make($original);
        $img->resize($size, $size, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($filename);

        return $filename;
    }
}
