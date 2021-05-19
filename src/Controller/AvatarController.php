<?php

namespace App\Controller;

use App\Entity\Member;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    private const AVATAR_TMP_SUFIX = "_tmp";
    private const AVATAR_SUFIX = "_original";

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @Route("/members/uploadavatar", methods={"POST"})
     */
    public function uploadAvatar(Request $request): Response
    {
        $member = $this->getUser();
        if (!$member || !$member->getId()) {
            return new Response('File upload failed', Response::HTTP_UNAUTHORIZED);
        }

        /** @var UploadedFile */
        $avatarFile = $request->files->get('avatar');
        if (!$avatarFile) {
            return new Response('File upload failed', Response::HTTP_BAD_REQUEST);
        }


        $isTemporary = !! $request->query->get('tmp');
        $this->storeAvatar($member->getId(), $avatarFile->getRealPath(), $isTemporary);

        return new Response('');
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
            } catch (InvalidArgumentException $e) {
                return $this->emptyAvatar($size);
            }
        }

        $filename = $this->getAvatarImageFilename($member, $size);

        return $this->createCacheableResponse($filename);
    }

    private function storeAvatar($memberId, $tmpFilePath, $isTemporary)
    {
        if (! $isTemporary) {
            $this->removeAvatarFiles($memberId, $isTemporary);
        }

        $imageManager = new ImageManager();
        $img = $imageManager->make($tmpFilePath);
        $height = $img->getHeight();
        $width = $img->getWidth();
        if ($height !== $width) {
            $size = min($width, $height);
            $startX = (int) (($width - $size) / 2);
            $startY = (int) (($height - $size) / 2);
            $img->crop($size, $size, $startX, $startY);
        }

        $newFileName = self::AVATAR_PATH . $memberId . ($isTemporary ? self::AVATAR_TMP_SUFIX : self::AVATAR_SUFIX);
        $img->save($newFileName);

        $this->logger->info('New avatar picture was stored: ' . $newFileName);

        return true;
    }

    private function removeAvatarFiles($memberId)
    {
        $finder = new Finder();
        $finder->name($memberId . '_*');
        foreach ($finder->files()->in(self::AVATAR_PATH) as $oldAvatarFile) {
            unlink($oldAvatarFile->getRealPath());
        }
    }

    private function emptyAvatar($size): BinaryFileResponse
    {
        $filename = self::AVATAR_PATH . 'empty_avatar_' . $size . '_' . $size . '.png';

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
        if ('original' !== $size) {
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
        $original = self::AVATAR_PATH . $member->getId() . self::AVATAR_SUFIX;
        if (!file_exists($original)) {
            $message = 'No original avatar image exists for member ' . $member->getUsername();
            throw new InvalidArgumentException($message);
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
        $original = self::EMPTY_AVATAR_PATH . 'empty_avatar' . self::AVATAR_SUFIX . '.png';
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
