<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\NewMember as Member;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

final class DoctrineExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function __construct(
        EntrypointLookupInterface $entrypointLookup,
        Environment $environment,
        ManagerRegistry $registry,
        private readonly string $className,
        private readonly string $memberRelationName,
        private readonly string $alias,
    ) {
        parent::__construct($entrypointLookup, $environment, $registry);
    }

    public function extract(Member $member, string $tempDir): string
    {
        $repository = $this->getRepository($this->className);
        $data = $repository->findBy([$this->memberRelationName => $member]);

        return $this->writePersonalDataFile([$this->alias => $data, 'member' => $member], $this->alias, $tempDir . $this->alias . '.html');
    }
}
