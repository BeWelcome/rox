<?php

namespace App\Controller;

use App\Entity\BroadcastMessage;
use App\Entity\Member;
use App\Entity\Preference;
use App\Model\SubscriptionModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    /**
     * @Route( "/newsletter/unsubscribe/{username}/{unsubscribeKey}", name="newsletter_unsubscribe",
     *     requirements={"unsubscribeKey"="[a-z0-9]{64}"}
     * )
     */
    public function UnsubscribeNewsletter(
        SubscriptionModel $subscriptionModel,
        string $username,
        string $unsubscribeKey
    ): Response {
        $broadcastRepository = $this->getDoctrine()->getRepository(BroadcastMessage::class);
        $memberRepository =  $this->getDoctrine()->getRepository(Member::class);
        /** @var BroadcastMessage $broadcast */
        $broadcast = $broadcastRepository->findOneBy(['unsubscribeKey' => $unsubscribeKey]);
        if (null === $broadcast) {
            return $this->render('newsletter/unsubscribe_failed.html.twig');
        }

        /** @var Member $member */
        $member = $memberRepository->find($broadcast->getReceiver());
        if ($username !== $member->getUsername()) {
            return $this->render('newsletter/unsubscribe_failed.html.twig');
        }

        $subscriptionModel->unsubscribeNewsletter($member, $broadcast->getNewsletter());

        return $this->render('newsletter/unsubscribe_successful.html.twig', [
            'username' => $username,
            'broadcast' => $broadcast,
        ]);
    }
}
