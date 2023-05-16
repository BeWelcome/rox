<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Entity\Word;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends AbstractController
{
    /**
     * Show a page with all newsletters.
     *
     * @Route("/newsletters/{page}", name="newsletters")
     *
     * @param mixed $page
     */
    public function showNewslettersOverview($page = 1)
    {
        $newsletters = [];
        $newsletterRepository = $this->getDoctrine()->getRepository(Newsletter::class);
        $translationRepository = $this->getDoctrine()->getRepository(Word::class);
        $newslettersRaw = $newsletterRepository->findAllPublished();

        /** @var Newsletter $newsletter */
        foreach ($newslettersRaw as $newsletter) {
            $id = $newsletter->getId();
            $name = $newsletter->getName();
            $translations = $translationRepository->findBy([
                'code' => [
                    strtolower('Broadcast_body_' . $name),
                ],
            ]);
            $languages = [];
            foreach ($translations as $translatedNewsletter) {
                $languages[] = $translatedNewsletter->getShortCode();
            }
            $newsletters[] = [
                'id' => $id,
                'name' => $name,
                'created' => $newsletter->getCreated(),
                'languages' => $languages,
            ];
        }

        $pagerfanta = new Pagerfanta(new ArrayAdapter($newsletters));
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page);

        return $this->render('newsletter/list.html.twig', [
            'newsletters' => $pagerfanta,
        ]);
    }

    /**
     * Show a single newsletter translated.
     *
     * @Route("/newsletters/{id}/{language}", name="newsletter_single")
     */
    public function showSingleNewsletter(Newsletter $newsletter, string $language)
    {
        $member = $this->getUser();
        $username = (null === $member) ? 'anonymous' : $member->getUsername();

        // \todo check if newsletter was translated into the given language
        return $this->render('newsletter/show.html.twig', [
            'newsletter' => $newsletter,
            'language' => $language,
            'username' => $username,
        ]);
    }

    /**
     * This is used to define the route but never reached as the .htaccess loads files it finds directly.
     *
     * @Route("/images/newsletter/{id}", name="newsletter_uploaded_image")
     */
    public function serveImage(string $id): BinaryFileResponse
    {
        $filepath = $this->getParameter('newsletter_image_directory') . '/' . $id;
        $mimeType = mime_content_type($filepath);

        // Uploaded images aren't updated; set expiry to 1 year
        $response = new BinaryFileResponse($filepath);
        $response->headers->set('Content-Type', $mimeType);
        $response->setSharedMaxAge(31536000);

        return $response;
    }
}
