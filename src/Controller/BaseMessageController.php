<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Message;
use App\Form\CustomDataClass\MessageIndexRequest;
use App\Form\MessageIndexFormType;
use App\Model\MessageModel;
use App\Repository\MessageRepository;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseMessageController extends AbstractController
{
    use TranslatorTrait;
    use TranslatedFlashTrait;

    /** @var MessageModel */
    protected $messageModel;

    public function __construct(MessageModel $messageModel)
    {
        $this->messageModel = $messageModel;
    }

    protected function getSubMenuItems()
    {
        return [
            'both_inbox' => [
                'key' => 'MessagesRequestsReceived',
                'url' => $this->generateUrl('both', ['folder' => 'inbox']),
            ],
            'messages_inbox' => [
                'key' => 'MessagesReceived',
                'url' => $this->generateUrl('messages', ['folder' => 'inbox']),
            ],
            'requests_inbox' => [
                'key' => 'RequestsReceived',
                'url' => $this->generateUrl('requests', ['folder' => 'inbox']),
            ],
            'requests_sent' => [
                'key' => 'RequestsSent',
                'url' => $this->generateUrl('requests', ['folder' => 'sent']),
            ],
            'messages_sent' => [
                'key' => 'MessagesSent',
                'url' => $this->generateUrl('messages', ['folder' => 'sent']),
            ],
            'messages_spam' => [
                'key' => 'MessagesSpam',
                'url' => $this->generateUrl('messages', ['folder' => 'spam']),
            ],
            'messages_deleted' => [
                'key' => 'MessagesDeleted',
                'url' => $this->generateUrl('messages', ['folder' => 'deleted']),
            ],
        ];
    }

    /**
     * @param Request    $request
     * @param string     $folder
     * @param Pagerfanta $messages
     * @param $type
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return Response
     */
    protected function handleFolderRequest(Request $request, $folder, Pagerfanta $messages, $type)
    {
        /** @var Member $member */
        $member = $this->getUser();
        $messageIds = [];
        foreach ($messages->getIterator() as $key => $val) {
            $messageIds[$key] = $val->getId();
        }
        $messageRequest = new MessageIndexRequest();
        $form = $this->createForm(MessageIndexFormType::class, $messageRequest, [
            'folder' => $folder,
            'ids' => $messageIds,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $messageIds = $data->getMessages();

            if ($form->has('purge') && $form->get('purge')->isClicked()) {
                $this->messageModel->markPurged($member, $messageIds);
                $this->addTranslatedFlash('notice', 'flash.purged');

                return $this->redirect($request->getRequestUri());
            }
            if ($form->has('delete') && $form->get('delete')->isClicked()) {
                if ('deleted' === $folder) {
                    $this->messageModel->unmarkDeleted($member, $messageIds);
                    $this->addTranslatedFlash('notice', 'flash.undeleted');

                    return $this->redirect($request->getRequestUri());
                }
                $this->messageModel->markDeleted($member, $messageIds);
                $this->addTranslatedFlash('notice', 'flash.deleted');

                return $this->redirect($request->getRequestUri());
            }
            if ($form->get('spam')->isClicked()) {
                if ('spam' === $folder) {
                    $this->messageModel->unmarkAsSpam($messageIds);
                    $this->addTranslatedFlash('notice', 'flash.marked.nospam');

                    return $this->redirect($request->getRequestUri());
                }
                $this->messageModel->markAsSpam($messageIds);
                $this->addTranslatedFlash('notice', 'flash.marked.spam');

                return $this->redirect($request->getRequestUri());
            }
        }

        return $this->render('message/index.html.twig', [
            'form' => $form->createView(),
            'items' => $messages,
            'folder' => $folder,
            'filter' => $request->query->all(),
            'submenu' => [
                'active' => $type.'_'.$folder,
                'items' => $this->getSubMenuItems(),
            ],
        ]);
    }

    /**
     * @param Message $message
     *
     * @return bool
     * @throws AccessDeniedException
     */
    protected function isMessageOfMember(Message $message)
    {
        $member = $this->getUser();
        if (($message->getReceiver() !== $member) && ($message->getSender() !== $member)) {
            throw $this->createAccessDeniedException();
        }

        return true;
    }

    /**
     * @param $probableParent
     *
     * @return Message
     */
    protected function getParent($probableParent)
    {
        // Check if there is already a newer message than the one used for the request
        // as there might be a clash of replies
        /** @var MessageRepository */
        $hostingRequestRepository = $this->getDoctrine()->getRepository(Message::class);
        /** @var Message[] $messages */
        $messages = $hostingRequestRepository->findBy(['subject' => $probableParent->getSubject()]);

        return $messages[\count($messages) - 1];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getOptionsFromRequest(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'dateSent');
        $direction = $request->query->get('dir', 'desc');

        return [$page, $limit, $sort, $direction];
    }
}
