<?php

namespace App\Model\MockupProvider;

use App\Entity\Message;
use Mockery;

class MessageMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
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
        $mockMessage = Mockery::mock(Message::class, [
            'getId' => 1,
            'getMessage' => 'Message text',
        ]);
        $mockMessage->shouldReceive('getSender')->andReturn($parameters['user']);
        $mockMessage->shouldReceive('getReceiver')->andReturn($parameters['admin']);

        return [
            'sender' => $parameters['user'],
            'receiver' => $parameters['admin'],
            'message' => $mockMessage,
        ];
    }
}
