<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\MembersPhoto;
use App\Entity\MemberTranslation;
use App\Entity\Message;
use App\Entity\Preference;
use App\Model\BaseRequestModel;
use App\Model\ConversationModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;

abstract class BaseRequestAndInvitationController extends AbstractController
{
    use TranslatorTrait;
    use TranslatedFlashTrait;
    protected ConversationModel $conversationModel;

    public function __construct(protected BaseRequestModel $model, protected EntityManagerInterface $entityManager)
    {
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
        $clickedButton = $requestForm->getClickedButton()->getName();

        // handle changes in request and subject
        $newRequest = $this->model->getFinalRequest($sender, $receiver, $currentRequest, $data, $clickedButton);
        $this->entityManager->persist($newRequest);
        $this->entityManager->flush();

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
        if (!str_starts_with($subject, 'Re:')) {
            $subject = 'Re: ' . $subject;
        }

        $locale = $newRequest->getReceiver()->getPreferredLanguage()->getShortCode();

        return $this->adjustSubject($newRequest->getRequest()->getStatus(), $subject, $locale);
    }

    private function adjustSubject(int $status, string $subject, string $locale): string
    {
        $suffix = match ($status) {
            HostingRequest::REQUEST_DECLINED => 'email.suffix.declined',
            HostingRequest::REQUEST_CANCELLED => 'email.suffix.cancelled',
            HostingRequest::REQUEST_ACCEPTED => 'email.suffix.accepted',
            HostingRequest::REQUEST_TENTATIVELY_ACCEPTED => 'email.suffix.maybe',
            default => '',
        };

        if (!empty($suffix)) {
            $translator = $this->getTranslator();
            $currentLocale = $translator->getLocale();
            $translator->setLocale($locale);
            $suffix = $translator->trans($suffix);
            if (!str_contains($suffix, $subject)) {
                $subject .= ' ' . $suffix;
            }
            $translator->setLocale($currentLocale);
        }

        return $subject;
    }
}
