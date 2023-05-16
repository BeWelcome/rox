<?php

namespace App\Controller;

use App\Entity\LoginMessage;
use App\Entity\LoginMessagesAcknowledged;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginMessageController extends AbstractController
{
    /**
     * @Route("/loginmessage/acknowledge/{id}",
     *     name="acknowledge_login_message",
     *     requirements={"id": "\d+"},
     *     methods={"POST"}
     * )
     *
     * @return void
     */
    public function acknowledge(LoginMessage $loginMessage, EntityManagerInterface $entityManager): Response
    {
        $member = $this->getUser();

        $loginMessageAcknowledgedRepository = $entityManager->getRepository(LoginMessagesAcknowledged::class);
        $loginMessageAcknowledged = $loginMessageAcknowledgedRepository->findOneBy(['message' => $loginMessage, 'member' => $member]);
        if (null == $loginMessageAcknowledged) {
            $loginMessageAcknowledged  = new LoginMessagesAcknowledged();
        }
        $loginMessageAcknowledged->setMessage($loginMessage);
        $loginMessageAcknowledged->setMember($member);
        $loginMessageAcknowledged->setAcknowledged(true);
        $entityManager->persist($loginMessageAcknowledged);
        $entityManager->flush();

        return new Response();
    }
}
