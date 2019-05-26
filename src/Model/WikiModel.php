<?php

namespace App\Model;

use App\Entity\Wiki;
use App\Repository\WikiRepository;
use App\Utilities\ManagerTrait;
use App\Utilities\RoxWikiParserBackend;
use Mike42\Wikitext\WikitextParser;

class WikiModel
{
    use ManagerTrait;

    /** @var RoxWikiParserBackend */
    private $roxWikiParserBackend;

    public function __construct()
    {
        $this->roxWikiParserBackend = new RoxWikiParserBackend($this);
    }

    public function getPage($pageTitle)
    {
        $pageName = $this->getPagename($pageTitle);

        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $this->getManager()->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName);

        return $wikiPage;
    }

    public function getPagename($pageTitle)
    {
        return str_replace(' ', '_', trim($pageTitle));
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
        try {
            // Initialise the Parser
            WikitextParser::init();
            WikitextParser::$backend = $this->roxWikiParserBackend;
            $parser = new WikitextParser($content);
            $result = $parser->result;
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }
}
