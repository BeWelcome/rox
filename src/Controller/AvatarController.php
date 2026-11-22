<?php

namespace App\Controller;

use App\Attribute\ReleasesSession;
use App\Entity\Member;
use App\Entity\MemberPhoto;
use App\Security\MemberVoter;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
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
    private const int ORIGINAL_QUALITY = 90;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly UriSigner $uriSigner,
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
    #[ReleasesSession]
    public function showAvatar(Request $request, Member $member, string $size): BinaryFileResponse
    {
        // a link from a mail (MailLinkSigner::mailAvatarUrl()): the receiver is a member, the mail program isn't
        // logged in; the picture shows as long as members may see the profile
        if ($this->uriSigner->checkRequest($request)) {
            if (!$member->isVisibleToMembers()) {
                return $this->emptyAvatar($size);
            }
        } elseif (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED') || !$this->isGranted(MemberVoter::MEMBER_VIEW, $member)) {
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

                return $this->emptyAvatar($size);
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

    private function storeAvatar(Member $member, string $avatar): bool
    {
        $imageManager = new ImageManager(new Driver());
        try {
            $img = $imageManager->decode($avatar);
        } catch (Throwable) {
            return false;
        }

        $this->removeAvatarFiles($member);
        $newFileName = self::AVATAR_PATH . $member->getId() . '_original';
        // the avatar files have no extension: keep the format of the uploaded picture (the cropper sends WebP at
        // quality 90, PNG from a browser that can't write WebP); written again at that quality, not the default 75
        $img->encode(new AutoEncoder(quality: self::ORIGINAL_QUALITY))->save($newFileName);

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
        // the name createEmptyAvatarImage() writes: looked up with ".png" it was never found, so the placeholder was
        // made again for every request, and a request could read it half written
        $filename = self::AVATAR_PATH . 'empty_avatar_' . $size . '_' . $size;

        if ('original' === $size || !file_exists($filename)) {
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
        $img = $imageManager->decodePath($original);

        $height = $img->height();
        $width = $img->width();
        if ($height !== $width) {
            $size = min($width, $height);
            $startX = (int) (($width - $size) / 2);
            $startY = (int) (($height - $size) / 2);
            $img->crop($size, $size, $startX, $startY);
        }

        $img->scale(width: $sizeOfAvatar);

        $img->encodeUsingFormat(Format::JPEG, quality: 100)->save($filename);
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
            $img->scale(width: $sizeOfAvatar);
            $img->encode()->save($filename);
        }

        return $filename;
    }
}
