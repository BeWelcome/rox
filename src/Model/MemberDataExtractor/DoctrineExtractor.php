<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

final class DoctrineExtractor extends AbstractExtractor implements ExtractorInterface
{
    private $className;
    private $memberRelationName;
    private $alias;

    public function __construct(
        EntrypointLookupInterface $entrypointLookup,
        Environment $environment,
        ManagerRegistry $registry,
        string $className,
        string $memberRelationName,
        string $alias
    ) {
        parent::__construct($entrypointLookup, $environment, $registry);
        $this->className = $className;
        $this->memberRelationName = $memberRelationName;
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        $repository = $this->getRepository($this->className);
        $data = $repository->findBy([$this->memberRelationName => $member]);

        return $this->writePersonalDataFile([$this->alias => $data, 'member' => $member], $this->alias, $tempDir . $this->alias . '.html');
    }
}
