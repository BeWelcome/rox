<?php

namespace App\Model\MockupProvider;

use App\Entity\HostingRequest;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
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
    private InvitationUtility $invitationUtility;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        $this->invitationUtility = new InvitationUtility();
    }

    public function getFeature(): string
    {
        return 'invitations';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
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

        $leg = $this->invitationUtility->getLeg($host);

        return [
            'leg' => $leg,
            'form' => $form->createView(),
        ];
    }

    private function getVariablesForReplyGuest(array $parameters): array
    {
        $host = $parameters['user'];
        $guest = $parameters['admin'];

        $leg = $this->invitationUtility->getLeg($parameters);
        $thread = $this->invitationUtility->getThread($host, $guest, $leg, $parameters['status'], 4);

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

        $leg = $this->invitationUtility->getLeg($parameters);
        $thread = $this->invitationUtility->getThread($host, $guest, $leg, $parameters['status'], 3);

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

        $leg = $this->invitationUtility->getLeg($parameters);
        $thread = $this->invitationUtility->getThread($host, $guest, $leg, $parameters['status'], 3);

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

        $leg = $this->invitationUtility->getLeg($parameters);
        $thread = $this->invitationUtility->getThread($host, $guest, $leg, $parameters['status'], 4);

        return [
            'leg' => $leg,
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'is_spam' => false,
            'show_deleted' => false,
        ];
    }
}
