<?php

namespace App\Utilities;

use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use Twig\Environment;

trait MessageTrait
{
    use TranslatorTrait;

    /**
     * Twig environment used to render templates.
     *
     * @var Environment
     */
    private $environment;

    /**
     * @Required
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
        $subject = $parameters['subject'];
        $translator = $this->getTranslator();
        if (\is_array($subject)) {
            $subjectText = $translator->trans($subject['translationId'], $subject['parameters']);
        } else {
            $subjectText = $translator->trans($subject);
        }
        $subject = new Subject();
        $subject->setSubject($subjectText);
        $em->persist($subject);
        $em->flush($subject);

        $message = new Message();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setSubject($subject);

        $body = $this->getEnvironment()->render($template . '.html.twig', $parameters);
        $message->setMessage($body);
        $em->persist($message);
        $em->flush();
        $this->setTranslatorLocale($sender);
    }
}
