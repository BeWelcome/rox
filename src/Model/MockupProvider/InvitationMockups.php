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
use Mockery\MockInterface;
use Symfony\Component\Form\FormFactoryInterface;

class InvitationMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'intial invitation (host)' => [
            'type' => 'page',
            'template' => 'invitation/invite.html.twig',
        ],
        'invitation reply (guest)' => [
            'type' => 'page',
            'with_parameters' => true,
            'url' => '/conversation/{id}/reply',
            'template' => 'invitation/reply_from_guest.html.twig',
            'parameters' => [
                'class' => self::class,
                'function' => 'getMockupParameters',
            ],
        ],
        'invitation reply (host)' => [
            'type' => 'page',
            'with_parameters' => true,
            'url' => '/conversation/{id}/reply',
            'template' => 'invitation/reply_from_host.html.twig',
            'parameters' => [
                'getStatus' => [
                    'open' => HostingRequest::REQUEST_OPEN,
                    'cancelled' => HostingRequest::REQUEST_CANCELLED,
                    'declined' => HostingRequest::REQUEST_DECLINED,
                    'tentatively' => HostingRequest::REQUEST_TENTATIVELY_ACCEPTED,
                    'accepted' => HostingRequest::REQUEST_ACCEPTED,
                ],
            ],
        ],
        'view invitation (guest)' => [
            'type' => 'page',
            'with_parameters' => true,
            'url' => '/conversation/{id}',
            'template' => 'invitation/view.html.twig',
            'parameters' => [
                'class' => self::class,
                'function' => 'getMockupParameters',
            ],
        ],
        'view invitation (host)' => [
            'type' => 'page',
            'with_parameters' => true,
            'url' => '/conversation/{id}',
            'template' => 'invitation/view.html.twig',
            'parameters' => [
                'class' => self::class,
                'function' => 'getMockupParameters',
            ],
        ],
    ];

    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getFeature(): string
    {
        return 'invitations';
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
        switch ($parameters['name'])
        {
            case 'intial invitation (host)':
                return $this->getVariablesForInitialInvitation($parameters);
            case 'invitation reply (guest)':
                return $this->getVariablesForReplyGuest($parameters);
            case 'invitation reply (host)':
                return $this->getVariablesForReplyHost($parameters);
            case 'view invitation (guest)':
                return $this->getVariablesForViewGuest($parameters);
            case 'view invitation (host)':
                return $this->getVariablesForViewHost($parameters);
            default:
                return [];
        }
    }

    private function getVariablesForInitialInvitation(array $parameters): array
    {
        $host = $parameters['user'];
        $form = $this->formFactory->create(InvitationType::class);

        $leg = $this->getLeg($host);

        return [
            'leg' => $leg,
            'form' => $form->createView(),
        ];
    }

    private function getVariablesForReplyGuest(array $parameters): array
    {
        $host = $parameters['user'];
        $guest = $parameters['admin'];

        $leg = $this->getLeg($parameters);
        $thread = $this->getThread($host, $guest, $leg, $parameters['status'], 4);

        $form = $this->formFactory->create(InvitationGuest::class, $thread[1]);

        return [
            'leg' => $leg,
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

        $leg = $this->getLeg($parameters);
        $thread = $this->getThread($host, $guest, $leg, $parameters['status'], 3);

        $form = $this->formFactory->create(InvitationHost::class, $thread[1]);

        return [
            'leg' => $leg,
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'form' => $form->createView(),
        ];
    }

    private function getVariablesForViewGuest(array $parameters): array
    {
        $host = $parameters['admin'];
        $guest = $parameters['user'];

        $leg = $this->getLeg($parameters);
        $thread = $this->getThread($host, $guest, $leg, $parameters['status'], 3);

        return [
            'leg' => $leg,
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'is_spam' => false,
            'show_deleted' => false,
        ];
    }

    private function getVariablesForViewHost(array $parameters): array
    {
        $host = $parameters['user'];
        $guest = $parameters['admin'];

        $leg = $this->getLeg($parameters);
        $thread = $this->getThread($host, $guest, $leg, $parameters['status'], 4);

        return [
            'leg' => $leg,
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'is_spam' => false,
            'show_deleted' => false,
        ];
    }

    private function getThread(Member $host, Member $guest, Subtrip $leg, int $status, int $replies): array
    {
        $subject = Mockery::mock(Subject::class, [
            'getSubject' => 'Subject'
        ]);
        $request = Mockery::mock(HostingRequest::class, [
            'getId' => 1,
            'getArrival' => new Carbon(),
            'getDeparture' => new Carbon(),
            'getNumberOfTravellers' => 2,
            'getFlexible' => true,
            'getStatus' => $status,
            'getInviteForLeg' => $leg,
        ]);

        $parent = Mockery::mock(Message::class, [
            'getId' => 1,
            'getMessage' => 'Initial invitation',
        ]);
        $parent->shouldReceive('getSubject')->andReturn($subject);
        $parent->shouldReceive('getCreated')->andReturn(new Carbon());
        $parent->shouldReceive('getSender')->andReturn($host);
        $parent->shouldReceive('getInitiator')->andReturn($host);
        $parent->shouldReceive('getReceiver')->andReturn($guest);
        $parent->shouldReceive('getRequest')->andReturn($request);
        $parent->shouldReceive('isDeletedByMember')->andReturn(false);
        $parent->shouldReceive('isPurgedByMember')->andReturn(false);

        $thread = [];
        $thread[] = $parent;
        $lastMessage = $parent;
        for ($i = 1;$i < $replies; $i++) {
            $lastMessage = $this->getReply($lastMessage, $subject, $request, $guest, $host);
            $temp = $host; $host = $guest; $guest = $temp;
            $thread[] = $lastMessage;
        }

        return array_reverse($thread);
    }

    private function getLeg($host): Subtrip
    {
        $trip = Mockery::mock(Trip::class, [
            'getId' => 1,
            'getCreator' => $host,
            'getSummary' => 'Mocking Bird',
            'getDescription' => 'Mocking description',
            'getCountOfTravellers' => 2,
            'getAdditionalInfo' => TripAdditionalInfoType::NONE,
            'getCreated' => new DateTime(),
        ]);
        $location = Mockery::mock(Location::class, [
            'getId' => 1,
            'getName' => 'Mock',
        ]);
        $leg = Mockery::mock(SubTrip::class, [
            'getId' => 1,
            'getArrival' => Carbon::instance(new DateTime('2021-02-22')),
            'getDeparture' => Carbon::instance(new DateTime('2021-02-24')),
            'getOptions' => [SubtripOptionsType::MEET_LOCALS],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $host,
        ]);

        return $leg;
    }

    private function getReply(
        Message $parent,
        Subject $subject,
        HostingRequest $request,
        Member $guest,
        Member $host
    ): Message {
        $reply = Mockery::mock(Message::class, [
            'getId' => 1,
            'getMessage' => 'Reply',
            'getParent' => $parent,
        ]);

        $reply->shouldReceive('getSubject')->andReturn($subject);
        $reply->shouldReceive('getCreated')->andReturn(new Carbon());
        $reply->shouldReceive('getSender')->andReturn($guest);
        $reply->shouldReceive('getInitiator')->andReturn($parent->getInitiator());
        $reply->shouldReceive('getReceiver')->andReturn($host);
        $reply->shouldReceive('getRequest')->andReturn($request);
        $reply->shouldReceive('isDeletedByMember')->andReturn(false);
        $reply->shouldReceive('isPurgedByMember')->andReturn(false);

        return $reply;
    }
}
