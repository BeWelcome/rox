<?php

namespace App\Service;

use App\Entity\BrowserPushSubscription;

interface PushGatewayInterface
{
    public function send(BrowserPushSubscription $subscription, BrowserNotificationMessage $message): PushSendReport;
}
