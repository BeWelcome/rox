<?php

namespace App\Model\MockupProvider;

use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use Carbon\Carbon;
use DateTime;
use Mockery;
use Symfony\Component\Form\FormFactoryInterface;

class MessageMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'message' => [
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

    public function getMockupParameter(): array
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
