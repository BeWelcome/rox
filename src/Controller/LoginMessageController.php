<?php

namespace App\Controller;

use App\Entity\LoginMessage;
use App\Entity\LoginMessageAcknowledged;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * A member acknowledges a site announcement ("Got it" on its strip: templates/layout/_login_messages.html.twig); it
 * no longer shows for them.
 */
class LoginMessageController extends AbstractController
{
    /** The CSRF token id of acknowledging. */
    public const string TOKEN = 'acknowledge_login_message';

    #[Route(path: '/loginmessage/acknowledge/{id}', name: 'acknowledge_login_message', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function acknowledge(LoginMessage $loginMessage, EntityManagerInterface $entityManager): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $acknowledgedRepository = $entityManager->getRepository(LoginMessageAcknowledged::class);
        $acknowledged = $acknowledgedRepository->findOneBy(['message' => $loginMessage, 'member' => $member]);
        if (null === $acknowledged) {
            $acknowledged = new LoginMessageAcknowledged();
        }
        $acknowledged
            ->setMessage($loginMessage)
            ->setMember($member)
            ->setAcknowledged();
        $entityManager->persist($acknowledged);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
