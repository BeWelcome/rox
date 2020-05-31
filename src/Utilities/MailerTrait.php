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
     * @param Member|Address        $receiver
     * @param string        $template
     * @param mixed         ...$params
     *
     * @return bool
     */
    protected function sendTemplateEmail($sender, $receiver, string $template, ...$params)
    {
        $success = true;
        $locale = 'en';
        if ($receiver instanceof Member) {
            $this->setTranslatorLocale($receiver);
            $locale = $receiver->getPreferredLanguage()->getShortcode();
            $params = array_merge([
                'receiver' => $receiver,
            ], ...$params);
            $receiver = new Address($receiver->getEmail(), $receiver->getUsername());
        } elseif (!$receiver instanceof Address) {
            throw new \InvalidArgumentException(sprintf('$receiver must be an instance of %s or %s.', Member::class, Address::class));
        }

        $parameters = array_merge([
            'sender' => $sender,
            'template' => $template,
            'receiverLocale' => $locale,
        ], $params);
        $subject = $parameters['subject'];
        $subjectParams = [];
        if (\is_array($subject)) {
            $subjectParams = $subject['parameters'];
            $subject = $subject['translationId'];
        }
        $subject = $this->getTranslator()->trans($subject, $subjectParams);
        $email = (new TemplatedEmail())
            ->to($receiver)
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
