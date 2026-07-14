<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\FeedbackCategory;
use App\Entity\Friend;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Subtrip;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 *
 * \todo split into different responsibilities instead of clubbing all into the same mailer service.
 */
class Mailer
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly MailerInterface $mailer,
        private readonly string $noReplyEmailAddress = 'noreply@bewelcome.org',
        private readonly string $messageEmailAddress = 'message@bewelcome.org',
        private readonly string $groupEmailAddress = 'group@bewelcome.org',
        private readonly string $passwordEmailAddress = 'password@bewelcome.org',
        private readonly string $signupEmailAddress = 'signup@bewelcome.org',
        private readonly string $accountFeedbackAddress = 'account@bewelcome.org',
        private readonly string $reminderEmailAddress = 'reminder@bewelcome.org',
        private readonly string $termsOfUseEmailAddress = 'tou@bewelcome.org',
        private readonly string $newsletterEmailAddress = 'newsletter@bewelcome.org',
    ) {
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
            $this->getBeWelcomeAddress($sender, $this->groupEmailAddress),
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendGroupEmail(Member $receiver, string $template, $parameters): bool
    {
        return $this->sendTemplateEmail(
            $this->groupEmailAddress,
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
            $this->passwordEmailAddress,
            $receiver,
            'reset.password',
            $parameters
        );
    }

    public function sendSignupEmail(Member $receiver, string $template, $parameters): bool
    {
        return $this->sendTemplateEmail(
            $this->signupEmailAddress,
            $receiver,
            $template,
            $parameters
        );
    }

    public function sendNewEmailConfirmationEmail(Member $receiver, array $parameters): bool
    {
        $currentLocale = $this->translator->getLocale();
        $this->setTranslatorLocale($receiver);
        $parameters['receiver'] = $receiver;
        $parameters['receiverLocale'] = $receiver->getLocale();

        try {
            return $this->sendTemplateEmail(
                $this->signupEmailAddress,
                new Address($parameters['email_address'], $receiver->getUsername()),
                'newemail',
                $parameters
            );
        } finally {
            $this->translator->setLocale($currentLocale);
        }
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
        $identificationHeaders = [
            'Message-ID' => $parameters['messageId'],
        ];
        $references = $parameters['previousMessageIds'] ?? [];
        if ($references) {
            $identificationHeaders['In-Reply-To'] = $references[array_key_last($references)];
            $identificationHeaders['References'] = $references;
        }

        return $this->sendTemplateEmail(
            $sender,
            $receiver,
            'notifications',
            $parameters,
            $identificationHeaders
        );
    }

    /**
     * This feeds the feedback given by a user into the OTRS queues.
     */
    public function sendFeedbackEmail($sender, Address $receiver, $parameters): bool
    {
        $parameters['subject'] = "Your feedback in '"
            . str_replace('_', ' ', $parameters['IdCategory']->getName()) . "'";

        return $this->sendTemplateEmail(
            $sender,
            $receiver,
            'feedback',
            $parameters
        );
    }

    /**
     * Send notification for a friendship request.
     */
    public function sendFriendshipNotification(Friend $friend, Member $requester): bool
    {
        $receiver = $friend->getLeft() === $requester ? $friend->getRight() : $friend->getLeft();
        $parameters = [];
        $parameters['sender'] = $requester;
        $parameters['receiver'] = $receiver;
        $parameters['subject'] = [
            'translationId' => 'email.subject.friendship',
            'parameters' => [
                'username' => $requester->getUsername(),
            ],
        ];

        return $this->sendTemplateEmail(
            $this->getBeWelcomeAddress($requester, $this->noReplyEmailAddress),
            $receiver,
            'friendship.request',
            $parameters
        );
    }

    public function sendTripNotificationEmail(Member $receiver, Subtrip $leg): bool
    {
        $trip = $leg->getTrip();
        $sender = $trip->getCreator();

        return $this->sendTemplateEmail(
            $this->getBeWelcomeAddress($sender, $this->noReplyEmailAddress),
            $receiver,
            'trip.notification.new',
            [
                'sender' => $sender,
                'subject' => [
                    'translationId' => 'trip.notification.new.subject',
                    'parameters' => [
                        'username' => $sender->getUsername(),
                    ],
                ],
                'leg' => $leg,
                'trip' => $trip,
            ]
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
            new Address($this->noReplyEmailAddress, 'BeWelcome'),
            $guest,
            $template,
            $parameters
        );
    }

    public function sendCommentReminderToHost(Member $guest, Member $host): bool
    {
        $parameters = $this->getParametersForCommentReminder($guest, $host, 'comment.reminder.host.subject', $guest);

        return $this->sendTemplateEmail(
            new Address($this->noReplyEmailAddress, 'BeWelcome'),
            $host,
            'comment.reminder.host',
            $parameters
        );
    }

    public function sendProfileDeletionFeedback(Member $retiree, string $body): bool
    {
        return $this->sendTemplateEmail(
            $this->getBeWelcomeAddress($retiree, $this->noReplyEmailAddress),
            new Address($this->accountFeedbackAddress, 'BeWelcome'),
            'profile.delete.feedback',
            [
                'subject' => 'profile.delete.feedback',
                'member' => $retiree,
                'body' => $body,
            ]
        );
    }

    private function sendCommentTemplateEmail(Comment $comment, string $template, array $parameters): bool
    {
        $parameters['sender'] = $comment->getFromMember();
        $parameters['receiver'] = $comment->getToMember();
        $parameters['comment'] = $comment;

        return $this->sendTemplateEmail(
            $this->getBeWelcomeAddress($comment->getFromMember(), $this->noReplyEmailAddress),
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
        return new Address($this->messageEmailAddress, $sender->getUsername() . ' [BeWelcome]');
    }

    /**
     * Used for all notifications except messages and requests notifications to allow recipients to distinguish between
     * those notifications.
     */
    private function getBeWelcomeAddress(Member $sender, $email): Address
    {
        return new Address($email, 'BeWelcome - ' . $sender->getUsername());
    }

    /**
     * @param Member|Address|string $sender
     * @param Member|Address        $receiver
     * @param mixed                 $parameters
     */
    private function sendTemplateEmail(
        $sender,
        $receiver,
        string $template,
        array $parameters,
        array $identificationHeaders = [],
    ): bool {
        $currentLocale = $this->translator->getLocale();
        $success = true;
        $locale = $parameters['receiverLocale'] ?? 'en';
        if ($receiver instanceof Member) {
            $this->setTranslatorLocale($receiver);
            $locale = $receiver->getLocale();
            $parameters['receiver'] = $receiver;
            $receiver = new Address($receiver->getEmail(), $receiver->getUsername());
        } elseif (!$receiver instanceof Address) {
            $message = \sprintf('$receiver must be an instance of %s or %s.', Member::class, Address::class);
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
        $email = new TemplatedEmail()
            ->to($receiver)
            ->subject($subject)
            ->htmlTemplate('emails/' . $template . '.html.twig')
            ->context($parameters);

        if (isset($parameters['datesent'])) {
            $email->date($parameters['datesent']);
        }

        foreach ($identificationHeaders as $name => $ids) {
            $email->getHeaders()->addIdHeader($name, $ids);
        }

        if (!\is_string($sender) && !$sender instanceof Address) {
            $sender = $email->from($this->getBeWelcomeAddressWithUsername($sender));
        }
        $email->from($sender);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            $success = false;
        }
        $this->translator->setLocale($currentLocale);

        return $success;
    }

    /**
     * Make sure to send the email notification in the preferred language of the user.
     */
    private function setTranslatorLocale(Member $receiver): void
    {
        // make sure Member object is fully loaded
        if ($receiver instanceof \Doctrine\Persistence\Proxy && !$receiver->__isInitialized()) {
            $receiver->__load();
        }

        $this->translator->setLocale($receiver->getLocale());
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
        $parameters['language'] = $receiver->getLocale();

        return $parameters;
    }

    private function determineSenderForNewsletter($type): Address
    {
        $sender = match ($type) {
            'RemindToLog', 'MailToConfirmReminder', Newsletter::SUSPENSION_NOTIFICATION => new Address($this->reminderEmailAddress, 'BeWelcome'),
            Newsletter::TERMS_OF_USE => new Address($this->termsOfUseEmailAddress, 'BeWelcome'),
            default => new Address($this->newsletterEmailAddress, 'BeWelcome'),
        };

        return $sender;
    }

    private function getAddCommentATag(Member $member): string
    {
        $url = $this->urlGenerator->generate(
            'add_comment',
            ['username' => $member->getUsername()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return \sprintf('<a href="%s">', $url);
    }

    private function getProfileATag(Member $member): string
    {
        $url = $this->urlGenerator->generate(
            'members_profile',
            ['username' => $member->getUsername()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return \sprintf('<a href="%s">', $url);
    }

    private function getReportProfileATag(Member $member): string
    {
        $url = $this->urlGenerator->generate(
            'feedback',
            ['IdCategory' => 2, 'username' => $member->getUsername()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return \sprintf('<a href="%s">', $url);
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

        $parameters['report_start'] = $this->getReportProfileATag($for);
        $parameters['report_end'] = '</a>';

        return $parameters;
    }
}
