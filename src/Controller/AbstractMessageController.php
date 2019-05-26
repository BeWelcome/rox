<?php

namespace App\Controller;

use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Preference;
use App\Form\CustomDataClass\MessageIndexRequest;
use App\Form\MessageIndexFormType;
use App\Model\MessageModel;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Html2Text\Html2Text;
use Pagerfanta\Pagerfanta;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractMessageController extends AbstractController
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var Swift_Mailer */
    protected $mailer;

    /**
     * @required
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @required
     *
     * @param Swift_Mailer $mailer
     */
    public function setMailer(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
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
     * @param Member $sender
     * @param Member $receiver
     * @param string $subject
     * @param string $htmlBody
     *
     * @return bool
     */
    protected function sendEmail(Member $sender, Member $receiver, $subject, $htmlBody)
    {
        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::HTML_MAILS]);
        $htmlMails = ('Yes' === $receiver->getMemberPreferenceValue($preference));

        $converter = new Html2Text($htmlBody, [
            'do_links' => 'table',
            'width' => 75,
        ]);
        $plainText = $converter->getText();
        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom([
                'message@bewelcome.org' => 'BeWelcome - '.$sender->getUsername(),
            ])
            ->setTo($receiver->getEmail())
            ->setBody(
                $plainText,
                'text/plain'
            );

        if ($htmlMails) {
            $message
                ->addPart($htmlBody, 'text/html')
            ;
        }
        $recipients = $this->mailer->send($message);

        return (0 === $recipients) ? false : true;
    }

    /**
     * Make sure to sent the email notification in the preferred language of the user.
     *
     * @param Member $receiver
     */
    protected function setTranslatorLocale(Member $receiver)
    {
        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::LOCALE]);

        $languageRepository = $this->getDoctrine()->getRepository(Language::class);
        /** @var Language $language */
        $language = $languageRepository->find($receiver->getMemberPreferenceValue($preference));

        $this->translator->setLocale($language->getShortcode());
    }

    protected function addExpiredFlash(Member $receiver)
    {
        $expiredSendMessage = $this->translator->trans('flash.request.expired', [
            '%link_start%' => '<a href="'.$this->generateUrl('message_new', [
                    'username' => $receiver->getUsername(),
                ]).'" class="text-primary">',
            '%link_end%' => '</a>',
        ]);
        $this->addFlash('notice', $expiredSendMessage);
    }

    protected function addTranslatedFlash($type, $flashId)
    {
        $translatedFlash = $this->translator->trans($flashId);
        $this->addFlash($type, $translatedFlash);
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
        $member = $this->getUser();
        $messageModel = new MessageModel();
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
            if ($form->get('purge')->isClicked()) {
                $messageModel->markPurged($member, $messageIds);
                $this->addTranslatedFlash('notice', 'flash.purged');

                return $this->redirect($request->getRequestUri());
            }
            if ($form->get('delete')->isClicked()) {
                if ('deleted' === $folder) {
                    $messageModel->unmarkDeleted($member, $messageIds);
                    $this->addTranslatedFlash('notice', 'flash.undeleted');

                    return $this->redirect($request->getRequestUri());
                }
                $messageModel->markDeleted($member, $messageIds);
                $this->addTranslatedFlash('notice', 'flash.deleted');

                return $this->redirect($request->getRequestUri());
            }
            if ($form->get('spam')->isClicked()) {
                if ('spam' === $folder) {
                    $messageModel->unmarkAsSpam($messageIds);
                    $this->addTranslatedFlash('notice', 'flash.marked.nospam');

                    return $this->redirect($request->getRequestUri());
                }
                $messageModel->markAsSpam($messageIds);
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
}
