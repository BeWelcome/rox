<?php

/*
 * Wikitext
 */

namespace Mike42\Wikitext;

/**
 * Abstract repository for Interwiki configuration
 */
interface InterwikiRepository
{

    /**
     * Does the namespace $ns exist in this repository ?
     * @param string $ns
     * @return bool
     */
    public function hasNamespace(string $ns): bool;

    /**
     * Gets the template for the target url
     * @param string $namespace
     * @return string an URL with '$1' as a placeholder for the document title
     */
    public function getTargetUrl(string $namespace): string;
}
