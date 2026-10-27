<?php

namespace App\Pagerfanta;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pages a list of the translation tool (WordRepository::*List()): rows of scalars, one per code of the root alias.
 *
 * Not Pagerfanta's ORM QueryAdapter: Doctrine's paginator can't count Word, whose identifier is code and language.
 *
 * @implements AdapterInterface<array<string, mixed>>
 */
final readonly class WordListAdapter implements AdapterInterface
{
    public function __construct(private QueryBuilder $rows)
    {
    }

    public function getNbResults(): int
    {
        $alias = $this->rows->getRootAliases()[0];

        return (int) (clone $this->rows)
            ->select("COUNT(DISTINCT {$alias}.code)")
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return (clone $this->rows)
            ->setFirstResult($offset)
            ->setMaxResults($length)
            ->getQuery()
            ->getArrayResult();
    }
}
