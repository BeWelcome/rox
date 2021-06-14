<?php

namespace App\Model;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subtrip;
use App\Service\Mailer;
use DateTime;

class InvitationModel
{
    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function isInvitationExpired(HostingRequest $invitation): bool
    {
        $today = new DateTime('today');
        $arrival = $invitation->getArrival();
        if (null === $arrival) {
            // No departure date given assume an interval of two days max
            $arrival = (clone $today)->modify('+2days');
        }

        return !($today < $arrival);
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param mixed $subject
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
