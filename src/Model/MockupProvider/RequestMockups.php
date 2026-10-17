<?php

namespace App\Model\MockupProvider;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Form\HostingRequestGuest;
use App\Form\HostingRequestHost;
use App\Form\ReportSpamType;
use Symfony\Component\Form\FormFactoryInterface;

class RequestMockups extends AbstractMockups
{
    protected const string FEATURE = 'requests';

    protected const array MOCKUPS = [
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

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly InvitationUtility $invitationUtility,
    ) {
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
        switch ($parameters['name']) {
            case 'intial request (guest)':
                return $this->getVariablesForInitialRequest($parameters);
            case 'request reply (guest)':
                return $this->getVariablesForReplyGuest($parameters);
            case 'request reply (host)':
                return $this->getVariablesForReplyHost($parameters);
            case 'view request (guest)':
                return $this->getVariablesForViewGuest($parameters);
            case 'view request (host)':
                return $this->getVariablesForViewHost($parameters);
            default:
                return [];
        }
    }

    public function getThread(Member $host, Member $guest, int $status, int $replies): array
    {
        $thread = $this->invitationUtility->getThread($host, $guest, null, $status, $replies);

        return array_reverse($thread);
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
}
