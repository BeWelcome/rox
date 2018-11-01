<?php

namespace App\Pagerfanta;

use Pagerfanta\View\Template\TwitterBootstrap3Template;

/**
 * TwitterBootstrap4Template.

 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class TwitterBootstrap4Template extends TwitterBootstrap3Template
{
    protected static $defaultOptions = [
        'prev_message' => '&laquo;',
        'next_message' => '&raquo;',
        'dots_message' => '&hellip;',
        'active_suffix' => '',
        'css_container_class' => 'pagination',
        'css_prev_class' => 'prev',
        'css_next_class' => 'next',
        'css_disabled_class' => 'disabled',
        'css_dots_class' => 'disabled',
        'css_active_class' => 'active',
        'rel_previous' => 'prev',
        'rel_next' => 'next',
    ];

    protected function linkLi($class, $href, $text, $rel = null)
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));
        $rel = $rel ? sprintf(' rel="%s"', $rel) : '';

        return sprintf('<li class="%s"><a class="page-link" href="%s"%s>%s</a></li>', $liClass, $href, $rel, $text);
    }

    protected function spanLi($class, $text)
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));

        return sprintf('<li class="%s"><span class="page-link">%s</span></li>', $liClass, $text);
    }
}
