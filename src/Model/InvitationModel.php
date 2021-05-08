<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subtrip;
use App\Service\Mailer;

class InvitationModel
{
    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * The requestChanged parameter triggers a PHPMD warning which is out of place in this case.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param mixed $subject
     * @param mixed $template
     * @param mixed $requestChanged
     */
    public function sendInvitationNotification(
        Member $sender,
        Member $receiver,
        Member $host,
        Message $request,
        $subject,
        string $template,
        bool $requestChanged,
        ?Subtrip $leg
    ): bool {
        // Send mail notification
        $this->mailer->sendMessageNotificationEmail($sender, $receiver, $template, [
            'host' => $host,
            'subject' => $subject,
            'message' => $request,
            'request' => $request->getRequest(),
            'changed' => $requestChanged,
            'leg' => $leg,
        ]);

        return true;
    }
}
