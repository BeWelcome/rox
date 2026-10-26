<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\GalleryImage;
use App\Entity\Member;
use App\Entity\UploadedImage;
use App\Form\CustomDataClass\GalleryImageEditRequest;
use App\Form\GalleryEditImageFormType;
use App\Logger\Logger;
use App\Model\GalleryModel;
use App\Repository\GalleryImageRepository;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\UniqueFilenameTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hidehalo\Nanoid\Client;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 *
 * \todo rewrite to reduce coupling
 */
class GalleryController extends AbstractController
{
    use TranslatedFlashTrait;
    use UniqueFilenameTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        /** @var TranslatorInterface&LocaleAwareInterface $translator */
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws AccessDeniedException
     */
    #[Route(path: '/gallery/show/image/{id}/edit', name: 'gallery_edit_image', requirements: ['id' => '\d+'])]
    public function editImage(Request $request, GalleryImage $image): Response
    {
        $user = $this->getUser();
        if ($user !== $image->getOwner()) {
            throw $this->createAccessDeniedException('Not your image!');
        }

        $editImageRequest = new GalleryImageEditRequest($image);
        $editImageForm = $this->createForm(GalleryEditImageFormType::class, $editImageRequest);
        $editImageForm->handleRequest($request);
        if ($editImageForm->isSubmitted() && $editImageForm->isValid()) {
            $data = $editImageForm->getData();
            $image->setTitle($data->title);
            $image->setDescription($data->description);
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            $this->addTranslatedFlash('notice', 'flash.gallery.information.update');

            return $this->redirectToRoute('gallery_show_image', ['imageId' => $image->getId()]);
        }

        return $this->render('gallery/edit.image.html.twig', [
            'image' => $image,
            'form' => $editImageForm->createView(),
        ]);
    }

    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     *
     * \todo Extract method(s) to break down in digestible pieces.)
     */
    #[Route(path: '/new/image/upload', name: 'gallery_upload_new')]
    public function handleImageUploadToGallery(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var Member $member */
        $member = $this->getUser();

        $response = new JsonResponse();
        // Create Image constraint to check if uploaded file is an image and not something else

        /** @var UploadedFile $image */
        $image = $request->files->get('file');

        if (null === $image) {
            $this->translator->setLocale($request->getLocale());

            $response->setData([
                'success' => false,
                'filename' => $this->translator->trans('upload.error.no_image'),
                'error' => $this->translator->trans('upload.error.too_large'),
            ]);
            $response->setStatusCode(413);

            return $response;
        }

        $constraint = new Image(
            maxSize: UploadedFile::getMaxFilesize(),
            mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
            mimeTypesMessage: 'upload.error.not_supported'
        );

        $violations = $validator->validate($image, $constraint);

        $originalName = $image->getClientOriginalName();
        if (\count($violations) > 0) {
            $response->setData([
                'success' => false,
                'filename' => $originalName,
                'error' => $violations->get(0)->getMessage(),
            ]);
            $response->setStatusCode(415);

            return $response;
        }

        // We got an image and need to create a thumbnail for it and put it into the correct place
        [$width, $height] = getimagesize($image);
        $uploadDirectory = $this->getParameter('gallery_directory') . '/member' . $member->getId();
        $fileName = $this->generateUniqueFileName() . '.' . $image->guessExtension();

        // moves the file to the directory where gallery images are stored
        $image = $image->move(
            $uploadDirectory,
            $fileName
        );

        // creates a thumbnail for the current image
        $imageManager = new ImageManager(new Driver());
        $img = $imageManager->read($image->getRealPath())->orient();
        if ($width > 240 || $height > 240) {
            $img->scale(width: 240);
        }
        $img->save($uploadDirectory . '/thumb' . $fileName);

        $album = $request->request->get('album');

        // Create doctrine entity for image and save to database
        $galleryImage = new GalleryImage();
        if ('' !== $album) {
            // Check if album exists
            // and if so check if the current member is owner of that album
            $galleryRepository = $this->entityManager->getRepository(Gallery::class);
            /** @var Gallery $gallery */
            $gallery = $galleryRepository->findOneBy(['title' => $album, 'owner' => $member]);
            if (null === $gallery) {
                $gallery = new Gallery();
                $gallery->setTitle($album);
                $gallery->setOwner($member);
                $gallery->setFlags('');
                $gallery->setDescription('');

                $this->entityManager->persist($gallery);
                $this->entityManager->flush();
            }

            $galleryImage->addGallery($gallery);
            $gallery->addImage($galleryImage);
        }

        $galleryImage->setFile($fileName);
        $galleryImage->setFlags('');
        $galleryImage->setOriginal($originalName);
        $galleryImage->setHeight($height);
        $galleryImage->setWidth($width);
        $galleryImage->setOwner($member);
        $galleryImage->setMimetype($image->getMimeType());
        $galleryImage->setTitle('');
        $galleryImage->setDescription('');

        $this->entityManager->persist($galleryImage);
        $this->entityManager->flush();

        $response->setData([
            'success' => true,
            'filename' => $originalName,
            'imageId' => $galleryImage->getId(),
            'constraints' => [
                'size' => UploadedFile::getMaxFilesize(),
            ],
        ]);

        return $response;
    }

    /**
     * Pictures of a member, shown inside the profile layout.
     *
     * Takes over the legacy page at the same path (the legacy route keeps the name
     * gallery_show_user_images, so existing links still end up here).
     */
    #[Route(path: '/gallery/show/user/{username:member}/pictures', name: 'gallery_member_images')]
    public function showMemberImages(
        Member $member,
        ProfileSubmenu $profileSubmenu,
        ChangeProfilePictureGlobals $globals,
        #[MapQueryParameter] ?int $album = null,
    ): Response {
        if (
            !($member->isBrowsable()
                || $this->isGranted(Member::ROLE_ADMIN_ADMIN, $member)
                || $this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM, $member)
                || $this->isGranted(Member::ROLE_ADMIN_PROFILE, $member)
            )
        ) {
            throw $this->createAccessDeniedException();
        }

        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        /** @var GalleryImageRepository $galleryImageRepository */
        $galleryImageRepository = $this->entityManager->getRepository(GalleryImage::class);

        // Albums: a filter above the pictures, ?album= shows only the pictures of one of them
        $albums = $galleryImageRepository->getAlbumsForMember($member);
        $activeAlbum = null;
        foreach ($albums as $candidate) {
            if ($candidate['id'] === $album) {
                $activeAlbum = $candidate;
            }
        }

        return $this->render('gallery/member.images.html.twig', [
            'member' => $member,
            'pictures' => null !== $activeAlbum
                ? $galleryImageRepository->getImagesForMemberInAlbum($member, $activeAlbum['id'])
                : $galleryImageRepository->getImagesForMember($member),
            'pictures_total' => null !== $activeAlbum ? count($galleryImageRepository->getImagesForMember($member)) : null,
            'albums' => $albums,
            'active_album' => $activeAlbum,
            'globals_js_json' => $globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'gallery']),
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/gallery/upload_multiple', name: 'gallery_upload_multiple')]
    public function uploadImagesToGallery(): Response
    {
        return $this->redirectToRoute('gallery_manage', [], 301);
    }

    /**
     * @throws AccessDeniedException
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    #[Route(path: '/gallery/upload/image', name: 'gallery_upload_ckeditor')]
    public function uploadImageFromCKEditor5(Request $request, GalleryModel $galleryModel): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $response = new JsonResponse();

        $image = $request->files->get('upload');
        $errors = $galleryModel->checkUploadedImage($image);
        if (0 < \count($errors)) {
            $response->setData([
                'uploaded' => false,
                'error' => [
                    'message' => $errors->get(0)->getMessage(),
                ],
            ]);

            return $response;
        }

        // Check if an image with the same content already exists
        $hash = hash_file('sha256', $image->getPathname());

        $uploadedImageRepository = $this->entityManager->getRepository(UploadedImage::class);
        $existingImages = $uploadedImageRepository->findBy(['fileHash' => $hash]);

        if (0 === \count($existingImages)) {
            [$width, $height] = getimagesize($image);
            $uploadDirectory = $this->getParameter('upload_directory');
            $fileName = $this->generateUniqueFileName() . '.' . $image->guessExtension();

            // moves the file to the directory where group images are stored
            /** @var UploadedFile */
            $image = $image->move(
                $uploadDirectory,
                $fileName
            );

            $nanoId = new Client();
            $fileInfo = $nanoId->generateId(16, Client::MODE_DYNAMIC);

            // Write database entry and get id for response
            $uploadedImage = new UploadedImage();
            $uploadedImage
                ->setFilename($fileName)
                ->setSize($image->getSize())
                ->setwidth($width)
                ->setHeight($height)
                ->setMimeType($image->getMimeType())
                ->setFileInfo($fileInfo)
                ->setFileHash($hash)
            ;

            $this->entityManager->persist($uploadedImage);
            $this->entityManager->flush();
        } else {
            $uploadedImage = $existingImages[0];
        }

        $response->setData([
            'uploaded' => true,
            'url' => $this->generateUrl(
                'gallery_uploaded_ckeditor',
                [
                    'id' => $uploadedImage->getId(),
                    'fileInfo' => $uploadedImage->getFileInfo(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'width' => 123,
            'height' => 456,
        ]);

        return $response;
    }

    #[Route(path: '/gallery/show/uploaded/{id}', name: 'gallery_uploaded_ckeditor_old', requirements: ['id' => '\d+'])]
    public function showUploadedImageOld(UploadedImage $image, Logger $logger): Response
    {
        $logger->write('Image ' . $image->getId() . ' accessed using old URL', 'Image');

        return $this->forward(self::class . '::showUploadedImage', [
            'id' => $image->getId(),
            'fileInfo' => '', ]);
    }

    #[Route(path: '/gallery/show/uploaded/{id}/{fileInfo}', name: 'gallery_uploaded_ckeditor', requirements: ['id' => '\d+'])]
    public function showUploadedImage(UploadedImage $image, string $fileInfo): BinaryFileResponse
    {
        $uploadDirectory = $this->getParameter('upload_directory') . '/';
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED') || $image->getFileInfo() !== $fileInfo) {
            $filepath = \sprintf($uploadDirectory . 'placeholder_%d_%d.png', $image->getWidth(), $image->getHeight());
        } else {
            $filepath = $this->getParameter('upload_directory') . '/' . $image->getFilename();
        }
        if (!file_exists($filepath)) {
            // create image!
            $imageManager = new ImageManager(new Driver());
            $imageManager
                ->create($image->getWidth(), $image->getHeight())->fill('#ccc')
                ->save($filepath);
        }

        // Uploaded images aren't updated; set expiry to 1 year
        $response = new BinaryFileResponse($filepath);
        $response->setSharedMaxAge(31536000);

        return $response;
    }

}
