<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Model\AbstractRequestModel;
use App\Model\ConversationModel;
use App\Utilities\TranslatedFlashTrait;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseRequestAndInvitationController extends AbstractController
{
    use TranslatedFlashTrait;

    protected AbstractRequestModel $model;
    protected ConversationModel $conversationModel;

    public function __construct(AbstractRequestModel $model)
    {
        $this->model = $model;
    }

    /**
     * Deals with replies to hosting requests.
     */
    public function reply(Request $request, Message $message): Response
    {
        // determine if guest or host reply to a request
        $guest = $message->getInitiator();
        $host = $message->getReceiver() === $guest ? $message->getSender() : $message->getReceiver();

        $member = $this->getUser();
        if ($member === $guest) {
            return $this->guestReply($request, $message, $guest, $host);
        }

        return $this->hostReply($request, $message, $guest, $host);
    }

    abstract protected function addExpiredFlash(Member $receiver);

    protected function getRequestClone(Message $hostingRequest): Message
    {
        // copy only the bare minimum needed
        $newRequest = new Message();
        $newRequest->setSubject($hostingRequest->getSubject());
        $newHostingRequest = clone $hostingRequest->getRequest();
        $newRequest->setRequest($newHostingRequest);
        $newRequest->setMessage('');

        return $newRequest;
    }

    protected function persistRequest(Form $requestForm, $currentRequest, Member $sender, Member $receiver): Message
    {
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

        if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
            $subject = $this->adjustSubject('(Cancelled)', $subject);
        }

        if (HostingRequest::REQUEST_DECLINED === $newRequest->getRequest()->getStatus()) {
            $subject = $this->adjustSubject('(Declined)', $subject);
        }

        if (HostingRequest::REQUEST_ACCEPTED === $newRequest->getRequest()->getStatus()) {
            $subject = $this->adjustSubject('(Accepted)', $subject);
        }

        if (HostingRequest::REQUEST_TENTATIVELY_ACCEPTED === $newRequest->getRequest()->getStatus()) {
            $subject = $this->adjustSubject('(Tentatively accepted)', $subject);
        }

        return $subject;
    }

    private function adjustSubject(string $suffix, string $subject): string
    {
        if (false === strpos($suffix, $subject)) {
            $subject .= $suffix;
        }

        return $subject;
    }
}
