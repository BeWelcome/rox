<?php

namespace App\Model;

use App\Entity\Message;
use DateTime;

class HostingRequestModel extends AbstractRequestModel
{
    /**
     * A hosting request expires on the last day of the stay (to allow for easy communication during the stay).
     */
    public function hasExpired(Message $message): bool
    {
        $today = new DateTime('today');
        $departure = $message->getRequest()->getDeparture();

        return !($today <= $departure);
    }
}
