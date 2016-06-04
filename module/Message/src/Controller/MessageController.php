<?php

namespace Rox\Message\Controller;

use Rox\Message\Repository\MessageRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class MessageController
{
    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var EngineInterface
     */
    protected $engine;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        EngineInterface $engine
    ) {
        $this->messageRepository = $messageRepository;
        $this->engine = $engine;
    }

    public function view(Request $request)
    {
        $messageId = $request->attributes->get('id');

        $message = $this->messageRepository->getById($messageId);

        $content = $this->engine->render('@message/message/view.html.twig', [
            'message' => $message,
        ]);

        return new Response($content);
    }
}
