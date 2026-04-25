<?php

namespace App\Utilities;

class ForumUtilities
{
    /**
     * Wraps images inside <figure> elements with an anchor so ekko-lightbox
     * can open them. Adds data-toggle="lightbox" to the wrapping <a>.
     *
     * Input:  <figure class="image"><img src="/url" ...></figure>
     * Output: <figure class="image"><a href="/url" data-toggle="lightbox"
     *                 data-type="image"><img src="/url" ...></a></figure>
     */
    public static function addLightboxToFigures(string $html): string
    {
        if (empty($html) || !str_contains($html, '<figure')) {
            return $html;
        }

        return preg_replace_callback(
            '/<figure([^>]*)>(<img[^>]*src="([^"]*)"[^>]*\/?>)(.*?)<\/figure>/si',
            static function (array $m): string {
                // Skip if already wrapped in an anchor or already has lightbox
                if (str_contains($m[2], 'data-toggle="lightbox"') || str_contains($m[0], '<a ')) {
                    return $m[0];
                }

                return '<figure' . $m[1] . '>'
                    . '<a href="' . $m[3] . '" data-toggle="lightbox" data-type="image">'
                    . $m[2]
                    . '</a>'
                    . $m[4]
                    . '</figure>';
            },
            $html
        );
    }

    public function removeFqdn(string $postContent)
    {
        // Replace href in links
        $postContent = preg_replace(
            '/href="http[s]?:\/\/(www|beta)\.bewelcome\.org\//i',
            'href="/',
            $postContent
        );

        // Replace src in images (and sets)
        $postContent = preg_replace(
            '/src="http[s]?:\/\/(www|beta)\.bewelcome\.org\//i',
            'src="/',
            (string) $postContent
        );

        return $postContent;
    }
}
