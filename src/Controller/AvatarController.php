<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\MembersPhoto;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Format;
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

    public function __construct(private readonly LoggerInterface $logger, private readonly EntityManagerInterface $entityManager, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @Route("/members/uploadavatar", methods={"POST"})
     */
    public function uploadAvatar(Request $request, EntityManagerInterface $entityManager): Response
    {
        $uploadFailedTranslation = $this->translator->trans('profile.picture.upload.failed');

        /** @var Member $member */
        $member = $this->getUser();
        if (!$member || !$member->getId()) {
            return new Response($uploadFailedTranslation, Response::HTTP_UNAUTHORIZED);
        }

        /** @var UploadedFile $avatarFile */
        $avatarFile = $request->files->get('avatar');

        if (null === $avatarFile) {
            return new Response($uploadFailedTranslation, Response::HTTP_BAD_REQUEST);
        }

        $this->storeAvatar($entityManager, $member, $avatarFile->getRealPath());

        if ($success) {
            return new Response('');
        }

        return new Response($uploadFailedTranslation, Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }

    #[Route(path: '/members/avatar/{username:member}/{size}', name: 'avatar', requirements: ['size' => '\d+|original'], defaults: ['size' => '48'])]
    public function showAvatar(Member $member, string $size): BinaryFileResponse
    {
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->emptyAvatar($size);
        }

        $isBrowsable = $member->isBrowsable();
        $isAdministrativeProfile =
            $this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
            || $this->isGranted(Member::ROLE_ADMIN_PROFILE);

        if (!$isBrowsable && !$isAdministrativeProfile) {
            return $this->emptyAvatar($size);
        }

        if (!$this->avatarImageExists($member, $size)) {
            try {
                $this->createAvatarImage($member, $size);
            } catch (InvalidArgumentException) {
                return $this->emptyAvatar($size);
            } catch (Throwable $throwable) {
                $this->logger->warning(\sprintf(
                    'Creating avatar image (size %s) for member %d failed: %s',
                    $size,
                    $member->getId(),
                    $throwable->getMessage()
                ));
            }
        }

        $filename = $this->getAvatarImageFilename($member, $size);

        if (!is_file($filename) || !is_readable($filename)) {
            $this->logger->warning(\sprintf(
                'Avatar image %s for member %d is missing or not readable, falling back to empty avatar',
                $filename,
                $member->getId()
            ));

            return $this->emptyAvatar($size);
        }

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

        $this->removeAvatarFiles($member);
        $newFileName = self::AVATAR_PATH . $member->getId() . '_original';
        $img->encodeUsingFormat(Format::PNG, quality: 100)->save($newFileName);

        $memberPhotoRepository = $this->entityManager->getRepository(MembersPhoto::class);
        $memberPhoto = $memberPhotoRepository->findOneBy(['member' => $member->getId()], ['created' => 'DESC']);
        if (null === $memberPhoto) {
            $memberPhoto = new MembersPhoto();
        }
        $memberPhoto->setMember($member);
        $memberPhoto->setFilepath($newFileName);
        $memberPhoto->setCreated(new DateTime());
        $memberPhoto->setComment('Uploaded new avatar');

        $this->entityManager->persist($memberPhoto);
        $this->entityManager->flush();

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

    private function removeAvatarFiles($member)
    {
        $finder = new Finder();
        $finder->name($member->getId() . '_*');
        foreach ($finder->files()->in(self::AVATAR_PATH) as $oldAvatarFile) {
            unlink($oldAvatarFile->getRealPath());
        }
    }

    private function emptyAvatar($size): BinaryFileResponse
    {
        $filename = self::AVATAR_PATH . 'empty_avatar_' . $size . '_' . $size;

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

    private function avatarImageExists(Member $member, string $size): bool
    {
        return file_exists($this->getAvatarImageFilename($member, $size));
    }

    private function createAvatarImage(Member $member, string $sizeOfAvatar)
    {
        // creates a thumbnail for the current image (if we have an original that is)
        $original = self::AVATAR_PATH . $member->getId() . '_original';
        if (!file_exists($original)) {
            $message = 'No original avatar image exists for member ' . $member->getUsername();
            throw new InvalidArgumentException($message);
        }

        $filename = self::AVATAR_PATH . $member->getId() . '_' . $sizeOfAvatar . '_' . $sizeOfAvatar;

        $imageManager = new ImageManager(new Driver());
        $img = $imageManager->decodePath($original);

        $height = $img->height();
        $width = $img->width();
        if ($height !== $width) {
            $size = min($width, $height);
            $startX = (int) (($width - $size) / 2);
            $startY = (int) (($height - $size) / 2);
            $img->crop($size, $size, $startX, $startY);
        }

        $img->scale((int) $sizeOfAvatar);

        $img->encodeUsingFormat(Format::PNG, quality: 100)->save($filename);
    }

    private function createEmptyAvatarImage(string $sizeOfAvatar): string
    {
        // creates a thumbnail of the empty avatar
        $original = self::EMPTY_AVATAR_PATH . 'empty_avatar_original.png';

        $imageManager = new ImageManager(new Driver());
        $img = $imageManager->decodePath($original);
        if ('original' === $sizeOfAvatar) {
            $filename = $original;
        } else {
            $filename = self::AVATAR_PATH . 'empty_avatar_' . $sizeOfAvatar . '_' . $sizeOfAvatar;
            $img->scale((int) $sizeOfAvatar, (int) $sizeOfAvatar);
            $img->encodeUsingFormat(Format::PNG, quality: 100)->save($filename);
        }

        return $filename;
    }
}
