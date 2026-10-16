<?php

/**
 * Methods from this class are called as different types of markup are encountered,
 * and are expected to provide supporting functions like template substitutions,
 * link destinations, and other installation-specific oddities
 */

namespace Mike42\Wikitext;

/**
 * Concrete html renderer example
 */
class DefaultParserBackend extends HtmlRenderer
{

    /**
     * @inheritDoc
     */
    public function getInternalLinkInfo($info): array
    {
        return $info;
    }

    public function getImageInfo($info): array
    {
        return $info;
    }

}
