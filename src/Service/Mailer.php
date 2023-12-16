<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\FeedbackCategory;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Relation;
use App\Logger\Logger;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class Mailer
{
    private const NO_REPLY_EMAIL_ADDRESS = 'noreply@bewelcome.org';
    private const MESSAGE_EMAIL_ADDRESS = 'message@bewelcome.org';
    private const GROUP_EMAIL_ADDRESS = 'group@bewelcome.org';
    private const PASSWORD_EMAIL_ADDRESS = 'password@bewelcome.org';
    private const SIGNUP_EMAIL_ADDRESS = 'signup@bewelcome.org';

    /** @var MailerInterface */
    private $mailer;
    /** @var TranslatorInterface */
    private $translator;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    private Logger $logger;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        MailerInterface $mailer,
        Logger $logger
    ) {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function sendMessageNotificationEmail(Member $sender, Member $receiver, string $template, $parameters): bool
    {
        $parameters['sender'] = $sender;

        return $this->sendTemplateEmail(
            $this->getBeWelcomeAddressWithUsername($sender),
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendGroupNotificationEmail(Member $sender, Member $receiver, string $template, $parameters): bool
    {
        $parameters['sender'] = $sender;
        $parameters['receiver'] = $receiver;

        return $this->sendTemplateEmail(
            $this->getBeWelcomeAddress($sender, self::GROUP_EMAIL_ADDRESS),
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendGroupEmail(Member $receiver, string $template, $parameters): bool
    {
        return $this->sendTemplateEmail(
            self::GROUP_EMAIL_ADDRESS,
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendCommentReportedFeedbackEmail(Member $member, $parameters): bool
    {
        $parameters['sender'] = $member;
        $parameters['receiver'] = $member;
        $feedbackCategoryRepository = $this->entityManager->getRepository(FeedbackCategory::class);
        $feedbackCategory = $feedbackCategoryRepository->findOneBy(['name' => 'Comment_issue']);

        return $this->sendTemplateEmail(
            new Address($member->getEmail()),
            new Address($feedbackCategory->getEmailToNotify(), 'Comment Issue'),
            'comment.feedback',
            $parameters
        );
    }

    public function sendPasswordResetLinkEmail(Member $receiver, $parameters): bool
    {
        return $this->sendTemplateEmail(
            self::PASSWORD_EMAIL_ADDRESS,
            $receiver,
            'reset.password',
            $parameters
        );
    }

    public function sendSignupEmail(Member $receiver, string $template, $parameters): bool
    {
        return $this->sendTemplateEmail(
            self::SIGNUP_EMAIL_ADDRESS,
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendNewsletterEmail(Newsletter $newsletter, Member $receiver, array $parameters): bool
    {
        $parameters = array_merge($parameters, $this->prepareParametersForNewsletter($newsletter, $receiver));

        return $this->sendTemplateEmail(
            $parameters['sender'],
            $receiver,
            'newsletter',
            $parameters
        );
    }

    public function sendNotificationEmail(Address $sender, Member $receiver, $parameters): bool
    {
        return $this->sendTemplateEmail(
            $sender,
            $receiver,
            'notifications',
            $parameters
        );
    }

    /**
     * This feeds the feedback given by a user into the OTRS queues.
     */
    public function sendFeedbackEmail($sender, Address $receiver, $parameters): bool
    {
        $parameters['subject'] = "Your feedback in '"
            . str_replace('_', ' ', ($parameters['IdCategory'])->getName()) . "'";

        return $this->sendTemplateEmail(
            $sender,
            $receiver,
            'feedback',
            $parameters
        );
    }

    /**
     * Send notification for special relation (friends and family).
     */
    public function sendRelationNotification(Relation $relation): bool
    {
        $parameters = [];
        $parameters['sender'] = $relation->getOwner();
        $parameters['receiver'] = $relation->getReceiver();
        $parameters['comment'] = $relation->getCommentText();
        $parameters['subject'] = [
            'translationId' => 'email.subject.relation',
            'parameters' => [
                'username' => $relation->getOwner()->getUsername(),
            ],
        ];

        return $this->sendTemplateEmail(
            $this->getBeWelcomeAddress($relation->getOwner(), self::NO_REPLY_EMAIL_ADDRESS),
            $relation->getReceiver(),
            'relation.notification',
            $parameters
        );
    }

    /**
     * Send notification for new comment.
     */
    public function sendNewCommentNotification(Comment $comment): bool
    {
        $parameters = [];
        $parameters['subject'] = [
            'translationId' => 'comment.notification.new.subject',
            'parameters' => [
                'username' => $comment->getFromMember()->getUsername(),
            ],
        ];

        return $this->sendCommentTemplateEmail($comment, 'comment.notification.new', $parameters);
    }

    /**
     * Send notification for new comment.
     */
    public function sendCommentUpdateNotification(Comment $comment): bool
    {
        $parameters = [];
        $parameters['subject'] = [
            'translationId' => 'comment.notification.update.subject',
            'parameters' => [
                'username' => $comment->getFromMember()->getUsername(),
            ],
        ];

        return $this->sendCommentTemplateEmail($comment, 'comment.notification.update', $parameters);
    }

    public function sendCommentReminderToGuest(Member $guest, Member $host, string $template): bool
    {
        $parameters = $this->getParametersForCommentReminder($guest, $host, 'comment.reminder.guest.subject', $host);

        return $this->sendTemplateEmail(
            new Address(self::NO_REPLY_EMAIL_ADDRESS, 'BeWelcome'),
            $guest,
            $template,
            $parameters
        );
    }

    public function sendCommentReminderToHost(Member $guest, Member $host): bool
    {
        $parameters = $this->getParametersForCommentReminder($guest, $host, 'comment.reminder.host.subject', $guest);

        return $this->sendTemplateEmail(
            new Address(self::NO_REPLY_EMAIL_ADDRESS, 'BeWelcome'),
            $host,
            'comment.reminder.host',
            $parameters
        );
    }

    private function sendCommentTemplateEmail(Comment $comment, string $template, array $parameters): bool
    {
        $parameters['sender'] = $comment->getFromMember();
        $parameters['receiver'] = $comment->getToMember();
        $parameters['comment'] = $comment;

        return $this->sendTemplateEmail(
            $this->getBeWelcomeAddress($comment->getFromMember(), self::NO_REPLY_EMAIL_ADDRESS),
            $comment->getToMember(),
            $template,
            $parameters
        );
    }

    /**
     * Used for messages and requests notifications to allow recipients to distinguish between those
     * and other notifications.
     */
    private function getBeWelcomeAddressWithUsername(Member $sender): Address
    {
        return new Address(self::MESSAGE_EMAIL_ADDRESS, $sender->getUsername() . ' [BeWelcome]');
    }

    /**
     * Used for all notifications except messages and requests notifications to allow recipients to distinguish between
     * those notifications.
     *
     * @param mixed $email
     */
    private function getBeWelcomeAddress(Member $sender, $email): Address
    {
        return new Address($email, 'BeWelcome - ' . $sender->getUsername());
    }

    /**
     * @param Member|Address|string $sender
     * @param Member|Address        $receiver
     * @param string                $template
     * @param mixed                 $parameters
     *
     * @return bool
     */
    private function sendTemplateEmail($sender, $receiver, string $template, array $parameters): bool
    {
        $currentLocale = $this->translator->getLocale();
        $success = true;
        $locale = 'en';
        if ($receiver instanceof Member) {
            $this->setTranslatorLocale($receiver);
            $locale = $receiver->getPreferredLanguage()->getShortCode();
            $parameters['receiver'] = $receiver;
            $receiver = new Address($receiver->getEmail(), $receiver->getUsername());
        } elseif (!$receiver instanceof Address) {
            $message = sprintf('$receiver must be an instance of %s or %s.', Member::class, Address::class);
            throw new InvalidArgumentException($message);
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
            ->context($parameters);

        if (isset($parameters['datesent'])) {
            $email->date($parameters['datesent']);
        }

        if (!\is_string($sender) && !$sender instanceof Address) {
            $sender = $email->from($this->getBeWelcomeAddressWithUsername($sender));
        }
        $email->from($sender);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $success = false;
        }
        $this->translator->setLocale($currentLocale);

        return $success;
    }

    /**
     * Make sure to send the email notification in the preferred language of the user.
     */
    private function setTranslatorLocale(Member $receiver)
    {
        $language = $receiver->getPreferredLanguage();
        $this->translator->setLocale($language->getShortCode());
    }

    private function prepareParametersForNewsletter(Newsletter $newsletter, Member $receiver): array
    {
        $newsletterType = $newsletter->getType();
        $newsletterName = $newsletter->getName();

        $parameters = [];
        $parameters['sender'] = $this->determineSenderForNewsletter($newsletterType);
        $parameters['receiver'] = $receiver;
        $parameters['newsletter_type'] = $newsletterType;
        $parameters['subject'] = strtolower('Broadcast_Title_' . $newsletterName);
        $parameters['wordcode'] = strtolower('Broadcast_Body_' . $newsletterName);
        if (
            Newsletter::SPECIFIC_NEWSLETTER === $newsletterType
            || Newsletter::REGULAR_NEWSLETTER === $newsletterType
        ) {
        }
        $parameters['newsletter'] = $newsletter;
        $parameters['language'] = $receiver->getPreferredLanguage()->getShortCode();

        return $parameters;
    }

    private function determineSenderForNewsletter($type): Address
    {
        switch ($type) {
            case 'RemindToLog':
            case 'MailToConfirmReminder':
            case Newsletter::SUSPENSION_NOTIFICATION:
                $sender = new Address('reminder@bewelcome.org', 'BeWelcome');
                break;
            case Newsletter::TERMS_OF_USE:
                $sender = new Address('tou@bewelcome.org', 'BeWelcome');
                break;
            default:
                $sender = new Address('newsletter@bewelcome.org', 'BeWelcome');
        }

        return $sender;
    }

    private function getAddCommentATag(Member $member): string
    {
        $url = $this->urlGenerator->generate(
            'add_comment',
            ['username' => $member->getUsername()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return sprintf('<a href="%s">', $url);
    }

    private function getProfileATag(Member $member): string
    {
        $url = $this->urlGenerator->generate(
            'members_profile',
            ['username' => $member->getUsername()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return sprintf('<a href="%s">', $url);
    }

    private function getParametersForCommentReminder(Member $guest, Member $host, string $subject, Member $for): array
    {
        $parameters = [];
        $parameters['guest'] = $guest->getUsername();
        $parameters['host'] = $host->getUsername();
        $parameters['subject'] = [
            'translationId' => $subject,
            'parameters' => [
                'username' => $for->getUsername(),
            ],
        ];

        $parameters['comment_start'] = $this->getAddCommentATag($for);
        $parameters['comment_end'] = '</a>';

        $parameters['profile_start'] = $this->getProfileATag($for);
        $parameters['profile_end'] = '</a>';

        return $parameters;
    }
}
