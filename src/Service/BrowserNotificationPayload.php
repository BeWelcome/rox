<?php

namespace App\Service;

use App\Entity\Member;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class BrowserNotificationPayload
{
    public function __construct(
        private string $type,
        private string $titleKey,
        private array $titleParameters,
        private string $url,
        private ?string $senderUsername = null,
    ) {
    }

    public static function message(Member $sender, string $url): self
    {
        return self::withSender('message', 'browser.notification.message.title', $sender, $url);
    }

    public static function request(Member $sender, string $url): self
    {
        return self::withSender('request', 'browser.notification.request.title', $sender, $url);
    }

    public static function invitation(Member $sender, string $url): self
    {
        return self::withSender('invitation', 'browser.notification.invitation.title', $sender, $url);
    }

    public static function forum(Member $sender, string $url): self
    {
        return self::withSender('forum', 'browser.notification.forum.title', $sender, $url);
    }

    public static function fromStored(string $type, ?string $senderUsername, string $url): self
    {
        $titleKey = match ($type) {
            'request' => 'browser.notification.request.title',
            'invitation' => 'browser.notification.invitation.title',
            'forum' => 'browser.notification.forum.title',
            default => 'browser.notification.message.title',
        };
        $parameters = ['username' => $senderUsername ?? 'BeWelcome'];

        return new self($type, $titleKey, $parameters, $url, $senderUsername);
    }

    public function toMessage(TranslatorInterface $translator, string $locale): BrowserNotificationMessage
    {
        return new BrowserNotificationMessage(
            $this->type,
            $translator->trans($this->titleKey, $this->titleParameters, locale: $locale),
            $translator->trans('browser.notification.body', locale: $locale),
            $this->url
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSenderUsername(): ?string
    {
        return $this->senderUsername;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    private static function withSender(string $type, string $titleKey, Member $sender, string $url): self
    {
        return new self($type, $titleKey, ['username' => $sender->getUsername()], $url, $sender->getUsername());
    }
}
