<?php

namespace App\Service;

use DateInterval;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFunction;

/**
 * Links in mails that act on the member's behalf (accept or decline an invitation, a join request, a friendship)
 * carry a signature that expires: nobody can make up such a link for someone else, and old mails stop working.
 * The actions check it with #[IsSignatureValid].
 */
class MailLinkSigner
{
    public const int DAYS_VALID = 30;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UriSigner $uriSigner,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[AsTwigFunction('signed_url')]
    public function signedUrl(string $route, array $parameters = []): string
    {
        return $this->uriSigner->sign(
            $this->urlGenerator->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL),
            new DateInterval('P' . self::DAYS_VALID . 'D'),
        );
    }
}
