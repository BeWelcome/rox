<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Model\BaseRequestModel;
use App\Model\ConversationModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;

abstract class BaseRequestAndInvitationController extends AbstractController
{
    use TranslatorTrait;
    use TranslatedFlashTrait;

    protected BaseRequestModel $model;
    protected ConversationModel $conversationModel;

    public function __construct(BaseRequestModel $model)
    {
        $this->model = $model;
    }

    abstract protected function addExpiredFlash(Member $receiver);

    protected function getMessageClone(Message $message): Message
    {
        // copy only the bare minimum needed
        $newMessage = new Message();
        $newMessage->setSubject($message->getSubject());
        $newMessage->setRequest($message->getRequest());
        $newMessage->setMessage('');
        $newMessage->setInitiator($message->getInitiator());

        return $newMessage;
    }

    protected function getMessageAndRequestClone(Message $message): Message
    {
        // copy only the bare minimum needed
        $newMessage = new Message();
        $newMessage->setSubject($message->getSubject());
        $newRequest = clone $message->getRequest();
        $newMessage->setRequest($newRequest);
        $newMessage->setMessage('');
        $newMessage->setInitiator($message->getInitiator());

        return $newMessage;
    }

    protected function persistFinalRequest(
        Form $requestForm,
        $currentRequest,
        Member $sender,
        Member $receiver
    ): Message {
        $data = $requestForm->getData();
        $em = $this->getDoctrine()->getManager();
        $clickedButton = $requestForm->getClickedButton()->getName();

        // handle changes in request and subject
        $newRequest = $this->model->getFinalRequest($sender, $receiver, $currentRequest, $data, $clickedButton);
        $em->persist($newRequest);
        $em->flush();

        return $newRequest;
    }

    protected function getMessageFromData($data, $member, $host): Message
    {
        /** @var Message $hostingRequest */
        $hostingRequest = $data;
        $hostingRequest->setSender($member);
        $hostingRequest->setReceiver($host);
        $hostingRequest->setFirstRead(null);
        $hostingRequest->setStatus('Sent');
        $hostingRequest->setFolder('Normal');
        $hostingRequest->setCreated(new DateTime());

        return $hostingRequest;
    }

    protected function getSubjectForReply(Message $newRequest): string
    {
        $subject = $newRequest->getSubject()->getSubject();
        if ('Re:' !== substr($subject, 0, 3)) {
            $subject = 'Re: ' . $subject;
        }

        return $this->adjustSubject($newRequest->getRequest()->getStatus(), $subject);
    }

    private function adjustSubject(int $status, string $subject): string
    {
        switch ($status) {
            case HostingRequest::REQUEST_DECLINED:
                $suffix = 'email.suffix.declined';
                break;
            case HostingRequest::REQUEST_CANCELLED:
                $suffix = 'email.suffix.cancelled';
                break;
            case HostingRequest::REQUEST_ACCEPTED:
                $suffix = 'email.suffix.accepted';
                break;
            case HostingRequest::REQUEST_TENTATIVELY_ACCEPTED:
                $suffix = 'email.suffix.maybe';
                break;
            default:
                $suffix = '';
        }

        if (!empty($suffix)) {
            $suffix = $this->getTranslator()->trans($suffix);
            if (false === strpos($suffix, $subject)) {
                $subject .= $suffix;
            }
        }

        return $subject;
    }
}
