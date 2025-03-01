<?php

/*
 * Wikitext
 */

namespace Mike42\Wikitext;

/**
 * Design Pattern : Null Object for the interface InterwikiRepository
 */
class NullInterwikiRepository implements InterwikiRepository
{

    public function getTargetUrl(string $namespace): string
    {
        throw new \LogicException("Don't call getTargetUrl on a Null Object");
    }

    public function hasNamespace(string $ns): bool
    {
        return false;
    }

}
