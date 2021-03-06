<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Model\HostingRequestModel;
use App\Model\MessageModel;
use DateTime;
use Symfony\Component\Form\Form;

class BaseHostingRequestAndInvitationController extends BaseMessageController
{
    protected $requestModel;

    public function __construct(HostingRequestModel $requestModel, MessageModel $messageModel)
    {
        parent::__construct($messageModel);

        $this->requestModel = $requestModel;
    }

    protected function checkRequestExpired(Message $hostingRequest): bool
    {
        return $this->requestModel->isRequestExpired($hostingRequest->getRequest());
    }

    protected function addExpiredFlash(Member $receiver)
    {
        $this->addTranslatedFlash('notice', 'flash.request.expired', [
            '%link_start%' => '<a href="' . $this->generateUrl('message_new', [
                    'username' => $receiver->getUsername(),
                ]) . '" class="text-primary">',
            '%link_end%' => '</a>',
        ]);
    }

    protected function getRequestClone(Message $hostingRequest)
    {
        // copy only the bare minimum needed
        $newRequest = new Message();
        $newRequest->setSubject($hostingRequest->getSubject());
        $newHostingRequest = clone $hostingRequest->getRequest();
        $newRequest->setRequest($newHostingRequest);
        $newRequest->setMessage('');

        return $newRequest;
    }

    protected function persistRequest(Form $requestForm, $currentRequest, Member $sender, Member $receiver)
    {
        $data = $requestForm->getData();
        $em = $this->getDoctrine()->getManager();
        $clickedButton = $requestForm->getClickedButton()->getName();

        // handle changes in request and subject
        $newRequest = $this->requestModel->getFinalRequest($sender, $receiver, $currentRequest, $data, $clickedButton);
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
