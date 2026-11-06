<?php

namespace App\Service;

use Dom\Element;
use Dom\HTMLDocument;
use Twig\Attribute\AsTwigFunction;

/**
 * The "On this page" list of a policy (templates/policies/_layout.html.twig), read from its rendered text, so that
 * translators never keep one up to date. The English and French texts are sections with an id, headed by an h2 (or
 * none: the introduction). Translated texts arrive as HTML from the translations, usually with h3 headings; those
 * headings get an id (section-1, section-2, …) where they have none.
 */
final class PolicyToc
{
    /**
     * @return array{html: string, items: list<array{id: string, label: string}>} the text, with ids where needed, and
     *                                                                            the entries of the list in order
     */
    #[AsTwigFunction('policy_toc')]
    public function build(string $html, string $introduction): array
    {
        $document = HTMLDocument::createFromString('<!DOCTYPE html><html><body>' . $html . '</body></html>', \LIBXML_NOERROR);
        $body = $document->body;
        if (null === $body) {
            return ['html' => $html, 'items' => []];
        }

        $items = [];
        $sections = array_filter(iterator_to_array($body->children), static fn (Element $element): bool => 'SECTION' === $element->tagName && '' !== $element->id);
        if ([] !== $sections) {
            foreach ($sections as $section) {
                $heading = $section->querySelector('h2');
                $label = null === $heading ? $introduction : $this->text($heading);
                $items[] = ['id' => $section->id, 'label' => $label];
            }

            return ['html' => $html, 'items' => $items];
        }

        $changed = false;
        $number = 0;
        foreach ($body->children as $element) {
            if (!\in_array($element->tagName, ['H2', 'H3'], true)) {
                continue;
            }
            $label = $this->text($element);
            if ('' === $label) {
                continue;
            }
            if ('' === $element->id) {
                $element->id = 'section-' . ++$number;
                $changed = true;
            }
            $items[] = ['id' => $element->id, 'label' => $label];
        }

        return ['html' => $changed ? $body->innerHTML : $html, 'items' => $items];
    }

    private function text(Element $element): string
    {
        return trim(preg_replace('/\s+/u', ' ', $element->textContent ?? '') ?? '');
    }
}
