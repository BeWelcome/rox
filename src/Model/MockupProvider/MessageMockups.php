<?php

namespace App\Model\MockupProvider;

use App\Entity\Message;
use ReflectionProperty;

class MessageMockups extends AbstractMockups
{
    protected const string FEATURE = 'messages';

    protected const array MOCKUPS = [
        'message (email)' => [
            'type' => 'email',
            'template' => 'emails/message.html.twig',
        ],
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [];
    }

    public function getMockupVariables(array $parameters): array
    {
        $message = (new Message())
            ->setMessage('Message text')
            ->setSender($parameters['user'])
            ->setReceiver($parameters['admin'])
        ;

        $reflectionProperty = new ReflectionProperty(Message::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, 1);

        return [
            'sender' => $parameters['user'],
            'receiver' => $parameters['admin'],
            'message' => $message,
        ];
    }
}
