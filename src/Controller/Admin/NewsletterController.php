<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Word;
use App\Model\GalleryModel;
use App\Model\TranslationModel;
use Hidehalo\Nanoid\Client;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsletterController extends AbstractController
{
    /**
     * Work around to have newsletter directly translated without the need to save it in the
     * translation module.
     *
     * @Route("/admin/massmail/create/finish", name="admin_massmail_finish")
     */
    public function updateTranslations(TranslationModel $translationModel): RedirectResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_MASSMAIL)) {
            throw $this->createAccessDeniedException('You need to have Massmail right to access this.');
        }

        $translationModel->refreshTranslationsCacheForLocale();

        return $this->redirectToRoute('admin_massmail');
    }

    /**
     * Images added to a newsletter need to be publicly accessible; therefore we need a separate upload function.
     * Uploaded images are stored in /public/images/newsletters. Some checks need to be done to ensure that's safe.
     *
     * @Route("/newsletter/add/image", name="newsletter_add_image")
     */
    public function uploadImageForNewsletter(Request $request, GalleryModel $galleryModel): JsonResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_MASSMAIL)) {
            throw $this->createAccessDeniedException('You need to have Massmail right to access this.');
        }

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

        $uploadDirectory = $this->getParameter('newsletter_image_directory');
        $nanoClient = new Client();
        $nanoId = $nanoClient->generateId(16, Client::MODE_DYNAMIC);

        $filename = $nanoId;

        // moves the file to the directory where group images are stored
        /** @var UploadedFile */
        $image = $image->move(
            $uploadDirectory,
            $filename
        );

        $response->setData([
            'uploaded' => true,
            'url' => $this->generateUrl(
                'newsletter_uploaded_image',
                [
                    'id' => $nanoId,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $response;
    }
}
