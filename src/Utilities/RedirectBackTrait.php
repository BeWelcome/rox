<?php

namespace App\Utilities;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Back to the page a request came from, for controllers (uses AbstractController's redirect methods).
 *
 * The page comes from the Referer header, which browsers may leave out: a typed URL, a bookmark, a link in a mail,
 * privacy settings. redirect(null) then failed with a TypeError (HTTP 500), so there's always a route to fall back
 * to. Only pages of this site count: an action never sends a member on to another site.
 */
trait RedirectBackTrait
{
    /**
     * @param array<string, mixed> $parameters of the fallback route
     */
    protected function redirectBack(Request $request, string $fallbackRoute, array $parameters = []): RedirectResponse
    {
        return $this->redirectToPageOrRoute($request->headers->get('referer'), $request, $fallbackRoute, $parameters);
    }

    /**
     * To $url if it is a page of this site, else to the route.
     *
     * @param array<string, mixed> $parameters of the fallback route
     */
    protected function redirectToPageOrRoute(
        ?string $url,
        Request $request,
        string $fallbackRoute,
        array $parameters = [],
    ): RedirectResponse {
        if (null !== $url && '' !== $url) {
            $host = parse_url($url, \PHP_URL_HOST);
            if (null === $host || $host === $request->getHost()) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute($fallbackRoute, $parameters);
    }
}
