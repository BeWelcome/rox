<?php

namespace App\Model;

use App\Entity\Wiki;
use App\Repository\WikiRepository;
use App\Utilities\ManagerTrait;
use App\Utilities\RoxWikiParserBackend;
use Doctrine\ORM\EntityManagerInterface;
use Mike42\Wikitext\DefaultParserBackend;
use Mike42\Wikitext\HtmlRenderer;
use Mike42\Wikitext\WikitextParser;
use Symfony\Contracts\Service\Attribute\Required;

class WikiModel
{
    use ManagerTrait;

    private HtmlRenderer $roxWikiParserBackend;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Required]
    public function setWikiParserBackend(RoxWikiParserBackend $roxWikiParserBackend): void
    {
        $this->roxWikiParserBackend = $roxWikiParserBackend;
    }

    public function getPage($pageTitle, $version = 0): ?Wiki
    {
        $pageName = $this->getPagename($pageTitle);

        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $this->getManager()->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName, $version);

        return $wikiPage;
    }

    public function getPagename($pageTitle): string
    {
        if (str_starts_with((string) $pageTitle, 'Group ')) {
            $pageTitle = str_replace('Group ', 'Group_', trim((string) $pageTitle));
        }

        if (str_starts_with((string) $pageTitle, 'Group_')) {
            return str_replace(' ', '', trim((string) $pageTitle));
        }

        return str_replace(' ', '_', trim((string) $pageTitle));
    }

    /**
     * @param $content
     *
     * @return string
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function parseWikiMarkup($content): ?string
    {
//         try {
            $parser = new WikitextParser($this->roxWikiParserBackend);
            $result = $parser->parse($content);
//        } catch (\Exception $e) {
//            $result = 'Wiki content could not be parsed: ' . $e->getMessage();
//        }

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

    public function getHistory(Wiki $wikiPage)
    {
        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $this->getManager()->getRepository(Wiki::class);

        return $wikiRepository->getHistory($wikiPage);
    }
}
