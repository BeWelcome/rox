<?php

/*
 * Wikitext
 */

namespace Mike42\Wikitext;

/**
 * InterwikiRepository implemented when the source is a json file
 */
class JsonInterwikiRepository implements InterwikiRepository
{

    protected $interwiki = [];

    public function __construct(string $filename)
    {
        $json = file_get_contents($filename);
        /* Unserialize data and load into associative array for easy lookup */
        $arr = json_decode($json);
        foreach ($arr->query->interwikimap as $site) {
            if (isset($site->prefix) && isset($site->url)) {
                $this->interwiki[$site->prefix] = $site->url;
            }
        }
    }

    public function getTargetUrl(string $namespace): string
    {
        return $this->interwiki[$namespace];
    }

    public function hasNamespace(string $ns): bool
    {
        return array_key_exists($ns, $this->interwiki);
    }

}
