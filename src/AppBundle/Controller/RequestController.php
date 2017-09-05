<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use AppBundle\Form\MessageRequestType;
use AppBundle\Model\MessageModel;
use Html2Text\Html2Text;
use Rox\Core\Exception\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestController extends Controller
{
    /**
     * @Route("/new/request/{username}", name="hosting_request")
     *
     * @param Member  $receiver
     * @param Request $request
     * @param Member  $receiver
     *
     * @return Response
     * @return Response
     */
    public function newHostingRequestAction(Request $request, Member $receiver)
    {
        $member = $this->getUser();
        if ($member === $receiver) {
            throw new InvalidArgumentException('You can\'t send a request to yourself.');
        }

        $requestForm = $this->createForm(MessageRequestType::class);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            // Write request to database after doing some checks
            /** @var Message $hostingRequest */
            $hostingRequest = $requestForm->getData();
            $hostingRequest->setSender($this->getUser());
            $hostingRequest->setReceiver($receiver);
            $hostingRequest->setInfolder('requests');
            $hostingRequest->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();

            // Send mail notification
            $html2Text = new Html2Text($hostingRequest->getMessage());
            $hostingRequestText = $html2Text->getText();
            $message = \Swift_Message::newInstance()
                ->setSubject($hostingRequest->getSubject()->getSubject())
                ->setFrom('request@bewelcome.org')
                ->setTo($receiver->getCryptedField('Email'))
                ->setBody(
                    $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                        'emails/request.html.twig',
                        ['request_text' => $hostingRequest->getMessage()]
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'emails/request.txt.twig',
                        ['request_text' => $hostingRequestText]
                    ),
                    'text/plain'
                )
            ;
            $this->get('mailer')->send($message);
            $this->addFlash('success', 'Request has been sent.');

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render(':request:request.html.twig', [
            'receiver' => $receiver,
            'form' => $requestForm->createView(),
        ]);
    }

    /**
     * @Route("/request/{id}/reply", name="hosting_request_reply")
     *
     * @param Request $request
     * @param Message $hostingRequest
     *
     * @return Response
     */
    public function hostingRequestReplyAction(Request $request, Message $hostingRequest)
    {
        if ($hostingRequest->getRequest() === null) {
            // Todo redirect to message instead of throwing an exception
            throw new InvalidArgumentException();
        }

        $requestForm = $this->createForm(MessageRequestType::class);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($hostingRequest);

        return $this->render(':request:reply.html.twig', [
            'form' => $requestForm->createView(),
            'current' => $hostingRequest,
            'thread' => $thread,
        ]);
    }
}
