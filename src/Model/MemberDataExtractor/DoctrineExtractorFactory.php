<?php

namespace App\Model\MemberDataExtractor;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Twig\Environment;

final class DoctrineExtractorFactory
{
    private $entrypointLookup;
    private $environment;
    private $registry;

    public function __construct(EntrypointLookup $entrypointLookup, Environment $environment, ManagerRegistry $registry)
    {
        $this->entrypointLookup = $entrypointLookup;
        $this->environment = $environment;
        $this->registry = $registry;
    }

    public function create(string $className, $memberRelationName, $alias): string
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
