<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\MemberPhoto;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * Class AvatarController.
 *
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 */
class AvatarController extends AbstractController
{
    private const EXPIRY = 60 * 60 * 24; // One day
    private const string AVATAR_PATH = '../data/user/avatars/';
    private const string EMPTY_AVATAR_PATH = 'images/';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/members/uploadavatar', methods: ['POST'])]
    public function uploadAvatar(Request $request): Response
    {
        $uploadFailedTranslation = $this->translator->trans('profile.picture.upload.failed');

        /** @var Member $member */
        $member = $this->getUser();
        if (null === $member) {
            return new Response($uploadFailedTranslation, Response::HTTP_UNAUTHORIZED);
        }

        /** @var UploadedFile $avatarFile */
        $avatar = $request->request->get('avatar');

        if (null === $avatar) {
            return new Response($uploadFailedTranslation, Response::HTTP_BAD_REQUEST);
        }

        $success = $this->storeAvatar($member, $avatar);

        if ($success) {
            return new Response('');
        }

        return new Response($uploadFailedTranslation, Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }

    #[Route(
        path: '/members/avatar/{username:member}/{size}',
        name: 'avatar',
        requirements: ['size' => '\d+|original'],
        defaults: ['size' => '48']
    )]
    public function showAvatar(Member $member, string $size): BinaryFileResponse
    {
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->emptyAvatar($size);
        }

        $isBrowsable = $member->isBrowsable();
        $isAdministrativeProfile =
            $this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
            || $this->isGranted(Member::ROLE_ADMIN_PROFILE)
        ;
        if (!$isBrowsable && !$isAdministrativeProfile) {
            return $this->emptyAvatar($size);
        }

        if (!$this->avatarImageExists($member, $size)) {
            try {
                $this->createAvatarImage($member, $size);
            } catch (InvalidArgumentException) {
                return $this->emptyAvatar($size);
            }
        }

        $filename = $this->getAvatarImageFilename($member, $size);

        return $this->createCacheableResponse($filename);
    }

    private function storeAvatar(Member $member, string $avatar): bool
    {
        $imageManager = new ImageManager(new Driver());
        try {
            $img = $imageManager->read($avatar);
        } catch (Throwable) {
            return false;
        }

        $this->removeAvatarFiles($member);
        $newFileName = self::AVATAR_PATH . $member->getId() . '_original';
        $img->save($newFileName);

        $memberPhotoRepository = $this->entityManager->getRepository(MemberPhoto::class);
        $memberPhoto = $memberPhotoRepository->findOneBy(['member' => $member->getId()], ['created' => 'DESC']);
        if (null === $memberPhoto) {
            $memberPhoto = new MemberPhoto();
        }
        $memberPhoto->setMember($member);
        $memberPhoto->setFilepath($newFileName);

        $this->entityManager->persist($memberPhoto);
        $this->entityManager->flush();

        $this->logger->info('New avatar picture was stored: ' . $newFileName);

        return true;
    }

    private function removeAvatarFiles($member): void
    {
        $finder = new Finder();
        $finder->name($member->getId() . '_*');
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

        return $this->createCacheableResponse($filename);
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

    private function avatarImageExists(Member $member, string $size): bool
    {
        return file_exists($this->getAvatarImageFilename($member, $size));
    }

    private function createAvatarImage(Member $member, string $sizeOfAvatar): void
    {
        // creates a thumbnail for the current image (if we have an original that is)
        $original = self::AVATAR_PATH . $member->getId() . '_original';
        if (!file_exists($original)) {
            $message = 'No original avatar image exists for member ' . $member->getUsername();
            throw new InvalidArgumentException($message);
        }

        $filename = self::AVATAR_PATH . $member->getId() . '_' . $sizeOfAvatar . '_' . $sizeOfAvatar;

        $imageManager = new ImageManager(new Driver());
        $img = $imageManager->read($original);

        $height = $img->height();
        $width = $img->width();
        if ($height !== $width) {
            $size = min($width, $height);
            $startX = (int) (($width - $size) / 2);
            $startY = (int) (($height - $size) / 2);
            $img->crop($size, $size, $startX, $startY);
        }

        $img->scale(width: $sizeOfAvatar);

        $img->save($filename, 100, 'jpg');
    }

    private function createEmptyAvatarImage(string $sizeOfAvatar): string
    {
        // creates a thumbnail of the empty avatar
        $original = self::EMPTY_AVATAR_PATH . 'empty_avatar_original.png';

        $imageManager = new ImageManager(new Driver());
        $img = $imageManager->read($original);
        if ('original' === $sizeOfAvatar) {
            $filename = $original;
        } else {
            $filename = self::AVATAR_PATH . 'empty_avatar_' . $sizeOfAvatar . '_' . $sizeOfAvatar;
            $img->scale(width: $sizeOfAvatar);
            $img->save($filename);
        }

        return $filename;
    }
}
