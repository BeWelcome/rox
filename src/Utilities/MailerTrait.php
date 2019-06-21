<?php

namespace App\Utilities;

use App\Entity\Member;
use App\Entity\Preference;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\NamedAddress;

trait MailerTrait
{
    use ManagerTrait;

    /** @var Mailer */
    private $mailer;

    /**
     * @required
     *
     * @param MailerInterface $mailer
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
     * @param Member|string $sender
     * @param Member        $receiver
     * @param string        $template
     * @param mixed         ...$params
     *
     * @return bool
     */
    protected function sendTemplateEmail($sender, Member $receiver, string $template, ...$params)
    {
        $success = true;
        /* Only HTML mails supported
        $preferenceRepository = $this->getManager()->getRepository(Preference::class);
        /** @var Preference $preference /
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::HTML_MAILS]);
        // Only HTML mails with text part supported
        $htmlMails = ('Yes' === $receiver->getMemberPreferenceValue($preference));
        */
        $this->setTranslatorLocale($receiver);
        $parameters = array_merge(['sender' => $sender, 'receiver' => $receiver], ...$params);
        $subject = $this->getTranslator()->trans($parameters['subject']);
        $email = (new TemplatedEmail())
            ->to(new NamedAddress($receiver->getEmail(), $receiver->getUsername()))
            ->subject($subject)
            ->htmlTemplate('emails/'.$template.'.html.twig')
            ->context($parameters)
        ;

        if (\is_string($sender)) {
            $email->from($sender);
        } else {
            $email->from(new NamedAddress('message@bewelcome.org', $sender->getUsername()));
        }
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $success = false;
        }
        if (!\is_string($sender)) {
            $this->setTranslatorLocale($sender);
        }

        return $success;
    }
}
