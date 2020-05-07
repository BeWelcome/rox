<?php

namespace App\Utilities;

use HTMLPurifier;
use HTMLPurifier_HTML5Config;
use HTMLPurifier_TagTransform_Simple;

class CustomHTMLPurifier extends HTMLPurifier
{
    /**
     * CustomPurifier constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct()
    {
        $config = HTMLPurifier_HTML5Config::createDefault();
        $config->set('HTML.Allowed', 'p,b,a[href],br,i,u,strong,em,ol,ul,li,dl,dt,dd,img[src|alt|width|height],blockquote,del,figure[class],figcaption');
        $config->set('HTML.TargetBlank', true);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('AutoFormat.AutoParagraph', true); // automatically turn double newlines into paragraphs
        $config->set('AutoFormat.Linkify', true); // automatically turn stuff like http://domain.com into links

        // tag transformation
        $def = $config->maybeGetRawHTMLDefinition();
        $def->info_tag_transform['strike'] = new HTMLPurifier_TagTransform_Simple('s');

        parent::__construct($config);
    }
}
