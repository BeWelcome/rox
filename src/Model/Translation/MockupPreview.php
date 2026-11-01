<?php

namespace App\Model\Translation;

use Dom\HTMLDocument;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

/**
 * A mail or a page as members get it, for the translators' previews (MockupController): the template rendered whole
 * (a mail with its layout and inlined styles, a page with the site's layout), then the part the tool page shows: a
 * page's main content, a mail's body and styles, and the stylesheets the page adds to the site's. Rendered in the
 * tool's request, its texts are listed for translation below the page (InPlaceTranslations).
 */
final readonly class MockupPreview
{
    public function __construct(
        private Environment $twig,
        private EntrypointLookupInterface $entrypoints,
    ) {
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @return array{html: string, stylesheets: list<string>, styles: string} stylesheets: the hrefs of the stylesheets
     *                                                                        the page adds; styles: its style elements (a mail's, for media queries)
     */
    public function render(string $template, array $variables): array
    {
        // as on the live site: mocks don't have every field their templates read, which shows as empty there
        $strict = $this->twig->isStrictVariables();
        $this->twig->disableStrictVariables();
        try {
            $html = $this->twig->render($template, $variables);
        } finally {
            if ($strict) {
                $this->twig->enableStrictVariables();
            }
            // Encore gives each file once per request: the tool page needs the site's again
            $this->entrypoints->reset();
        }
        $site = array_merge($this->entrypoints->getCssFiles('bewelcome'), $this->entrypoints->getCssFiles('tailwind'));
        $this->entrypoints->reset();

        $document = HTMLDocument::createFromString($html, \LIBXML_NOERROR);
        $stylesheets = [];
        foreach ($document->querySelectorAll('head link[rel~="stylesheet"][href]') as $link) {
            $href = (string) $link->getAttribute('href');
            if (!\in_array($href, $site, true)) {
                $stylesheets[] = $href;
            }
        }

        $styles = '';
        foreach ($document->querySelectorAll('head style') as $style) {
            $styles .= $style->textContent;
        }

        // a page without the site's menu and footer; a mail (or a part of a page) as its body
        $part = $document->querySelector('main') ?? $document->body;

        return [
            'html' => $part?->innerHTML ?? '',
            'stylesheets' => array_values(array_unique($stylesheets)),
            'styles' => $styles,
        ];
    }
}
