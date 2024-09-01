<?php

namespace App\Model;

use App\Entity\Message;
use DateTime;

class HostingRequestModel extends BaseRequestModel
{
    public function formatRequest(Message &$request): bool
    {
        $messageText = $request->getMessage();
        $found = preg_match("/@|at|.at.|-at-/i", $messageText);

        if ($found !== 0) {
            $request->setMessage($messageText . '<p>Potential spam. Please report if necessary.</p>');
        }

        return $found === 0;
    }
}
