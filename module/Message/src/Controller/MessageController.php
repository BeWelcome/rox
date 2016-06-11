<?php

namespace Rox\Message\Controller;

use Illuminate\Database\Eloquent\Builder;
use Rox\Member\Repository\MemberRepositoryInterface;
use Rox\Message\Model\Message;
use Rox\Message\Repository\MessageRepositoryInterface;
use Rox\Message\Service\MessageServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageController
{
    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var MessageServiceInterface
     */
    protected $messageService;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        MessageServiceInterface $messageService,
        MemberRepositoryInterface $memberRepository,
        EngineInterface $engine,
        SessionInterface $session
    ) {
        $this->messageRepository = $messageRepository;
        $this->messageService = $messageService;
        $this->memberRepository = $memberRepository;
        $this->engine = $engine;
        $this->session = $session;
    }

    public function update(Request $request)
    {
        $modifyAction = $request->request->get('modify');
        $messageIds = $request->request->get('message_id');

        $member = $this->memberRepository->getById($this->session->get('IdMember'));

        $message = new Message();

        $messages = $message->newQuery()->findMany($messageIds);

        foreach ($messages as $message) {
            if ($modifyAction === 'delete') {
                $this->messageService->deleteMessage($message, $member);
            } elseif ($modifyAction === 'markasspam') {
                $this->messageService->moveMessage($message, Message::FOLDER_SPAM);
            } elseif ($modifyAction === 'nospam') {
                $this->messageService->moveMessage($message, Message::FOLDER_INBOX);
            //} else {
                //throw new \InvalidArgumentException('Invalid message state.');
            }
        }

        return new RedirectResponse($request->getUri());
    }

    public function with(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        //$sort = $request->query->get('sort', 'date');
        //$dir = $request->query->get('dir', 'DESC');
        $otherUsername = $request->attributes->get('username');

        $otherMember = $this->memberRepository->getByUsername($otherUsername);

        $member = $this->memberRepository->getById($this->session->get('IdMember'));

        $message = new Message();

        $q = $message->newQuery();

        // Eager load each sender for each message
        $q->with('sender');

        $q->where(function (Builder $builder) use ($member, $otherMember) {
            $builder->where(function (Builder $builder) use ($member, $otherMember) {
                $builder->where('IdSender', $otherMember->id);
                $builder->where('IdReceiver', $member->id);
                $builder->where('Status', 'Sent');
            });

            $builder->orWhere(function (Builder $builder) use ($member, $otherMember) {
                $builder->where('IdSender', $member->id);
                $builder->where('IdReceiver', $otherMember->id);
            });
        });

        $q->where('DeleteRequest', 'NOT LIKE', '%receiverdeleted%');

        $q->orderByRaw('IF(messages.created > messages.DateSent, messages.created, messages.DateSent) DESC');

        $q->forPage($page, $limit);

        $count = $q->getQuery()->getCountForPagination();

        $messages = $q->get();

        $content = $this->engine->render('@message/message/index.html.twig', [
            'messages' => $messages,
            'folder' => '',
            'filter' => $request->query->all(),
            'page' => $page,
            'pages' => ceil($count / $limit),
        ]);
        return new Response($content);
    }

    public function compose(Request $request)
    {
        $receiverUsername = $request->attributes->get('username');

        $receiver = $this->memberRepository->getByUsername($receiverUsername);

        $content = $this->engine->render('@message/message/compose.html.twig', [
            'receiver' => $receiver,
        ]);

        return new Response($content);
    }

    public function index(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'date');
        $sortDir = $request->query->get('dir', 'desc');
        $filter = $request->attributes->get('filter', 'inbox');

        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException();
        }

        $memberId = $this->session->get('IdMember');
        $member = $this->memberRepository->getById($memberId);

        $q = $this->messageService->getFilteredMessages($member, $filter, $sort, $sortDir);

        // Eager load each sender for each message
        $q->with('sender');

        $q->getQuery()->forPage($page, $limit);

        $count = $q->getQuery()->getCountForPagination();

        $messages = $q->get();

        $content = $this->engine->render('@message/message/index.html.twig', [
            'messages' => $messages,
            'folder' => $filter,
            'filter' => $request->query->all(),
            'page' => $page,
            'pages' => ceil($count / $limit),
        ]);

        return new Response($content);
    }

    public function view(Request $request)
    {
        $messageId = $request->attributes->get('id');

        $member = $this->memberRepository->getById($this->session->get('IdMember'));

        $message = $this->messageRepository->getById($messageId);

        if ($message->isUnread() && $member->id === $message->receiver->id) {
            // Only mark as read when the receiver reads the message, not when
            // the message is presented to the Sender with url /messages/77/sent
            $this->messageService->markMessage($message, Message::STATE_READ);
        }

        $content = $this->engine->render('@message/message/view.html.twig', [
            'message' => $message,
        ]);

        return new Response($content);
    }
}
