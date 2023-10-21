<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\MembersPhoto;
use App\Entity\RightVolunteer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
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
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

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
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/members/uploadavatar", methods={"POST"})
     */
    public function uploadAvatar(Request $request): Response
    {
        $uploadFailedTranslation = $this->translator->trans('profile.picture.upload.failed');

        /** @var Member $member */
        $member = $this->getUser();
        if (!$member || !$member->getId()) {
            return new Response($uploadFailedTranslation, Response::HTTP_UNAUTHORIZED);
        }

        /** @var UploadedFile $avatarFile*/
        $avatarFile = $request->files->get('avatar');

        if (null === $avatarFile) {
            return new Response($uploadFailedTranslation, Response::HTTP_BAD_REQUEST);
        }

        $success = $this->storeAvatar($member, $avatarFile);

        if ($success) {
            return new Response('');
        }

        return new Response($uploadFailedTranslation, Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }

    /**
     * @Route("/members/avatar/{username}/{size}", name="avatar",
     *     requirements={"size" : "\d+|original" },
     *     defaults={"size": "48"})
     */
    public function showAvatar(Member $member, string $size): BinaryFileResponse {
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
            } catch (InvalidArgumentException $e) {
                return $this->emptyAvatar($size);
            }
        }

        $filename = $this->getAvatarImageFilename($member, $size);

        return $this->createCacheableResponse($filename);
    }

    private function storeAvatar(Member $member, UploadedFile $avatarFile): bool
    {
        $imageManager = new ImageManager();
        try {
            $img = $imageManager->make($avatarFile->getRealPath())->orientate();
        } catch (Throwable $e) {
            return false;
        }

        $this->removeAvatarFiles($member);
        $newFileName = self::AVATAR_PATH . $member->getId() . '_original';
        $img->save($newFileName);

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

    private function createAvatarImage(Member $member, int $sizeOfAvatar)
    {
        // creates a thumbnail for the current image (if we have an original that is)
        $original = self::AVATAR_PATH . $member->getId() . '_original';
        if (!file_exists($original)) {
            $message = 'No original avatar image exists for member ' . $member->getUsername();
            throw new InvalidArgumentException($message);
        }

        $filename = self::AVATAR_PATH . $member->getId() . '_' . $sizeOfAvatar . '_' . $sizeOfAvatar;

        $imageManager = new ImageManager();
        $img = $imageManager->make($original);

        $height = $img->getHeight();
        $width = $img->getWidth();
        if ($height !== $width) {
            $size = min($width, $height);
            $startX = (int) (($width - $size) / 2);
            $startY = (int) (($height - $size) / 2);
            $img->crop($size, $size, $startX, $startY);
        }

        $img->resize($sizeOfAvatar, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $img->save($filename, 100, 'jpg');
    }

    private function createEmptyAvatarImage($sizeOfAvatar): string
    {
        // creates a thumbnail of the empty avatar
        $original = self::EMPTY_AVATAR_PATH . 'empty_avatar_original.png';

        $imageManager = new ImageManager();
        $img = $imageManager->make($original);
        if (is_int($sizeOfAvatar)) {
            $filename = self::AVATAR_PATH . 'empty_avatar_' . $sizeOfAvatar . '_' . $sizeOfAvatar;
            $img->resize($sizeOfAvatar, $sizeOfAvatar, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($filename);
        } else {
            $filename = $original;
        }

        return $filename;
    }
}
