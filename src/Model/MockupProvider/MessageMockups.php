<?php

namespace App\Model\MockupProvider;

use App\Entity\Message;
use DateTime;

class MessageMockups implements MockupProviderInterface
{
    use MockupEntities;

    private const array MOCKUPS = [
        'message (email)' => [
            'type' => 'email',
            'template' => 'emails/message.html.twig',
        ],
    ];

    public function getFeature(): string
    {
        return 'messages';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }

    public function getMockupVariables(array $parameters): array
    {
        $message = self::withId(new Message(), 1)
            ->setMessage('Message text')
            ->setSender($parameters['user'])
            ->setReceiver($parameters['admin'])
            ->setCreated(new DateTime());

        return [
            'sender' => $parameters['user'],
            'receiver' => $parameters['admin'],
            'message' => $message,
        ];
    }
}
