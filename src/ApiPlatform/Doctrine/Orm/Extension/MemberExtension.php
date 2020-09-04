<?php

declare(strict_types=1);

namespace App\ApiPlatform\Doctrine\Orm\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Doctrine\ORM\QueryBuilder;

final class MemberExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $this->apply($queryBuilder, $resourceClass);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = []): void
    {
        $this->apply($queryBuilder, $resourceClass);
    }

    private function apply(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Member::class !== $resourceClass) {
            return;
        }

        $queryBuilder->andWhere('o.status NOT IN (:statuses)')->setParameter('statuses', [
            MemberStatusType::TAKEN_OUT,
            MemberStatusType::SUSPENDED,
            MemberStatusType::ASKED_TO_LEAVE,
            MemberStatusType::BUGGY,
            MemberStatusType::BANNED,
            MemberStatusType::REJECTED,
            MemberStatusType::DUPLICATE_SIGNED,
        ]);
    }
}
