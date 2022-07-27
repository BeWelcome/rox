<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\MembersPhoto;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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
    public function uploadAvatar(Request $request, EntityManagerInterface $entityManager): Response
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

        $this->storeAvatar($entityManager, $member, $avatarFile->getRealPath());

        return new Response('');
    }

    /**
     * @Route("/members/avatar/{username}/{size}", name="avatar",
     *     requirements={"username" : "(?i:[a-z][a-z0-9-._ ]{1,30}[a-z0-9-._])",
     *          "size" : "\d+|original" },
     *     defaults={"size": "50"})
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

    private function storeAvatar($entityManager, $member, $tmpFilePath)
    {
        // TODO
        // $this->writeMemberphoto($memberId);
        $memberId = $member->getId();
        $this->removeAvatarFile($memberId);

        $imageManager = new ImageManager();
        $img = $imageManager->make($tmpFilePath)->orientate();
        $height = $img->getHeight();
        $width = $img->getWidth();
        if ($height !== $width) {
            $size = min($width, $height);
            $startX = (int) (($width - $size) / 2);
            $startY = (int) (($height - $size) / 2);
            $img->crop($size, $size, $startX, $startY);
        }

        $newFileName = self::AVATAR_PATH . $memberId . '_original';
        $img->save($newFileName);

        $memberPhotoRepository = $entityManager->getRepository(MembersPhoto::class);
        $memberPhoto = $memberPhotoRepository->findOneBy(['member' => $memberId], ['created' => 'DESC']);
        if (null === $memberPhoto) {
            $memberPhoto = new MembersPhoto();
        }
        $memberPhoto->setMember($member);
        $memberPhoto->setFilepath($newFileName);
        $memberPhoto->setCreated(new DateTime());
        $memberPhoto->setComment('Uploaded new avatar');

        $entityManager->persist($memberPhoto);
        $entityManager->flush();

        $this->logger->info('New avatar picture was stored: ' . $newFileName);

        return true;
    }

    private function removeAvatarFile($memberId)
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
        $original = self::AVATAR_PATH . $member->getId() . '_original';
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

    private function createEmptyAvatarImage($size): string
    {
        // creates a thumbnail of the empty avatar
        $original = self::EMPTY_AVATAR_PATH . 'empty_avatar_original.png';

        $imageManager = new ImageManager();
        $img = $imageManager->make($original);
        if (is_int($size)) {
            $filename = self::AVATAR_PATH . 'empty_avatar_' . $size . '_' . $size;
            $img->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($filename);
        } else {
            $filename = $original;
        }

        return $filename;
    }
}
