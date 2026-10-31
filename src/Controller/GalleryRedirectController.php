<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\GalleryImage;
use App\Entity\Member;
use App\Security\MemberVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The URLs of the old gallery pages. They are linked in posts, wiki pages and messages, so they lead
 * to the pages that replaced them.
 */
class GalleryRedirectController extends AbstractController
{
    /**
     * A member's gallery: the overview, the albums, the latest pictures and their pages.
     */
    #[Route(
        path: '/gallery/show/user/{username:member}/{rest}',
        name: 'gallery_member_old',
        requirements: ['rest' => '.*'],
        defaults: ['rest' => ''],
        methods: ['GET'],
    )]
    public function member(Request $request, Member $member): RedirectResponse
    {
        $album = $request->query->getInt('album');
        if ($album > 0) {
            return $this->redirectToRoute('member_gallery_album', ['username' => $member->getUsername(), 'album' => $album], 301);
        }

        return $this->redirectToRoute('member_gallery', ['username' => $member->getUsername()], 301);
    }

    /**
     * A picture's page: the picture in its owner's gallery.
     */
    #[Route(path: '/gallery/show/image/{id:image}', name: 'gallery_image_page_old', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function image(GalleryImage $image): RedirectResponse
    {
        $owner = $image->getOwner();
        $this->denyAccessUnlessGranted(MemberVoter::MEMBER_VIEW, $owner);

        return $this->redirect(
            $this->generateUrl('member_gallery', ['username' => $owner->getUsername()]) . '#picture-' . $image->getId(),
            301,
        );
    }

    /**
     * An album.
     */
    #[Route(path: '/gallery/show/sets/{id:album}', name: 'gallery_album_old', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function album(Gallery $album): RedirectResponse
    {
        $owner = $album->getOwner();
        $this->denyAccessUnlessGranted(MemberVoter::MEMBER_VIEW, $owner);

        return $this->redirectToRoute('member_gallery_album', ['username' => $owner->getUsername(), 'album' => $album->getId()], 301);
    }

    /**
     * The start of the old gallery and the list of albums: the member's own gallery.
     */
    #[Route(path: '/gallery', name: 'gallery_old', methods: ['GET'])]
    #[Route(path: '/gallery/show/sets', name: 'gallery_albums_old', methods: ['GET'])]
    public function own(): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->redirectToRoute('member_gallery', ['username' => $member->getUsername()], 301);
    }

    /**
     * Uploading, managing and deleting pictures and albums: all on the page to manage the gallery now.
     */
    #[Route(path: '/gallery/upload', name: 'gallery_upload_old', methods: ['GET'])]
    #[Route(path: '/gallery/uploaded', name: 'gallery_uploaded_old', methods: ['GET'])]
    #[Route(path: '/gallery/manage/={page}', name: 'gallery_manage_pages_old', requirements: ['page' => 'page\d+'], methods: ['GET'])]
    #[Route(path: '/gallery/show/image/{id}/delete', name: 'gallery_image_delete_old', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Route(path: '/gallery/show/sets/{id}/delete', name: 'gallery_album_delete_old', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Route(path: '/gallery/show/sets/{id}/delete/true', name: 'gallery_album_delete_confirmed_old', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function manage(): RedirectResponse
    {
        return $this->redirectToRoute('gallery_manage_page', [], 301);
    }
}
