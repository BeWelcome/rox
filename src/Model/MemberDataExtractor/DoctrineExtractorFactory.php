<?php

namespace App\Model\MemberDataExtractor;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

final class DoctrineExtractorFactory
{
    private $entrypointLookup;
    private $environment;
    private $registry;

    public function __construct(EntrypointLookupInterface $entrypointLookup, Environment $environment, ManagerRegistry $registry)
    {
        $this->entrypointLookup = $entrypointLookup;
        $this->environment = $environment;
        $this->registry = $registry;
    }

    public function create(string $className, $memberRelationName, $alias): DoctrineExtractor
    {
        return new DoctrineExtractor(
            $this->entrypointLookup,
            $this->environment,
            $this->registry,
            $className,
            $memberRelationName,
            $alias
        );
    }
}
