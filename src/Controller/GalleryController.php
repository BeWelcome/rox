<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\GalleryImage;
use App\Entity\Group;
use App\Entity\Member;
use App\Entity\UploadedImage;
use App\Form\CustomDataClass\GalleryImageEditRequest;
use App\Form\GalleryEditImageFormType;
use App\Form\Select2Type;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use App\Utilities\UniqueFilenameTrait;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GalleryController extends AbstractController
{
    use TranslatorTrait;
    use TranslatedFlashTrait;
    use UniqueFilenameTrait;

    private const MAX_PIXELS = 16000000;

    /**
     * @Route("/gallery/show/image/{id}/edit", name="gallery_edit_image",
     *     requirements = {"id": "\d+"}
     * )
     *
     * @param Request      $request
     * @param GalleryImage $image
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function editImage(Request $request, GalleryImage $image)
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();

            $this->addTranslatedFlash('notice', 'flash.gallery.information.update');

            return $this->redirectToRoute('gallery_show_image', ['imageId' => $image->getId()]);
        }

        return $this->render('gallery/edit.image.html.twig', [
            'image' => $image,
            'form' => $editImageForm->createView(),
        ]);
    }

    /**
     * @Route("/new/image/upload", name="gallery_upload_new")
     *
     * @param Request $request
     *
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function handleImageUploadToGallery(Request $request, ValidatorInterface $validator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var Member $member */
        $member = $this->getUser();

        $response = new JsonResponse();
        // Create Image constraint to check if uploaded file is an image and not something else

        $constraint = new Image([
            'maxSize' => (int) ($this->getParameter('upload_max_size')),
            'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
            'mimeTypesMessage' => 'upload.error.not_supported',
            'maxPixels' => self::MAX_PIXELS,
        ]);

        /** @var UploadedFile $image */
        $this->getTranslator()->setLocale($request->getLocale());

        $image = $request->files->get('file');
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
        list($width, $height) = getimagesize($image);
        $uploadDirectory = $this->getParameter('gallery_directory') . '/member' . $member->getId();
        $fileName = $this->generateUniqueFileName() . '.' . $image->guessExtension();

        // moves the file to the directory where gallery images are stored
        /** @var UploadedFile */
        $image = $image->move(
            $uploadDirectory,
            $fileName
        );

        // creates a thumb nail for the current image
        $imageManager = new ImageManager();
        $img = $imageManager->make($image->getRealPath());
        if ($width > 240 || $height > 240) {
            $img->resize(240, 240, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $img->save($uploadDirectory . '/thumb' . $fileName);

        $albumId = $request->get('album');

        // Create doctrine entity for image and save to database
        $galleryImage = new GalleryImage();
        if ($albumId !== 0) {
            // Check if album exists
            // and if so check if the current member is owner of that album
            $galleryRepository = $this->getDoctrine()->getRepository(Gallery::class);
            /** @var Gallery $gallery */
            $gallery = $galleryRepository->findOneBy(['id' => $albumId]);
            if ($gallery && $gallery->getOwner() === $member) {
                $galleryImage->addGallery($gallery);
                $gallery->addImage($galleryImage);
            }
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
        $em = $this->getDoctrine()->getManager();
        $em->persist($galleryImage);
        $em->flush();

        $response->setData([
            'success' => true,
            'filename' => $originalName,
            'imageId' => $galleryImage->getId(),
            'constraints' => [
                'size' => (int) ($this->getParameter('upload_max_size')),
                'pixels' => self::MAX_PIXELS,
            ]
        ]);

        return $response;
    }

    /**
     * @Route("/gallery/upload_multiple", name="gallery_upload_multiple")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function uploadImagesToGallery(Request $request)
    {
        /** @var Member $member */
        $member = $this->getUser();

        $galleryRepository = $this->getDoctrine()->getRepository(Gallery::class);
        $galleries = $galleryRepository->findBy(['owner' => $member]);

        $albumTitles = [];
        if ($galleries) {
            $albumTitles[''] = 0;
            foreach($galleries as $gallery)
            {
                $albumTitles[$gallery->getTitle()] = $gallery->getId();
            }
        }

        $uploadImageForm = $this->createFormBuilder()
            ->add('albums', Select2Type::class, [
                'label' => 'gallery.upload_to_album',
                'choices' => $albumTitles,
                'searchbox' => false,
            ])
            ->add('files', FileType::class, [
                'label' => 'files',
                // 'multiple' => true,
            ])
            ->add('upload', SubmitType::class, [
                'label' => 'upload',
            ])
            ->add('abort', SubmitType::class, [
                'label' => 'abort',
            ])
            ->getForm();
        $uploadImageForm->handleRequest($request);
        if ($uploadImageForm->isSubmitted() && $uploadImageForm->isValid()) {
            // if this is called someone tries to hack the system as the Javascript on the upload page
            // takes care of uploading the files so we return an 403
            return new Response(403);
        }

        return $this->render('gallery/upload.image.html.twig', [
            'form' => $uploadImageForm->createView(),
            'submenu' => [
                'active' => 'upload',
                'items' => $this->getSubmenuItems(),
            ],
            'constraints' => [
                'size' => $this->getMaxUploadSizeInMegaBytes(),
                'pixels' => self::MAX_PIXELS,
            ]
        ]);
    }

    /**
     * @Route("/gallery/upload/image", name="gallery_upload_ckeditor")
     *
     * @param Request $request
     *
     * @param ValidatorInterface $validator
     * @return JsonResponse
     *
     * @throw AccessDeniedException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function uploadImageFromCKEditor5(Request $request, ValidatorInterface $validator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $response = new JsonResponse();

        // Create Image constraint to check if uploaded file is an image and not something else

        $constraint = new Image([
            'maxSize' => (int) ($this->getParameter('upload_max_size')),
            'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
            'mimeTypesMessage' => 'upload.error.not_supported',
        ]);

        $image = $request->files->get('upload');
        $violations = $validator->validate($image, $constraint);

        if (\count($violations) > 0) {
            $response->setData([
                'uploaded' => false,
                'error' => [
                    'message' => $violations->get(0)->getMessage(),
                ],
            ]);

            return $response;
        }

        list($width, $height) = getimagesize($image);
        $uploadDirectory = $this->getParameter('upload_directory');
        $fileName = $this->generateUniqueFileName() . '.' . $image->guessExtension();

        // moves the file to the directory where group images are stored
        /** @var UploadedFile */
        $image = $image->move(
            $uploadDirectory,
            $fileName
        );

        // Write database entry and get id for response
        $em = $this->getDoctrine()->getManager();
        $uploadedImage = new UploadedImage();
        $uploadedImage->setFilename($fileName);
        $uploadedImage->setwidth($width);
        $uploadedImage->setHeight($height);
        $uploadedImage->setMimeType($image->getMimeType());
        $em->persist($uploadedImage);
        $em->flush();

        $response->setData([
            'uploaded' => true,
            'url' => $this->generateUrl(
                'gallery_uploaded_ckeditor',
                [
                    'id' => $uploadedImage->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $response;
    }

    /**
     * @Route("/gallery/show/uploaded/{id}", name="gallery_uploaded_ckeditor",
     *     requirements={"id":"\d+"})
     *
     * @param UploadedImage $image
     *
     * @return BinaryFileResponse
     *
     * @throw AccessDeniedException
     */
    public function showUploadedImage(UploadedImage $image)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $filepath = $this->getParameter('upload_directory') . '/' . $image->getFilename();

        return new BinaryFileResponse($filepath);
    }

    /**
     * @return array
     */
    private function getSubmenuItems()
    {
        $member = $this->getUser();
        $submenuItems = [
            'manage' => [
                'key' => 'GalleryManage',
                'url' => $this->generateUrl('gallery_manage'),
            ],
            'upload' => [
                'key' => 'GalleryUpload',
                'url' => $this->generateUrl('gallery_upload_multiple'),
            ],
            'albums' => [
                'key' => 'GalleryMy',
                'url' => $this->generateUrl('gallery_show_user_albums', ['username' => $member->getUsername()]),
            ],
        ];

        return $submenuItems;
    }

    private function getMaxUploadSizeInMegaBytes()
    {
        return ((int) ($this->getParameter('upload_max_size'))) / 1024 / 1024;
    }
}
