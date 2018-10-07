<?php

namespace AppBundle\Model;

use AppBundle\Utilities\RoxWikiParserBackend;
use Mike42\Wikitext\WikitextParser;

class WikiModel
{
    public function getPageName($pageTitle)
    {
        return str_replace(' ', '_', $pageTitle);
    }

    /**
     * @param $content
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function parseWikiMarkup($content)
    {
        // Initialise the Parser
        WikitextParser::init();
        WikitextParser::$backend = new RoxWikiParserBackend();

        $parser = new WikitextParser($content);

        return $parser->result;
    }
}
