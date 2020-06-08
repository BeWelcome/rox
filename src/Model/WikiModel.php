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

    /**
     * @required
     */
    public function setWikiParserBackend(RoxWikiParserBackend $wikiParserBackend)
    {
        $this->roxWikiParserBackend = $wikiParserBackend;
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
        if ('Group ' === substr($pageTitle, 0, 6)) {
            $pageTitle = str_replace('Group ', 'Group_', trim($pageTitle));
        }

        if ('Group_' === substr($pageTitle, 0, 6)) {
            return str_replace(' ', '', trim($pageTitle));
        }

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

    public function createWikiPage($pageTitle, $wikiMarkup)
    {
        $newWikiPage = new Wiki();
        $newWikiPage->setPagename($this->getPagename($pageTitle));
        $newWikiPage->setVersion(1);
        $newWikiPage->setContent($wikiMarkup);
        $em = $this->getManager();
        $em->persist($newWikiPage);
        $em->flush();

        return $newWikiPage;
    }

    public function addNewVersion($wikiPage, $wikiMarkup)
    {
        $newWikiPage = clone $wikiPage;
        $newWikiPage->setContent($wikiMarkup);
        // \todo make this safe against multiple edits at the same time
        $newWikiPage->setVersion($wikiPage->getVersion() + 1);
        $em = $this->getManager();
        $em->persist($newWikiPage);
        $em->flush();

        return $newWikiPage;
    }
}
