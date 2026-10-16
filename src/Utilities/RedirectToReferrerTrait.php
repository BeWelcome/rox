<?php

namespace App\Utilities;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait RedirectToReferrerTrait.
 *
 * For controllers: back to the page the visitor came from. Browsers leave out the Referer header for a
 * bookmark, a new tab or strict privacy settings; redirect(null) then fails with a 500, so the visitor is
 * sent to a fallback route instead.
 */
trait RedirectToReferrerTrait
{
    protected function redirectToReferrer(
        Request $request,
        string $fallbackRoute = 'homepage',
        array $fallbackParameters = [],
    ): RedirectResponse {
        $referrer = $request->headers->get('referer');
        if (null === $referrer || '' === $referrer) {
            return $this->redirectToRoute($fallbackRoute, $fallbackParameters);
        }

        return $this->redirect($referrer);
    }
}
