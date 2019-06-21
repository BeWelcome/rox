<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\GalleryImage;
use App\Entity\UploadedImage;
use App\Form\CustomDataClass\GalleryImageEditRequest;
use App\Form\GalleryEditImageFormType;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use App\Utilities\UniqueFilenameTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

class GalleryController extends AbstractController
{
    use UniqueFilenameTrait;
    use TranslatorTrait;
    use TranslatedFlashTrait;

    // Limit uploaded files to 8MB
    const MAX_SIZE = 8 * 1024 * 1024;

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
    public function editImageAction(Request $request, GalleryImage $image)
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
     * @Route("/gallery/upload_multiple", name="gallery_upload_multiple")
     *
     * @param Request $request
     *
     * @return Response
    public function uploadImageToGallery(Request $request)
    {
        $uploadImageForm = $this->createFormBuilder()
            ->add('files', FileType::class, [
                'label' => 'files',
                'multiple' => true,
            ])
            ->add('upload', SubmitType::class, [
                'label' => 'upload',
            ])
            ->getForm();
        $uploadImageForm->handleRequest($request);
        if ($uploadImageForm->isSubmitted() && $uploadImageForm->isValid())
        {
            $data = $uploadImageForm->getData();
        }

        return $this->render('gallery/upload.image.html.twig', [
            'form' => $uploadImageForm->createView(),
        ]);
    }
     */

    /**
     * @Route("/gallery/upload/image", name="gallery_upload_ckeditor")
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throw AccessDeniedException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function uploadImageFromCKEditor5(Request $request)
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
        $validator = Validation::createValidator();
        $violations = $validator->validate($image, $constraint);

        if (\count($violations) > 0) {
            $response->setData([
                'uploaded' => false,
                'error' => [
                    'message' => 'upload.error.no_image',
                ],
            ]);

            return $response;
        }

        list($width, $height) = getimagesize($image);
        $uploadDirectory = $this->getParameter('upload_directory');
        $fileName = $this->generateUniqueFileName().'.'.$image->guessExtension();

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

        $filepath = $this->getParameter('upload_directory').'/'.$image->getFilename();

        return new BinaryFileResponse($filepath);
    }
}
