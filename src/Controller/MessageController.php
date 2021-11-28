<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Form\MessageToMemberType;
use App\Model\ConversationModel;
use App\Service\Mailer;
use App\Utilities\ConversationThread;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatedFlashTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MessageController.
 *
 * Ignore complexity warning. \todo fix this.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MessageController extends AbstractController
{
    use TranslatedFlashTrait;

    private Mailer $mailer;
    private ConversationModel $conversationModel;
    private ConversationThread $conversationThread;

    public function __construct(
        Mailer $mailer,
        ConversationModel $conversationModel,
        ConversationThread $conversationThread
    ) {
        $this->mailer = $mailer;
        $this->conversationModel = $conversationModel;
        $this->conversationThread = $conversationThread;
    }

    /**
     * @Route("/new/message/{username}", name="message_new")
     *
     * @throws Exception
     */
    public function newMessage(Request $request, Member $receiver): Response
    {
        /** @var Member $sender */
        $sender = $this->getUser();
        if (!$receiver->isBrowseable()) {
            $this->addTranslatedFlash('error', 'flash.member.invalid');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        if (
            $this->conversationModel->hasMessageLimitExceeded(
                $sender,
                $this->getParameter('new_members_messages_per_hour'),
                $this->getParameter('new_members_messages_per_day')
            )
        ) {
            $this->addTranslatedFlash('error', 'flash.message.limit');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $messageForm = $this->createForm(MessageToMemberType::class);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $subject = $messageForm->get('subject')->get('subject')->getData();
            $body = $messageForm->get('message')->getData();

            $this->addMessageAndSendNotification($sender, $receiver, null, $subject, $body);
            $this->addTranslatedFlash('success', 'flash.message.sent');

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render('message/message.html.twig', [
            'receiver' => $receiver,
            'form' => $messageForm->createView(),
        ]);
    }

    public function reply(Request $request, Message $message): Response
    {
        /** @var Member $sender */
        $sender = $this->getUser();

        $thread = $this->conversationThread->getThread($message);
        if ($message !== $thread[0]) {
            $message = $thread[\count($thread) - 1];
        }

        $receiver = ($message->getReceiver() === $sender) ? $message->getSender() : $message->getReceiver();

        $replyMessage = new Message();
        $replyMessage->setSubject($message->getSubject());

        $replyForm = $this->createForm(MessageToMemberType::class, $replyMessage);
        $replyForm->handleRequest($request);

        if ($replyForm->isSubmitted() && $replyForm->isValid()) {
            /** @var Message $data */
            $data = $replyForm->getData();
            $replySubject = $data->getSubject()->getSubject();
            if ('Re:' !== substr($replySubject, 0, 3)) {
                $replySubject = 'Re: ' . $replySubject;
            }

            $messageText = $data->getMessage();
            $message = $this->addMessageAndSendNotification($sender, $receiver, $message, $replySubject, $messageText);
            $this->addTranslatedFlash('success', 'flash.reply.sent');

            return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
        }

        return $this->render('message/reply.html.twig', [
            'form' => $replyForm->createView(),
            'receiver' => $receiver,
            'current' => $message,
            'thread' => $thread,
        ]);
    }

    /**
     * Creates a new message and stores it into the database afterwards sends a notification to the receiver
     * Only used for messages therefore request is set to null!
     */
    private function addMessageAndSendNotification(
        Member $sender,
        Member $receiver,
        ?Message $parent,
        string $subjectText,
        string $body
    ): Message {
        $em = $this->getDoctrine()->getManager();
        $message = new Message();
        if (null === $parent) {
            $subject = new Subject();
            $subject->setSubject($subjectText);
            $em->persist($subject);
            $em->flush();
        } else {
            $subject = $parent->getSubject();
        }

        $message->setSubject($subject);
        $message->setSender($sender);
        $message->setRequest(null);
        $message->setParent($parent);
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setMessage($body);
        $message->setStatus('Sent');
        $em->persist($message);
        $em->flush();

        $this->mailer->sendMessageNotificationEmail($sender, $receiver, 'message', [
            'message' => $message,
            'subject' => $subjectText,
            'body' => $body,
        ]);

        return $message;
    }
}
