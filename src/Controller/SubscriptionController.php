<?php

namespace App\Controller;

use App\Entity\BroadcastMessage;
use App\Entity\Member;
use App\Entity\Preference;
use App\Form\NewsletterUnsubscribeType;
use App\Model\SubscriptionModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    /**
     * @Route( "/unsubscribe/newsletter/{username}/{unsubscribeKey}", name="regular_newsletter_unsubscribe",
     *     requirements={"unsubscribeKey"="[a-z0-9]{64}"}
     * )
     */
    public function UnsubscribeNewsletter(
        Request $request,
        SubscriptionModel $subscriptionModel,
        string $username,
        string $unsubscribeKey
    ): Response {
        $form = $this->createForm(NewsletterUnsubscribeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = $subscriptionModel->unsubscribeNewsletter($username, $unsubscribeKey);

            if ($success) {
                return $this->render('newsletter/unsubscribe_successful.html.twig', [
                    'username' => $username,
                ]);
            } else {
                return $this->render('newsletter/unsubscribe_failed.html.twig', [
                    'username' => $username,
                ]);
            }
        }

        return $this->render('newsletter/unsubscribe_confirm.html.twig', [
            'username' => $username,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route( "/unsubscribe/local/{username}/{unsubscribeKey}", name="local_newsletter_unsubscribe",
     *     requirements={"unsubscribeKey"="[a-z0-9]{64}"}
     * )
     */
    public function UnsubscribeLocalEvents(
        Request $request,
        SubscriptionModel $subscriptionModel,
        string $username,
        string $unsubscribeKey
    ): Response {
        $form = $this->createForm(NewsletterUnsubscribeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = $subscriptionModel->unsubscribeNewsletter($username, $unsubscribeKey);

            if ($success) {
                return $this->render('newsletter/unsubscribe_local_successful.html.twig', [
                    'username' => $username,
                ]);
            } else {
                return $this->render('newsletter/unsubscribe_local_failed.html.twig', [
                    'username' => $username,
                ]);
            }
        }

        return $this->render('newsletter/unsubscribe_local_confirm.html.twig', [
            'username' => $username,
            'form' => $form->createView(),
        ]);
    }
}
