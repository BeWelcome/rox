<?php

namespace App\Controller;

use App\Dto\Gallery\AlbumTitle;
use App\Dto\Gallery\PictureDetails;
use App\Dto\Gallery\PictureSelection;
use App\Entity\GalleryImage;
use App\Entity\Member;
use App\Service\MemberGallery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The member's own pictures on /gallery/manage (it takes over the legacy page at that path): one grid of
 * pictures with the albums as pills above it. Photos are added from the page (they go to the album shown),
 * a picture opens in a panel to give it a title, a text, an album or to delete it, and several pictures can be
 * picked to move or delete them together. The changes are small JSON calls; the page updates in place.
 */
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class GalleryManageController extends AbstractController
{
    public const string TOKEN = 'gallery_manage';

    public function __construct(private readonly MemberGallery $gallery)
    {
    }

    #[Route(path: '/gallery/manage', name: 'gallery_manage_page', methods: ['GET'], priority: 10)]
    #[Route(path: '/gallery/manage/albums/{album}', name: 'gallery_manage_album', requirements: ['album' => '\d+'], methods: ['GET'], priority: 10)]
    public function manage(Request $request, ?int $album = null): Response
    {
        // The album was a query parameter before
        $oldAlbum = $request->query->getInt('album');
        if (null === $album && $oldAlbum > 0) {
            return $this->redirectToRoute('gallery_manage_album', ['album' => $oldAlbum], 301);
        }

        $member = $this->member();
        $albums = $this->gallery->albums($member);
        $activeAlbum = null !== $album ? $this->gallery->album($member, $album) : null;
        if (null !== $album && null === $activeAlbum) {
            throw $this->createNotFoundException();
        }
        $pictures = $this->gallery->pictures($member, $activeAlbum);
        $all = null === $activeAlbum ? $pictures : $this->gallery->pictures($member);

        return $this->render('gallery/manage.html.twig', [
            'member' => $member,
            'albums' => $albums,
            'active_album' => null === $activeAlbum ? null : $this->albumRow($albums, $activeAlbum->getId()),
            'pictures' => array_map($this->pictureRow(...), $pictures),
            'total' => \count($all),
            'latest' => [] !== $all ? $all[0]->getId() : null,
            'max_upload' => UploadedFile::getMaxFilesize(),
        ]);
    }

    #[Route(path: '/gallery/manage/albums', name: 'gallery_manage_album_create', methods: ['POST'])]
    public function createAlbum(Request $request, #[MapRequestPayload(acceptFormat: 'json')] AlbumTitle $payload): JsonResponse
    {
        $this->checkToken($request);
        $album = $this->gallery->createAlbum($this->member(), trim($payload->title));

        return $this->json(['id' => $album->getId(), 'title' => $album->getTitle()]);
    }

    #[Route(path: '/gallery/manage/albums/{id}', name: 'gallery_manage_album_rename', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function renameAlbum(Request $request, int $id, #[MapRequestPayload(acceptFormat: 'json')] AlbumTitle $payload): JsonResponse
    {
        $this->checkToken($request);
        $album = $this->gallery->album($this->member(), $id) ?? throw $this->createNotFoundException();
        $this->gallery->renameAlbum($album, trim($payload->title));

        return $this->json(['id' => $album->getId(), 'title' => $album->getTitle()]);
    }

    #[Route(path: '/gallery/manage/albums/{id}/delete', name: 'gallery_manage_album_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteAlbum(Request $request, int $id): JsonResponse
    {
        $this->checkToken($request);
        $album = $this->gallery->album($this->member(), $id) ?? throw $this->createNotFoundException();
        $this->gallery->deleteAlbum($album);

        return $this->json(['deleted' => $id]);
    }

    #[Route(path: '/gallery/manage/pictures/{id}', name: 'gallery_manage_picture', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function savePicture(Request $request, int $id, #[MapRequestPayload(acceptFormat: 'json')] PictureDetails $payload): JsonResponse
    {
        $this->checkToken($request);
        $member = $this->member();
        $picture = $this->gallery->picturesById($member, [$id])[0] ?? throw $this->createNotFoundException();
        $album = null !== $payload->album ? ($this->gallery->album($member, $payload->album) ?? throw $this->createNotFoundException()) : null;

        $this->gallery->describe($picture, trim($payload->title), trim($payload->description));
        $this->gallery->moveToAlbum([$picture], $album);

        return $this->json($this->pictureRow($picture));
    }

    #[Route(path: '/gallery/manage/pictures/move', name: 'gallery_manage_pictures_move', methods: ['POST'])]
    public function movePictures(Request $request, #[MapRequestPayload(acceptFormat: 'json')] PictureSelection $payload): JsonResponse
    {
        $this->checkToken($request);
        $member = $this->member();
        $album = null !== $payload->album ? ($this->gallery->album($member, $payload->album) ?? throw $this->createNotFoundException()) : null;
        $pictures = $this->gallery->picturesById($member, $payload->pictures);
        $this->gallery->moveToAlbum($pictures, $album);

        return $this->json(['moved' => array_map(static fn (GalleryImage $picture): int => $picture->getId(), $pictures)]);
    }

    #[Route(path: '/gallery/manage/pictures/delete', name: 'gallery_manage_pictures_delete', methods: ['POST'])]
    public function deletePictures(Request $request, #[MapRequestPayload(acceptFormat: 'json')] PictureSelection $payload): JsonResponse
    {
        $this->checkToken($request);
        $pictures = $this->gallery->picturesById($this->member(), $payload->pictures);
        $ids = array_map(static fn (GalleryImage $picture): int => $picture->getId(), $pictures);
        $this->gallery->delete($pictures);

        return $this->json(['deleted' => $ids]);
    }

    private function member(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }

    private function checkToken(Request $request): void
    {
        if (!$this->isCsrfTokenValid(self::TOKEN, (string) $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException('Invalid token');
        }
    }

    /**
     * @param list<array{id: int, title: string, count: int, cover: ?int}> $albums
     *
     * @return array{id: int, title: string, count: int, cover: ?int}|null
     */
    private function albumRow(array $albums, int $id): ?array
    {
        foreach ($albums as $album) {
            if ($album['id'] === $id) {
                return $album;
            }
        }

        return null;
    }

    /**
     * Titles and texts written by the legacy pages are stored with HTML entities: they are decoded here, Twig
     * escapes them again.
     *
     * @return array{id: int, title: string, description: string, album: ?int}
     */
    private function pictureRow(GalleryImage $picture): array
    {
        $album = $picture->getGalleries()->first();

        return [
            'id' => $picture->getId(),
            'title' => html_entity_decode((string) $picture->getTitle(), \ENT_QUOTES, 'UTF-8'),
            'description' => html_entity_decode((string) $picture->getDescription(), \ENT_QUOTES, 'UTF-8'),
            'album' => false !== $album ? $album->getId() : null,
        ];
    }
}
