<?php

namespace App\Model\MockupProvider;

use App\Entity\HostingRequest;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use App\Form\ReportSpamType;
use Symfony\Component\Form\FormFactoryInterface;

class InvitationMockups implements MockupProviderInterface
{
    private const array MOCKUPS = [
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
    private readonly InvitationUtility $invitationUtility;

    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
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
     * @SuppressWarnings("PHPMD")
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
        return match ($parameters['name']) {
            'intial invitation (host)' => $this->getVariablesForInitialInvitation($parameters),
            'invitation reply (guest)' => $this->getVariablesForReplyGuest($parameters),
            'invitation reply (host)' => $this->getVariablesForReplyHost($parameters),
            'view invitation (guest)' => $this->getVariablesForViewGuest($parameters),
            'view invitation (host)' => $this->getVariablesForViewHost($parameters),
            default => [],
        };
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

        $form = $this->formFactory->create(InvitationGuest::class, $thread[4]);

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
        $thread = $this->invitationUtility->getThread($host, $guest, $leg, $parameters['status'], 4);

        $form = $this->formFactory->create(InvitationHost::class, $thread[4]);

        return [
            'leg' => $leg,
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'invitation' => $thread[0],
            'form' => $form->createView(),
        ];
    }

    private function getVariablesForViewGuest(array $parameters): array
    {
        $host = $parameters['admin'];
        $guest = $parameters['user'];

        $leg = $this->invitationUtility->getLeg($parameters);
        $thread = $this->invitationUtility->getThread($host, $guest, $leg, $parameters['status'], 4);
        $form = $this->formFactory->create(ReportSpamType::class);

        return [
            'leg' => $leg,
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
        $host = $parameters['user'];
        $guest = $parameters['admin'];

        $leg = $this->invitationUtility->getLeg($parameters);
        $thread = $this->invitationUtility->getThread($host, $guest, $leg, $parameters['status'], 4);
        $form = $this->formFactory->create(ReportSpamType::class);

        return [
            'leg' => $leg,
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'form' => $form->createView(),
            'is_spam' => false,
            'show_deleted' => false,
        ];
    }
}
