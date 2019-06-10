<?php

namespace App\Utilities;

use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Preference;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Html2Text\Html2Text;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\NamedAddress;
use Symfony\Component\Templating\EngineInterface;

trait MailerTrait
{
    use ManagerTrait;
    use TranslatorTrait;

    /** @var Mailer */
    private $mailer;

    /**
     * @required
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
     * @param Member $receiver
     * @param string $template
     * @param mixed ...$params
     */
    protected function sendTemplateEmail($sender, Member $receiver, string $template, ...$params)
    {
        $preferenceRepository = $this->getManager()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::HTML_MAILS]);
        $htmlMails = ('Yes' === $receiver->getMemberPreferenceValue($preference));

        $this->setTranslatorLocale($receiver);
        $parameters = array_merge([ 'sender' => $sender, 'receiver' => $receiver], ...$params);
        $subject = $this->getTranslator()->trans($parameters['subject']);
        $email = (new TemplatedEmail())
            ->to(new Address($receiver->getEmail()))
            ->subject($subject)
            ->context($parameters)
        ;

        if (is_string($sender)) {
            $email->from($sender);
        } else {
            $email->from(new NamedAddress('message@bewelcome.org', $sender->getUsername()));
        }
        if ($htmlMails) {
            $email
                ->htmlTemplate('emails/' . $template . '.html.twig')
                ->textTemplate('emails/' . $template . '.txt.twig')
            ;
        } else {
            $email
                ->textTemplate('emails/' . $template . '.txt.twig')
            ;
        }
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // Mail not send; now what?
        }
        if (!is_string($sender)) {
            $this->setTranslatorLocale($sender);
        }
    }
}
