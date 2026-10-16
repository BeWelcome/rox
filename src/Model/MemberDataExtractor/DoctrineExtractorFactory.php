<?php

namespace App\Model\MemberDataExtractor;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

final readonly class DoctrineExtractorFactory
{
    public function __construct(private EntrypointLookupInterface $entrypointLookup, private Environment $environment, private ManagerRegistry $registry)
    {
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
