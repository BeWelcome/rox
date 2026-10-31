<?php

namespace App\Service;

use App\Entity\Gallery;
use App\Entity\GalleryImage;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

/**
 * A member's own pictures and albums, as the page /gallery/manage changes them: albums (galleries) with their
 * pictures, a picture's title and text, moving pictures to an album and deleting them with their files.
 * A picture is in at most one album.
 */
class MemberGallery
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%gallery_directory%')]
        private readonly string $galleryDirectory,
    ) {
    }

    /**
     * All the member's albums by title, empty ones too.
     *
     * @return list<array{id: int, title: string, count: int, cover: ?int}> (cover: the latest picture)
     */
    public function albums(Member $member): array
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select('g.id AS id, g.title AS title, COUNT(i.id) AS count, MAX(i.id) AS cover')
            ->from(Gallery::class, 'g')
            ->leftJoin('g.images', 'i')
            ->where('g.owner = :member')
            ->setParameter('member', $member)
            ->groupBy('g.id, g.title')
            ->orderBy('g.title', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'title' => html_entity_decode((string) $row['title'], \ENT_QUOTES, 'UTF-8'),
            'count' => (int) $row['count'],
            'cover' => null !== $row['cover'] ? (int) $row['cover'] : null,
        ], $rows);
    }

    /**
     * The member's pictures, newest first; with an album, only the pictures in it.
     *
     * @return list<GalleryImage>
     */
    public function pictures(Member $member, ?Gallery $album = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('i', 'a')
            ->from(GalleryImage::class, 'i')
            ->leftJoin('i.galleries', 'a')
            ->where('i.owner = :member')
            ->setParameter('member', $member)
            ->orderBy('i.created', 'DESC')
            ->addOrderBy('i.id', 'DESC')
        ;
        if (null !== $album) {
            $qb->innerJoin('i.galleries', 'g')
                ->andWhere('g = :album')
                ->setParameter('album', $album)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function album(Member $member, int $id): ?Gallery
    {
        return $this->entityManager->getRepository(Gallery::class)->findOneBy(['id' => $id, 'owner' => $member]);
    }

    /**
     * The member's pictures among these ids; ids of other members' pictures are left out.
     *
     * @param list<int> $ids
     *
     * @return list<GalleryImage>
     */
    public function picturesById(Member $member, array $ids): array
    {
        if ([] === $ids) {
            return [];
        }

        return $this->entityManager->getRepository(GalleryImage::class)->findBy(['id' => $ids, 'owner' => $member]);
    }

    public function createAlbum(Member $member, string $title): Gallery
    {
        $album = new Gallery();
        $album->setOwner($member);
        $album->setTitle($title);
        $album->setFlags('');
        $album->setDescription('');
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        return $album;
    }

    public function renameAlbum(Gallery $album, string $title): void
    {
        $album->setTitle($title);
        $this->entityManager->flush();
    }

    /**
     * Deletes the album only: its pictures stay, outside any album.
     */
    public function deleteAlbum(Gallery $album): void
    {
        foreach ($album->getImages()->toArray() as $picture) {
            $album->removeImage($picture);
            $picture->removeGallery($album);
        }
        $this->entityManager->remove($album);
        $this->entityManager->flush();
    }

    public function describe(GalleryImage $picture, string $title, string $description): void
    {
        $picture->setTitle($title);
        $picture->setDescription($description);
        $this->entityManager->flush();
    }

    /**
     * Puts the pictures in the album, or in no album when it is null.
     *
     * @param list<GalleryImage> $pictures
     */
    public function moveToAlbum(array $pictures, ?Gallery $album): void
    {
        foreach ($pictures as $picture) {
            foreach ($picture->getGalleries()->toArray() as $current) {
                $current->removeImage($picture);
                $picture->removeGallery($current);
            }
            if (null !== $album) {
                $album->addImage($picture);
                $picture->addGallery($album);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Deletes the pictures, their file and the smaller copies made of it (thumb, thumb1 to thumb3).
     *
     * @param list<GalleryImage> $pictures
     */
    public function delete(array $pictures): void
    {
        $filesystem = new Filesystem();
        foreach ($pictures as $picture) {
            $directory = $this->galleryDirectory . '/member' . $picture->getOwner()->getId() . '/';
            $file = (string) $picture->getFile();
            if ('' !== $file && !str_contains($file, '/')) {
                $filesystem->remove(array_map(
                    static fn (string $prefix): string => $directory . $prefix . $file,
                    ['', 'thumb', 'thumb1', 'thumb2', 'thumb3'],
                ));
            }
            foreach ($picture->getGalleries()->toArray() as $album) {
                $album->removeImage($picture);
                $picture->removeGallery($album);
            }
            $this->entityManager->remove($picture);
        }
        $this->entityManager->flush();
    }
}
