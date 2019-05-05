<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\GalleryImage;
use App\Entity\UploadedImage;
use App\Form\CustomDataClass\GalleryImageEditRequest;
use App\Form\GalleryEditImageFormType;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\ImageValidator;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class GalleryController extends AbstractController
{
    // Limit uploaded files to 8MB
    const MAX_SIZE = 8*1024*1024;

    /**
     * @Route("/gallery/show/image/{id}/edit", name="gallery_edit_image",
     *     requirements = {"id": "\d+"}
     * )
     *
     * @param Request $request
     * @param GalleryImage $image
     *
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function editImageAction(Request $request, GalleryImage $image, TranslatorInterface $translator)
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

            $this->addFlash('notice', $translator->trans('flash.gallery.information.update'));

            return $this->redirectToRoute('gallery_show_image', ['imageId' => $image->getId()]);
        }

        return $this->render('gallery/edit.image.html.twig', [
            'image' => $image,
            'form' => $editImageForm->createView(),
        ]);
    }

    /**
     * @Route("/gallery/upload/image", name="gallery_upload_ckeditor")
     *
     * @param Request $request
     *
     * @param TranslatorInterface $translator
     * @return JsonResponse
     *
     * @throw AccessDeniedException
     */
    public function uploadImageFromCKEditor5(Request $request, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $response = new JsonResponse();

        // Create Image constraint to check if uploaded file is an image and not something else

        $constraint = new \Symfony\Component\Validator\Constraints\Image([
            'maxSize' => intval($this->getParameter('upload_max_size')),
            'mimeTypes' => [ 'image/jpeg', 'image/png', 'image/gif' ],
            'mimeTypesMessage' => $translator->trans('upload.error.not_supported'),
        ]);

        $image = $request->files->get('upload');
        $validator = Validation::createValidator();
        $violations = $validator->validate($image, $constraint);

        if (count($violations) > 0) {
            $response->setData([
                'uploaded' => false,
                'error' => [
                    'message' => $translator->trans('upload.error.no_image'),
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
            $this->getParameter('upload_directory'),
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
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}