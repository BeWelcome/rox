<?php

namespace App\Utilities;

use App\Entity\Wiki;
use App\Model\WikiModel;
use Mike42\Wikitext\DefaultParserBackend;

class RoxWikiParserBackend extends DefaultParserBackend
{
    use ManagerTrait;

    /**
     * @var WikiModel
     */
    private $wikiModel;

    /**
     * RoxWikiParserBackend constructor.
     *
     * @param WikiModel $wikiModel
     */
    public function __construct(WikiModel $wikiModel)
    {
        $this->wikiModel = $wikiModel;
    }

    /**
     * Take people to the right place.
     *
     * Check if given url contains a protocol. If $info['url'] contains :// leave it as is
     * If not make sure it is a link to a wiki page.
     *
     * If it is an internal link check if it exists and set flag accordingly.
     *
     * @param array $info
     *
     * @return array
     */
    public function getInternalLinkInfo($info)
    {
        // if we have an external link just return
        if (false !== strpos($info['url'], '://')) {
            return $info;
        }

        $title = $this->wikiModel->getPagename($info['title']);
        $repository = $this->em->getRepository(Wiki::class);

        $wikiPage = $repository->findBy(['pagename' => $title]);
        $info['exists'] = ($wikiPage) ? true : false;

        if ($info['exists']) {
            $info['url'] = '/wiki/'.$info['url'];
        } else {
            $info['url'] = '/wiki/'.$info['url'].'/create';
        }

        return $info;
    }
}
