<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Message;
use App\Model\HostingRequestModel;
use App\Model\MessageModel;
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
}
