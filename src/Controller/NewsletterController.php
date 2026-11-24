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
            // what is not sent yet, for the volunteers who translate or write it
            'drafts' => 1 === $page ? $archive->getDrafts($locale, $this->translator(), $this->writesMassMails()) : [],
        ]);
    }

    /**
     * A newsletter in one language, with its other languages and the older and newer ones. A language it has no text
     * in leads to English or its first language. A draft (NewsletterArchiveModel::isDraft) is a 404 for everybody but
     * the mass mail volunteers and the translators of the language.
     */
    #[Route(path: '/newsletters/{id}/{language}', name: 'newsletter_single')]
    public function showSingleNewsletter(
        Request $request,
        Newsletter $newsletter,
        string $language,
        NewsletterArchiveModel $archive,
    ): Response {
        /** @var Member|null $member */
        $member = $this->getUser();
        $languages = $archive->getLanguages($newsletter);

        // A draft is no public page: see NewsletterArchiveModel::readsDraftIn. A translator gets it in a language
        // they translate with or without a text in it yet, and only their languages to go to.
        $translated = false;
        $draft = $archive->isDraft($newsletter);
        if ($draft) {
            $translator = $this->translator();
            $massMail = $this->writesMassMails();
            if (!$archive->readsDraftIn($language, $translator, $massMail)) {
                throw $this->createNotFoundException();
            }
            if (!$massMail) {
                $reads = static fn (string $code): bool => $archive->readsDraftIn($code, $translator, false);
                $languages = array_values(array_filter($languages, $reads));
                $translated = true;
            }
        }

        $redirect = $translated ? null : $archive->getRedirectLanguage($languages, $language);
        if (null !== $redirect) {
            return $this->redirectToRoute('newsletter_single', ['id' => $newsletter->getId(), 'language' => $redirect]);
        }

        return $this->render('newsletter/show.html.twig', [
            'newsletter' => $newsletter,
            'language' => $language,
            'languages' => $languages,
            'draft' => $draft,
            'username' => null === $member ? 'anonymous' : $member->getUsername(),
            'neighbours' => $archive->getNeighbours($newsletter, $language, $request->getLocale()),
        ]);
    }

    /**
     * An image uploaded for a newsletter (Admin\NewsletterController). An image that isn't there (any more) is a 404,
     * not an error.
     */
    #[Route(path: '/images/newsletter/{id}', name: 'newsletter_uploaded_image')]
    public function serveImage(string $id): BinaryFileResponse
    {
        $filepath = $this->getParameter('newsletter_image_directory') . '/' . $id;
        // {id} has no slash; a name starting with a dot (".", "..", hidden files) is no uploaded image
        if (str_starts_with($id, '.') || !is_file($filepath) || !is_readable($filepath)) {
            throw $this->createNotFoundException();
        }
        $mimeType = mime_content_type($filepath);

        // Uploaded images aren't updated; set expiry to 1 year
        $response = new BinaryFileResponse($filepath);
        $response->headers->set('Content-Type', $mimeType);
        $response->setSharedMaxAge(31536000);

        return $response;
    }

    /**
     * The member, if they translate the site (the Words right).
     */
    private function translator(): ?Member
    {
        $member = $this->getUser();

        return $member instanceof Member && $this->isGranted(Member::ROLE_ADMIN_WORDS) ? $member : null;
    }

    private function writesMassMails(): bool
    {
        return $this->isGranted(Member::ROLE_ADMIN_MASSMAIL);
    }
}
