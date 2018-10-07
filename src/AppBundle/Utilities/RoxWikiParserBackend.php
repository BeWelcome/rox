<?php

namespace AppBundle\Utilities;

use Mike42\Wikitext\DefaultParserBackend;

class RoxWikiParserBackend extends DefaultParserBackend
{
    /**
     * Take people to the right place.
     *
     * Check if given url contains a protocol. If $info['url'] contains :// leave it as is
     * If not make sure it is a link to a wiki page.
     *
     * @param array $info
     *
     * @return array
     */
    public function getInternalLinkInfo($info)
    {
        if (false === strpos($info['url'], '://')) {
            $info['url'] = '/wiki/'.$info['url'];
        }

        return $info;
    }
}
