<?php

namespace App\Utilities;

class ForumUtilities
{
    function RemoveFQDN(string $postContent)
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
            $postContent
        );

        return $postContent;
    }
}
