<?php

namespace App\Controller;

use App\Entity\LoginMessage;
use App\Entity\LoginMessageAcknowledged;
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
     */
    public function acknowledge(LoginMessage $loginMessage, EntityManagerInterface $entityManager): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $acknowledgedRepository = $entityManager->getRepository(LoginMessageAcknowledged::class);
        $acknowledged = $acknowledgedRepository->findOneBy(['message' => $loginMessage, 'member' => $member]);
        if (null == $acknowledged) {
            $acknowledged = new LoginMessageAcknowledged();
        }
        $acknowledged
            ->setMessage($loginMessage)
            ->setMember($member)
            ->setAcknowledged();
        $entityManager->persist($acknowledged);
        $entityManager->flush();

        return new Response();
    }
}
