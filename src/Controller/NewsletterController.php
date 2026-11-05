<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Model\NewsletterArchiveModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{
    /**
     * The archive of published newsletters, newest first.
     */
    #[Route(path: '/newsletters/{page}', name: 'newsletters', requirements: ['page' => '\\d+'])]
    public function showNewslettersOverview(Request $request, NewsletterArchiveModel $archive, int $page = 1): Response
    {
        $locale = $request->getLocale();

        return $this->render('newsletter/list.html.twig', [
            'newsletters' => $archive->getPage($page, $locale),
            'summary' => $archive->getSummary($locale),
        ]);
    }

    /**
     * A newsletter in one language, with its other languages and the older and newer ones. A language it has no text
     * in leads to English or its first language.
     */
    #[Route(path: '/newsletters/{id}/{language}', name: 'newsletter_single')]
    public function showSingleNewsletter(
        Request $request,
        Newsletter $newsletter,
        string $language,
        NewsletterArchiveModel $archive,
    ): Response {
        $languages = $archive->getLanguages($newsletter);
        $redirect = $archive->getRedirectLanguage($languages, $language);
        if (null !== $redirect) {
            return $this->redirectToRoute('newsletter_single', ['id' => $newsletter->getId(), 'language' => $redirect]);
        }

        /** @var Member|null $member */
        $member = $this->getUser();

        return $this->render('newsletter/show.html.twig', [
            'newsletter' => $newsletter,
            'language' => $language,
            'languages' => $languages,
            'username' => null === $member ? 'anonymous' : $member->getUsername(),
            'neighbours' => $archive->getNeighbours($newsletter, $language, $request->getLocale()),
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
