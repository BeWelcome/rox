<?php

namespace App\Pagerfanta;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pages a query of a large table (the logs) and counts its rows only up to a limit: an exact count reads every
 * matching row, which takes seconds on a large table. With more rows the count is the limit and isCapped() says so;
 * the pages end there.
 *
 * For queries that join no collection (one row per entity).
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
final class CappedCountAdapter implements AdapterInterface
{
    private ?int $count = null;

    private bool $capped = false;

    public function __construct(private readonly QueryBuilder $query, private readonly int $cap)
    {
    }

    public function getNbResults(): int
    {
        if (null === $this->count) {
            $alias = $this->query->getRootAliases()[0];
            // the ids of at most one row more than the limit, unsorted: stops early instead of counting all rows
            $ids = (clone $this->query)
                ->select("{$alias}.id")
                ->resetDQLPart('orderBy')
                ->setFirstResult(0)
                ->setMaxResults($this->cap + 1)
                ->getQuery()
                ->getSingleColumnResult();
            $this->capped = \count($ids) > $this->cap;
            $this->count = min(\count($ids), $this->cap);
        }

        return $this->count;
    }

    /** Whether there are more rows than getNbResults() tells. */
    public function isCapped(): bool
    {
        $this->getNbResults();

        return $this->capped;
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return (clone $this->query)
            ->setFirstResult($offset)
            ->setMaxResults($length)
            ->getQuery()
            ->getResult();
    }
}
