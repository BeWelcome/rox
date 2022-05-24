<?php

namespace App\Model;

use App\Entity\Message;
use DateTime;

class InvitationModel extends AbstractRequestModel
{
    /**
     * An invitation expires on the day it starts.
     */
    public function hasExpired(Message $message): bool
    {
        $today = new DateTime('today');
        $arrival = $message->getRequest()->getArrival();

        return $today > $arrival;
    }
}
