<?php

namespace App\Service;

final readonly class BrowserNotificationMessage
{
    public function __construct(
        private string $type,
        private string $title,
        private string $body,
        private string $url,
    ) {
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), \JSON_THROW_ON_ERROR);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
        ];
    }
}
