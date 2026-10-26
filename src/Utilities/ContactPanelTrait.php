<?php

namespace App\Utilities;

use App\Entity\Member;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

/**
 * New message or stay request opened in the "contact a member" side panel (?embed=1):
 * when the member can't write (limit reached, no about me...), the panel shows why
 * instead of loading the page the full site would redirect to.
 */
trait ContactPanelTrait
{
    /**
     * $response is what the full site does (a redirect with a flash message). In the
     * panel the flash messages are taken out and shown in the panel itself.
     */
    protected function notAllowedInPanel(Request $request, Member $member, string $kind, Response $response): Response
    {
        if (!$request->query->getBoolean('embed')) {
            return $response;
        }

        $messages = [];
        $session = $request->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            foreach ($session->getFlashBag()->all() as $list) {
                foreach ($list as $message) {
                    $messages[] = $message;
                }
            }
        }

        return $this->render('conversations/embed_blocked.html.twig', [
            'kind' => $kind,
            'member' => $member,
            'messages' => $messages,
        ]);
    }
}
