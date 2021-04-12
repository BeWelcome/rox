<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends AbstractController
{
    /**
     * @Route( "/newsletter/nounsubscribe", name="newsletter_no_unsubscribe")
     */
    public function noUnsubscribeFromNewsletter(): Response
    {
        return $this->render('newsletter/no_unsubscribe.html.twig');
    }
}
