<?php

namespace App\Utilities;

use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

trait MessageTrait
{
    use TranslatorTrait;

    /**
     * Twig environment used to render templates
     *
     * @var Environment
     */
    private $environment;

    /**
     * @Required
     *
     * @param Environment $environment
     */
    public function setTwigEnvironment(Environment $environment)
    {
        $this->environment = $environment;
    }

    protected function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param Member $sender
     * @param Member $receiver
     * @param string $parent
     * @param string $template
     * @param mixed ...$params
     */
    protected function createTemplateMessage(
        Member $sender,
        Member $receiver,
        string $template,
        ...$params
    ) {
        $parameters = array_merge(['sender' => $sender, 'receiver' => $receiver], ...$params);

        $em = $this->getManager();

        $this->setTranslatorLocale($receiver);
        $subjectText = $this->getTranslator()->trans($parameters['subject']);
        $subject = new Subject();
        $subject->setSubject($subjectText);
        $em->persist($subject);
        $em->flush($subject);

        $message = new Message();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setSubject($subject);

        $body = $this->getEnvironment()->render('emails/' . $template . '.html.twig', $parameters);
        $message->setMessage($body);
        $em->persist($message);
        $em->flush();
        $this->setTranslatorLocale($sender);
    }
}
