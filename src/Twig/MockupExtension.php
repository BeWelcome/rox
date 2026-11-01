<?php

namespace App\Twig;

/**
 * The email variable of the translators' previews of mails (MockupController::mockupFrame()): images the mailer
 * attaches (email.image('@images/...')) are links to the site's images instead.
 */
class MockupExtension
{
    public function image(string $image): string
    {
        return '/images/' . ltrim(preg_replace('~^@images/~', '', $image), '/');
    }
}
