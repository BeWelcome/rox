<?php

namespace App\Controller;

use App\Entity\GalleryImage;
use App\Security\MemberVoter;
use App\Service\GalleryImageFiles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The files of gallery pictures: the original and the smaller copies.
 */
class GalleryImageController extends AbstractController
{
    private const int MAX_AGE = 604800;

    /**
     * The sizes of the old gallery/thumbimg?t= (none or another value: the thumbnail).
     */
    private const array OLD_SIZES = [
        1 => GalleryImageFiles::SMALL,
        2 => GalleryImageFiles::MEDIUM,
        3 => GalleryImageFiles::LARGE,
    ];

    public function __construct(private readonly GalleryImageFiles $files)
    {
    }

    #[Route(path: '/gallery/images/{id:image}', name: 'gallery_image_original', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function original(GalleryImage $image): BinaryFileResponse
    {
        return $this->send($image, $this->files->original($image));
    }

    #[Route(path: '/gallery/images/{id:image}/download', name: 'gallery_image_download', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function download(GalleryImage $image): BinaryFileResponse
    {
        $response = $this->send($image, $this->files->original($image));
        $name = (string) $image->getOriginal();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            '' !== $name ? $name : basename($response->getFile()->getPathname()),
            'picture-' . $image->getId() . '.' . ($response->getFile()->guessExtension() ?? 'jpg'),
        );

        return $response;
    }

    #[Route(
        path: '/gallery/images/{id:image}/{size}',
        name: 'gallery_image',
        requirements: ['id' => '\d+', 'size' => 'thumbnail|small|medium|large'],
        methods: ['GET'],
    )]
    public function sized(GalleryImage $image, string $size): BinaryFileResponse
    {
        return $this->send($image, $this->files->sized($image, $size));
    }

    /**
     * The old URL of the original (gallery/img?id=), linked in posts, wiki pages and messages; s=1 downloaded it.
     */
    #[Route(path: '/gallery/img', name: 'gallery_image_old', methods: ['GET'])]
    public function oldOriginal(Request $request): RedirectResponse
    {
        $id = $this->oldId($request);
        $route = $request->query->has('s') ? 'gallery_image_download' : 'gallery_image_original';

        return $this->redirectToRoute($route, ['id' => $id], 301);
    }

    /**
     * The old URL of the smaller copies (gallery/thumbimg?id=&t=).
     */
    #[Route(path: '/gallery/thumbimg', name: 'gallery_thumbnail_old', methods: ['GET'])]
    public function oldSized(Request $request): RedirectResponse
    {
        $id = $this->oldId($request);
        $size = self::OLD_SIZES[$request->query->getInt('t')] ?? GalleryImageFiles::THUMBNAIL;

        return $this->redirectToRoute('gallery_image', ['id' => $id, 'size' => $size], 301);
    }

    private function oldId(Request $request): int
    {
        $id = $request->query->getInt('id');
        if ($id <= 0) {
            throw new NotFoundHttpException();
        }

        return $id;
    }

    private function send(GalleryImage $image, ?string $path): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted(MemberVoter::MEMBER_VIEW, $image->getOwner());
        if (null === $path) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($path);
        $mimetype = (string) $image->getMimetype();
        if (str_starts_with($mimetype, 'image/')) {
            $response->headers->set('Content-Type', $mimetype);
        }
        $response->setPrivate();
        $response->setMaxAge(self::MAX_AGE);
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
