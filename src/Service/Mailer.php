<?php


namespace App\Service;


use App\Entity\FeedbackCategory;
use App\Entity\Group;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class Mailer
{
    private const MESSAGE_EMAIL_ADDRESS = 'message@bewelcome.org';
    private const GROUP_EMAIL_ADDRESS = 'group@bewelcome.org';
    private const PASSWORD_EMAIL_ADDRESS = 'password@bewelcome.org';
    private const SIGNUP_EMAIL_ADDRESS = 'signup@bewelcome.org';

    /** @var Environment  */
    private $twig;
    /** @var MailerInterface */
    private $mailer;
    /** @var TranslatorInterface */
    private $translator;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        Environment $twig,
        TranslatorInterface $translator
    )
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    public function sendMessageNotificationEmail(Member $sender, Member $receiver, string $template, $parameters)
    {
        $parameters['sender'] = $sender;
        return $this->sendTemplateEmail(
            $this->getBewelcomeAddress($sender, self::MESSAGE_EMAIL_ADDRESS),
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendGroupNotificationEmail(Member $sender, Member $receiver, string $template, $parameters)
    {
        $parameters['sender'] = $sender;
        $parameters['receiver'] = $receiver;
        $parameters['receiver'] = $receiver;

        return $this->sendTemplateEmail(
            $this->getBewelcomeAddress($sender, self::GROUP_EMAIL_ADDRESS),
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendAdminGroupNotificationEmail(Member $receiver, string $template, $parameters)
    {
        return $this->sendTemplateEmail(
            self::GROUP_EMAIL_ADDRESS,
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendGroupApprovalEmail(Member $creator, $parameters)
    {
        return $this->sendTemplateEmail(
            self::GROUP_EMAIL_ADDRESS,
            $creator,
            'group/approved',
            $parameters
        );
    }

    public function sendCommentReportedFeedbackEmail(Member $member, $parameters)
    {
        $parameters['sender'] = $member;
        $feedbackCategoryRepository = $this->entityManager->getRepository(FeedbackCategory::class);
        $feedbackCategory = $feedbackCategoryRepository->findOneBy(['name' => 'Comment_issue']);

        return $this->sendTemplateEmail(
            $member,
            new Address($feedbackCategory->getEmailToNotify(), 'Comment Issue'),
            'comment.feedback',
            $parameters
        );
    }

    public function sendPasswordResetLinkEmail(Member $receiver, $parameters)
    {
        return $this->sendTemplateEmail(
            self::PASSWORD_EMAIL_ADDRESS,
            $receiver,
            'reset.password',
            $parameters
        );
    }

    public function sendSignupSuccessfulEmail(Member $receiver, $parameters)
    {
        return $this->sendTemplateEmail(
            self::SIGNUP_EMAIL_ADDRESS,
            $receiver,
            'signup',
            $parameters
        );
    }

    public function sendSignupEmailConfirmationEmail(Member $receiver, $parameters)
    {
        return $this->sendTemplateEmail(
            self::SIGNUP_EMAIL_ADDRESS,
            $receiver,
            'resent',
            $parameters
        );
    }

    public function sendNewsletterEmail(string $sender, Member $receiver, $parameters)
    {
        return $this->sendTemplateEmail(
            $sender,
            $receiver,
            'newsletter',
            $parameters
        );
    }

    public function sendNotificationEmail(string $sender, Member $receiver, $parameters)
    {
        return $this->sendTemplateEmail(
            $sender,
            $receiver,
            'notifications',
            $parameters
        );
    }

    private function getBewelcomeAddress(Member $sender, $email)
    {
        return new Address($email, 'Bewelcome - ' . $sender->getUsername());
    }

    /**
     * @param Member|Address|string $sender
     * @param Member|Address $receiver
     * @param string $template
     * @param mixed $parameters
     *
     * @return bool
     */
    private function sendTemplateEmail($sender, $receiver, string $template, $parameters)
    {
        $success = true;
        $locale = 'en';
        if ($receiver instanceof Member) {
            $this->setTranslatorLocale($receiver);
            $locale = $receiver->getPreferredLanguage()->getShortcode();
            $parameters['receiver'] = $receiver;
            $receiver = new Address($receiver->getEmail(), $receiver->getUsername());
        } elseif (!$receiver instanceof Address) {
            throw new InvalidArgumentException(sprintf('$receiver must be an instance of %s or %s.', Member::class, Address::class));
        }

        $parameters['template'] = $template;
        $parameters['receiverLocale'] = $locale;
        $subject = $parameters['subject'];
        $subjectParams = [];
        if (\is_array($subject)) {
            $subjectParams = $subject['parameters'];
            $subject = $subject['translationId'];
        }
        $subject = $this->translator->trans($subject, $subjectParams);
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
            $email->from($this->getBewelcomeAddress($sender, 'message@bewelcome.org'));
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

    /**
     * Make sure to sent the email notification in the preferred language of the user.
     */
    private function setTranslatorLocale(Member $receiver)
    {
        $language = $receiver->getPreferredLanguage();
        $this->translator->setLocale($language->getShortcode());
    }
}
