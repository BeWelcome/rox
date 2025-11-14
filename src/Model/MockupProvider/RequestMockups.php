<?php

namespace App\Model\MockupProvider;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Form\HostingRequestGuest;
use App\Form\HostingRequestHost;
use App\Form\ReportSpamType;
use Carbon\Carbon;
use Mockery;
use Symfony\Component\Form\FormFactoryInterface;

class RequestMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
        'intial request (guest)' => [
            'type' => 'page',
            'template' => 'request/request.html.twig',
        ],
        'request reply (host)' => [
            'type' => 'page',
            'with_parameters' => true,
            'url' => '/conversation/{id}/reply',
            'template' => 'request/reply_from_host.html.twig',
        ],
        'request reply (guest)' => [
            'type' => 'page',
            'with_parameters' => true,
            'url' => '/conversation/{id}/reply',
            'template' => 'request/reply_from_guest.html.twig',
        ],
        'view request (guest)' => [
            'type' => 'page',
            'with_parameters' => true,
            'url' => '/conversation/{id}',
            'template' => 'request/view.html.twig',
        ],
        'view request (host)' => [
            'type' => 'page',
            'with_parameters' => true,
            'url' => '/conversation/{id}',
            'template' => 'request/view.html.twig',
        ],
    ];

    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
    }

    public function getFeature(): string
    {
        return 'requests';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return [
            'status' => [
                'open' => HostingRequest::REQUEST_OPEN,
                'cancelled' => HostingRequest::REQUEST_CANCELLED,
                'declined' => HostingRequest::REQUEST_DECLINED,
                'tentatively' => HostingRequest::REQUEST_TENTATIVELY_ACCEPTED,
                'accepted' => HostingRequest::REQUEST_ACCEPTED,
            ],
        ];
    }

    public function getMockupVariables(array $parameters): array
    {
        return match ($parameters['name']) {
            'intial request (guest)' => $this->getVariablesForInitialRequest($parameters),
            'request reply (guest)' => $this->getVariablesForReplyGuest($parameters),
            'request reply (host)' => $this->getVariablesForReplyHost($parameters),
            'view request (guest)' => $this->getVariablesForViewGuest($parameters),
            'view request (host)' => $this->getVariablesForViewHost($parameters),
            default => [],
        };
    }

    private function getVariablesForInitialRequest(array $parameters): array
    {
        $form = $this->formFactory->create(HostingRequestGuest::class);

        return [
            'host' => $parameters['admin'],
            'guest' => $parameters['user'],
            'form' => $form->createView(),
        ];
    }

    private function getVariablesForReplyGuest(array $parameters): array
    {
        $host = $parameters['admin'];
        $guest = $parameters['user'];

        $thread = $this->getThread($host, $guest, $parameters['status'], 1);

        $form = $this->formFactory->create(HostingRequestGuest::class, $thread[1]);

        return [
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'form' => $form->createView(),
        ];
    }

    private function getVariablesForReplyHost(array $parameters): array
    {
        $host = $parameters['user'];
        $guest = $parameters['admin'];

        $thread = $this->getThread($host, $guest, $parameters['status'], 2);

        $form = $this->formFactory->create(HostingRequestHost::class, $thread[1]);

        return [
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'form' => $form->createView(),
        ];
    }

    private function getVariablesForViewGuest(array $parameters): array
    {
        $host = $parameters['user'];
        $guest = $parameters['admin'];

        $thread = $this->getThread($host, $guest, $parameters['status'], 3);
        $form = $this->formFactory->create(ReportSpamType::class);

        return [
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'form' => $form->createView(),
            'is_spam' => false,
            'show_deleted' => false,
        ];
    }

    private function getVariablesForViewHost(array $parameters): array
    {
        $host = $parameters['admin'];
        $guest = $parameters['user'];

        $thread = $this->getThread($host, $guest, $parameters['status'], 4);

        $form = $this->formFactory->create(ReportSpamType::class);

        return [
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'form' => $form->createView(),
            'is_spam' => false,
            'show_deleted' => false,
        ];
    }

    private function getThread(Member $host, Member $guest, int $status, int $numberOfNights): array
    {
        $mockSubject = Mockery::mock(Subject::class, [
            'getSubject' => 'Subject',
        ]);
        $mockRequest = Mockery::mock(HostingRequest::class, [
            'getId' => 1,
            'getArrival' => new Carbon(),
            'getDeparture' => new Carbon()->addDays($numberOfNights), // use number of replies to change number of nights
            'getNumberOfTravellers' => 2,
            'getFlexible' => true,
            'getStatus' => $status,
            'getInviteForLeg' => null,
        ]);

        $mockMessageParent = Mockery::mock(Message::class, [
            'getId' => 1,
            'getMessage' => 'Initial request',
        ]);
        $mockMessageParent->shouldReceive('getSubject')->andReturn($mockSubject);
        $mockMessageParent->shouldReceive('getCreated')->andReturn(new Carbon());
        $mockMessageParent->shouldReceive('getSender')->andReturn($host);
        $mockMessageParent->shouldReceive('getInitiator')->andReturn($host);
        $mockMessageParent->shouldReceive('getReceiver')->andReturn($guest);
        $mockMessageParent->shouldReceive('getRequest')->andReturn($mockRequest);
        $mockMessageParent->shouldReceive('isDeletedByMember')->andReturn(false);
        $mockMessageParent->shouldReceive('isPurgedByMember')->andReturn(false);

        $mockMessageReply = Mockery::mock(Message::class, [
            'getId' => 1,
            'getMessage' => 'Reply',
            'getParent' => $mockMessageParent,
        ]);

        $mockMessageReply->shouldReceive('getSubject')->andReturn($mockSubject);
        $mockMessageReply->shouldReceive('getCreated')->andReturn(new Carbon());
        $mockMessageReply->shouldReceive('getSender')->andReturn($guest);
        $mockMessageReply->shouldReceive('getInitiator')->andReturn($host);
        $mockMessageReply->shouldReceive('getReceiver')->andReturn($host);
        $mockMessageReply->shouldReceive('getRequest')->andReturn($mockRequest);
        $mockMessageReply->shouldReceive('isDeletedByMember')->andReturn(false);
        $mockMessageReply->shouldReceive('isPurgedByMember')->andReturn(false);

        $mockThread = [$mockMessageReply, $mockMessageParent];

        return $mockThread;
    }
}
