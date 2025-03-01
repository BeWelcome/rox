<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends AbstractController
{
    /**
     * Show a page with all newsletters.
     */
    #[Route(path: '/newsletters/{page}', name: 'newsletters')]
    public function showNewslettersOverview(EntityManagerInterface $entityManager, int $page = 1)
    {
        $newsletters = [];
        $newsletterRepository = $entityManager->getRepository(Newsletter::class);
        $translationRepository = $entityManager->getRepository(Word::class);
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
     */
    #[Route(path: '/newsletters/{id}/{language}', name: 'newsletter_single')]
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
     */
    #[Route(path: '/images/newsletter/{id}', name: 'newsletter_uploaded_image')]
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
