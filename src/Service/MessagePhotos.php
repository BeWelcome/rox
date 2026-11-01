<?php

namespace App\Service;

use Twig\Attribute\AsTwigFunction;

/**
 * The photos of a message (uploaded with the camera of the message forms, or with the old editor) apart from
 * its text, so the conversation shows them as pictures next to the text bubble (conversations/_thread.html.twig).
 */
class MessagePhotos
{
    private const string UPLOADED_IMAGE = '#<img\b[^>]*\bsrc="([^"]*/gallery/show/uploaded/[^"]*)"[^>]*>#i';

    /**
     * @param string $html the message, already purified
     *
     * @return array{photos: list<string>, text: string} the photos' addresses and the rest of the message
     */
    #[AsTwigFunction('message_photos')]
    public function split(string $html): array
    {
        if (!preg_match_all(self::UPLOADED_IMAGE, $html, $matches)) {
            return ['photos' => [], 'text' => $html];
        }

        $text = preg_replace(self::UPLOADED_IMAGE, '', $html);
        // The paragraphs and figures that only held a photo
        $text = preg_replace('#<(p|figure)\b[^>]*>(\s|&nbsp;|<br\s*/?>)*</\1>#i', '', $text);

        return [
            'photos' => array_map(static fn (string $src): string => html_entity_decode($src, \ENT_QUOTES | \ENT_HTML5, 'UTF-8'), $matches[1]),
            'text' => trim($text),
        ];
    }
}
