<?php

namespace App\Utilities;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Actions linked from mails (accept or decline an invitation, a join request, a friendship) only run with the
 * link's signature (MailLinkSigner), which expires. Without it the member gets a note and the page to act on.
 * For controllers extending AbstractController that use TranslatedFlashTrait.
 */
trait MailLinkTrait
{
    /**
     * @param array<string, mixed> $parameters
     */
    private function rejectMailLink(string $route, array $parameters = []): RedirectResponse
    {
        $this->addTranslatedFlash('notice', 'flash.mail.link.expired');

        return $this->redirectToRoute($route, $parameters);
    }
}
