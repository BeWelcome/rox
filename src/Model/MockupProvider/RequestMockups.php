<?php

namespace App\Model\MockupProvider;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Form\HostingRequestGuest;
use App\Form\HostingRequestHost;
use App\Form\ReportSpamType;
use App\Service\InboxFolders;
use Carbon\Carbon;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestMockups implements MockupProviderInterface
{
    use MockupEntities;

    private const array MOCKUPS = [
        'request (mail)' => [
            'type' => 'email',
            'with_parameters' => true,
            'template' => 'emails/request.html.twig',
            'description' => 'Mail to the host about a new hosting request.',
        ],
        'reply from guest (mail)' => [
            'type' => 'email',
            'with_parameters' => true,
            'template' => 'emails/reply_from_guest.html.twig',
            'description' => 'Mail to the host when the guest answered, here with changed details.',
        ],
        'reply from host (mail)' => [
            'type' => 'email',
            'with_parameters' => true,
            'template' => 'emails/reply_from_host.html.twig',
            'description' => 'Mail to the guest when the host answered.',
        ],
        'decline from host (mail)' => [
            'type' => 'email',
            'template' => 'emails/decline_from_host.html.twig',
            'description' => 'Mail to the guest when the host declined.',
        ],
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
        private readonly InboxFolders $inboxFolders,
        private readonly RequestStack $requestStack,
    ) {
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
            'request (mail)' => $this->getVariablesForRequestMail($parameters),
            'reply from guest (mail)' => $this->getVariablesForRequestMail($parameters, changed: true),
            'reply from host (mail)' => $this->getVariablesForRequestMail($parameters, fromHost: true),
            'decline from host (mail)' => $this->getVariablesForRequestMail(
                ['status' => HostingRequest::REQUEST_DECLINED] + $parameters,
                fromHost: true,
            ),
            'intial request (guest)' => $this->getVariablesForInitialRequest($parameters),
            'request reply (guest)' => $this->getVariablesForReplyGuest($parameters),
            'request reply (host)' => $this->getVariablesForReplyHost($parameters),
            'view request (guest)' => $this->getVariablesForViewGuest($parameters),
            'view request (host)' => $this->getVariablesForViewHost($parameters),
            default => [],
        };
    }

    /**
     * The mails about a request: from the guest (the translator) to the host (bwadmin), or the host's answer.
     */
    private function getVariablesForRequestMail(array $parameters, bool $fromHost = false, bool $changed = false): array
    {
        $guest = $parameters['user'];
        $host = $parameters['admin'];
        [$reply, $request] = $this->getThread($guest, $host, (int) ($parameters['status'] ?? HostingRequest::REQUEST_OPEN), 2);
        $request->setMessage('<p>Hi! I\'ll be passing through your town on my way south. Could you host me for two nights? I\'m happy to cook something from home.</p>');
        $reply->setMessage('<p>You\'re welcome! I live a ten-minute walk from the station; I\'ll send you the address closer to the date.</p>');
        $message = $fromHost ? $reply : $request;

        return [
            'sender' => $fromHost ? $host : $guest,
            'receiver' => $fromHost ? $guest : $host,
            'host' => $host,
            'message' => $message,
            'request' => $message->getRequest(),
            'changed' => $changed,
            'subject' => 'Subject',
        ];
    }

    private function getVariablesForInitialRequest(array $parameters): array
    {
        $form = $this->formFactory->create(HostingRequestGuest::class);

        // the inbox beside the form, as RequestController::newHostingRequest() shows it
        return [
            'host' => $parameters['admin'],
            'guest' => $parameters['user'],
            'form' => $form->createView(),
        ] + $this->inboxFolders->getContext($this->requestStack->getCurrentRequest(), $parameters['user'], 'requests');
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
        ] + $this->inboxFolders->getContext($this->requestStack->getCurrentRequest(), $parameters['user'], 'requests');
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
        ] + $this->inboxFolders->getContext($this->requestStack->getCurrentRequest(), $parameters['user'], 'requests');
    }

    /**
     * The request and a reply, the newest first.
     *
     * @return list<Message>
     */
    private function getThread(Member $host, Member $guest, int $status, int $numberOfNights): array
    {
        $subject = new Subject()->setSubject('Subject');
        $request = self::withId(new HostingRequest(), 1)
            ->setArrival(new Carbon())
            // the number of nights differs between the previews
            ->setDeparture(new Carbon()->addDays($numberOfNights))
            ->setNumberOfTravellers(2)
            ->setFlexible(true)
            ->setStatus($status);

        $parent = self::withId(new Message(), 1)
            ->setMessage('Initial request')
            ->setSubject($subject)
            ->setRequest($request)
            ->setSender($host)
            ->setReceiver($guest)
            ->setInitiator($host)
            ->setCreated(new Carbon());
        $reply = self::withId(new Message(), 2)
            ->setMessage('Reply')
            ->setParent($parent)
            ->setSubject($subject)
            ->setRequest($request)
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($host)
            ->setCreated(new Carbon());

        return [$reply, $parent];
    }
}
