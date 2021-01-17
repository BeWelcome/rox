<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Form\CustomDataClass\MessageIndexRequest;
use App\Form\MessageIndexFormType;
use App\Model\HostingRequestModel;
use App\Model\MessageModel;
use App\Repository\MessageRepository;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseHostingRequestAndInvitationController extends BaseMessageController
{
    /**
     * @var HostingRequestModel
     */
    protected $requestModel;

    public function __construct(MessageModel $messageModel, HostingRequestModel $requestModel)
    {
        parent::__construct($messageModel);

        $this->requestModel = $requestModel;
    }

    protected function checkRequestExpired(Message $hostingRequest): bool
    {
        $requestModel = new HostingRequestModel();

        return $requestModel->isRequestExpired($hostingRequest->getRequest());
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

    protected function getSubjectForReply(Message $newRequest)
    {
        $subject = $newRequest->getSubject()->getSubject();
        if ('Re:' !== substr($subject, 0, 3)) {
            $subject = 'Re: ' . $subject;
        }

        if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
            if (false === strpos('(Cancelled)', $subject)) {
                $subject = $subject . ' (Cancelled)';
            }
        }

        if (HostingRequest::REQUEST_DECLINED === $newRequest->getRequest()->getStatus()) {
            if (false === strpos('(Declined)', $subject)) {
                $subject = $subject . ' (Declined)';
            }
        }

        if (HostingRequest::REQUEST_ACCEPTED === $newRequest->getRequest()->getStatus()) {
            if (false === strpos('(Accepted)', $subject)) {
                $subject = $subject . ' (Accepted)';
            }
        }

        if (HostingRequest::REQUEST_TENTATIVELY_ACCEPTED === $newRequest->getRequest()->getStatus()) {
            if (false === strpos('(Tentatively accepted)', $subject)) {
                $subject = $subject . ' (Tentatively accepted)';
            }
        }

        return $subject;
    }

    protected function redirectToMessageReply(Message $message): RedirectResponse
    {
        return $this->redirectToRoute('message_reply', ['id' => $message->getId()]);
    }

    private function getRedirectUrl(Request $request)
    {
        return $request->getRequestUri();
    }
}
