<?php

namespace App\Model;

use App\Entity\Message;

class HostingRequestModel extends BaseRequestModel
{
    public function formatRequest(Message &$request): bool
    {
        $messageText = $request->getMessage();
        $found = preg_match('/@|at|.at.|-at-/i', $messageText);

        if (0 !== $found) {
            $request->setMessage($messageText . '<p>Potential spam. Please report if necessary.</p>');
        }

        return 0 === $found;
    }
}
