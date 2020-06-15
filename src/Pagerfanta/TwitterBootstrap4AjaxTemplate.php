<?php

namespace App\Pagerfanta;

/**
 * TwitterBootstrap4Template.
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class TwitterBootstrap4AjaxTemplate extends TwitterBootstrap4Template
{
    protected function linkLi($class, $href, $text, $rel = null): string
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));
        $rel = $rel ? sprintf(' rel="%s"', $rel) : '';

        return sprintf(
            '<li class="%s"><a class="page-link ajaxload" href="%s"%s>%s</a></li>',
            $liClass,
            $href,
            $rel,
            $text
        );
    }

    protected function spanLi($class, $text): string
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));

        return sprintf('<li class="%s"><span class="page-link">%s</span></li>', $liClass, $text);
    }
}
