<?php

namespace App\Service;

use App\Entity\Member;
use DateInterval;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFunction;

/**
 * Links in mails that act on the member's behalf (accept or decline an invitation, a join request, a friendship)
 * carry a signature that expires: nobody can make up such a link for someone else, and old mails stop working.
 * The actions check it with #[IsSignatureValid].
 * The pictures of members in mails are signed too: mail programs load them without the site's login, which the
 * avatars need otherwise (AvatarController::showAvatar()).
 */
class MailLinkSigner
{
    public const int DAYS_VALID = 30;

    /** Mails are read long after they came; an older one shows the empty avatar. */
    public const int AVATAR_DAYS_VALID = 365;

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

    /**
     * The member's picture for a mail, signed so that the mail program may load it without logging in.
     */
    #[AsTwigFunction('mail_avatar_url')]
    public function mailAvatarUrl(Member $member, int $size): string
    {
        return $this->uriSigner->sign(
            $this->urlGenerator->generate('avatar', ['username' => $member->getUsername(), 'size' => $size], UrlGeneratorInterface::ABSOLUTE_URL),
            new DateInterval('P' . self::AVATAR_DAYS_VALID . 'D'),
        );
    }
}
