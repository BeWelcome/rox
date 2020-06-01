<?php

namespace App\Utilities;

use App\Entity\Member;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Trait MailerTrait.
 */
trait MailerTrait
{
    /** @var Mailer */
    private $mailer;

    /**
     * @required
     */
    public function setMailer(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @return Mailer
     */
    protected function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @param Member|Address|string $sender
     * @param mixed                 ...$params
     *
     * @return bool
     */
    protected function sendTemplateEmail($sender, Member $receiver, string $template, ...$params)
    {
        $success = true;
        $this->setTranslatorLocale($receiver);
        $locale = $receiver->getPreferredLanguage();
        $parameters = array_merge(['sender' => $sender, 'receiver' => $receiver, 'tenplate' => $template,
            'receiverLocale' => $locale->getShortcode(), ], ...$params);
        $subject = $parameters['subject'];
        $subjectParams = [];
        if (\is_array($subject)) {
            $subjectParams = $subject['parameters'];
            $subject = $subject['translationId'];
        }
        $subject = $this->getTranslator()->trans($subject, $subjectParams);
        $email = (new TemplatedEmail())
            ->to(new Address($receiver->getEmail(), $receiver->getUsername()))
            ->subject($subject)
            ->htmlTemplate('emails/' . $template . '.html.twig')
            ->context($parameters)
        ;

        if (\is_string($sender)) {
            $email->from($sender);
        } elseif ($sender instanceof Address) {
            $email->from($sender);
        } else {
            $email->from(new Address('message@bewelcome.org', $sender->getUsername() . ' - BeWelcome'));
        }
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $success = false;
        }
        if ($sender instanceof Member) {
            $this->setTranslatorLocale($sender);
        }

        return $success;
    }
}
