<?php

namespace App\Pagerfanta;

use AnthonyMartin\GeoLocation\GeoPoint;
use App\Dto\MemberSearchResult;
use App\Entity\Location;
use App\Entity\Member;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;

class SearchAdapter implements AdapterInterface
{
    public function __construct(
        private readonly SearchFormRequest $searchRequest,
        private readonly EntityManagerInterface $entityManager,
        private readonly Member $currentUser,
    ) {
    }

    public function getNbResults(): int
    {
        $isDistanceSearch = $this->searchRequest->distance > 0 && $this->searchRequest->location_latitude && $this->searchRequest->location_longitude;

        $qb = $this->entityManager->createQueryBuilder();

        if ($isDistanceSearch) {
            $qb->select('COUNT(DISTINCT m.id)')
                ->from(Location::class, 'l')
                ->join('App\Entity\Address', 'a', 'WITH', 'a.location = l.geonameId AND a.active = 1')
                ->join('a.member', 'm');
        } else {
            $qb->select('COUNT(DISTINCT m.id)')
                ->from(Member::class, 'm');
        }

        $this->applySearchFilters($qb);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        $isDistanceSearch = $this->searchRequest->distance > 0 && $this->searchRequest->location_latitude && $this->searchRequest->location_longitude;

        // 1. Fetch only IDs to avoid running expensive subqueries and fetching all columns for discarded rows
        $idQb = $this->entityManager->createQueryBuilder();
        $idQb->select('DISTINCT m.id');

        if ($isDistanceSearch) {
            $idQb->from(Location::class, 'l')
                ->join('App\Entity\Address', 'a', 'WITH', 'a.location = l.geonameId AND a.active = 1')
                ->join('a.member', 'm');
        } else {
            $idQb->from(Member::class, 'm');
        }

        $this->applySearchFilters($idQb);
        $this->applySorting($idQb);

        $idQb->setFirstResult($offset)
            ->setMaxResults($length);

        $memberIds = array_column($idQb->getQuery()->getScalarResult(), 'id');

        if (empty($memberIds)) {
            return [];
        }

        // 2. Fetch full data for the matched IDs
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(Member::class, 'm')
            ->where('m.id IN (:memberIds)')
            ->setParameter('memberIds', $memberIds);

        if ($this->currentUser) {
            $senderCountQueryBuilder = $this->entityManager->createQueryBuilder()
                ->select('COUNT(msgS.id)')
                ->from('App\Entity\Message', 'msgS')
                ->where('msgS.sender = m.id AND msgS.receiver = :currentUserId');

            $receiverCountQueryBuilder = $this->entityManager->createQueryBuilder()
                ->select('COUNT(msgR.id)')
                ->from('App\Entity\Message', 'msgR')
                ->where('msgR.sender = :currentUserId AND msgR.receiver = m.id');

            $commentCountQueryBuilder = $this->entityManager->createQueryBuilder()
                ->select('COUNT(commentCountTable.id)')
                ->from('App\Entity\Comment', 'commentCountTable')
                ->where('commentCountTable.toMember = m.id');

            $qb->select(
                \sprintf(
                    'NEW %s(m, (%s), (%s), (%s))',
                    MemberSearchResult::class,
                    $senderCountQueryBuilder->getDQL(),
                    $receiverCountQueryBuilder->getDQL(),
                    $commentCountQueryBuilder->getDQL()
                )
            )->setParameter('currentUserId', $this->currentUser->getId());
        } else {
            $qb->select(\sprintf('NEW %s(m, 0, 0, 0)', MemberSearchResult::class));
        }

        // Re-apply sorting to the second query to ensure the order is preserved
        $this->applySorting($qb);

        return $qb->getQuery()->getResult();
    }

    private function applySearchFilters(QueryBuilder $qb): void
    {
        /** @var MemberRepository $memberRepository */
        $memberRepository = $this->entityManager->getRepository(Member::class);

        if ($this->searchRequest->distance > 0 && $this->searchRequest->location_latitude && $this->searchRequest->location_longitude) {
            // 1. Calculate the bounding box
            $center = new GeoPoint($this->searchRequest->location_latitude, $this->searchRequest->location_longitude);
            $boundingBox = $center->boundingBox($this->searchRequest->distance, 'km');

            $minLat = $boundingBox->getMinLatitude();
            $maxLat = $boundingBox->getMaxLatitude();
            $minLng = $boundingBox->getMinLongitude();
            $maxLng = $boundingBox->getMaxLongitude();

            // 2. Create a LINESTRING for ST_Envelope to define the bounding box polygon
            $lineString = \sprintf('LINESTRING(%F %F, %F %F)', $minLng, $minLat, $maxLng, $maxLat);

            // Ensure joins are present if not already added by root entity change
            if (!\in_array('a', $qb->getAllAliases(), true)) {
                $qb->join('m.addresses', 'a', 'WITH', 'a.active = 1');
            }
            if (!\in_array('l', $qb->getAllAliases(), true)) {
                $qb->join('a.location', 'l');
            }

            $qb->andWhere('l.latitude BETWEEN :minLat AND :maxLat')
                ->andWhere('l.longitude BETWEEN :minLng AND :maxLng')
                ->andWhere('MBRContains(ST_Envelope(ST_GeomFromText(:lineString, 0)), l.coordinates) = 1')
                ->andWhere('ST_Distance_Sphere(l.coordinates, ST_GeomFromText(:centerPoint, 0)) <= :distanceInMeters')
                ->setParameter('minLat', $minLat)
                ->setParameter('maxLat', $maxLat)
                ->setParameter('minLng', $minLng)
                ->setParameter('maxLng', $maxLng)
                ->setParameter('lineString', $lineString)
                ->setParameter('centerPoint', \sprintf('POINT(%F %F)', $this->searchRequest->location_longitude, $this->searchRequest->location_latitude))
                ->setParameter('distanceInMeters', $this->searchRequest->distance * 1000); // ST_Distance_Sphere uses meters
        } elseif ($this->searchRequest->location_geoname_id) {
            if (!\in_array('a', $qb->getAllAliases(), true)) {
                $qb->join('m.addresses', 'a', 'WITH', 'a.active = 1 AND a.location = :location_id');
            } else {
                $qb->andWhere('a.location = :location_id');
            }
            $qb->setParameter('location_id', $this->searchRequest->location_geoname_id);
        }

        $memberRepository->applySearchFilters($qb, $this->searchRequest);
    }

    private function applySorting(QueryBuilder $qb): void
    {
        $direction = (MemberRepository::DIRECTION_ASCENDING === $this->searchRequest->direction) ? 'ASC' : 'DESC';

        switch ($this->searchRequest->order) {
            case MemberRepository::ORDER_USERNAME:
                $qb->orderBy('m.username', $direction);
                break;
            case MemberRepository::ORDER_ACCOMMODATION:
                $qb->orderBy('m.accommodation', $direction)
                    ->addOrderBy('m.hostingInterest', 'ASC')
                    ->addOrderBy('m.lastActive', 'ASC');
                break;
            case MemberRepository::ORDER_LOGIN:
                $qb->orderBy('m.lastActive', 'ASC' === $direction ? 'DESC' : 'ASC');
                break;
            case MemberRepository::ORDER_MEMBERSHIP:
                $qb->orderBy('m.created', $direction);
                break;
            case MemberRepository::ORDER_COMMENTS:
                if ($this->currentUser) {
                    $commentOrderQb = $this->entityManager->createQueryBuilder()
                        ->select('COUNT(commentOrder.id)')
                        ->from('App\Entity\Comment', 'commentOrder')
                        ->where('commentOrder.toMember = m.id');
                    $qb->orderBy('(' . $commentOrderQb->getDQL() . ')', $direction);
                } else {
                    $qb->orderBy('m.lastActive', 'DESC');
                }
                break;
            case MemberRepository::ORDER_DISTANCE:
                if ($this->searchRequest->location_latitude && $this->searchRequest->location_longitude) {
                    $centerPointWkt = \sprintf('POINT(%F %F)', $this->searchRequest->location_longitude, $this->searchRequest->location_latitude);
                    if (!\in_array('a', $qb->getAllAliases(), true)) {
                        $qb->join('m.addresses', 'a', 'WITH', 'a.active = 1');
                    }
                    if (!\in_array('l', $qb->getAllAliases(), true)) {
                        $qb->join('a.location', 'l');
                    }
                    $qb->addSelect('ST_Distance_Sphere(l.coordinates, ST_GeomFromText(:centerPoint, 0)) AS HIDDEN distance')
                        ->setParameter('centerPoint', $centerPointWkt)
                        ->orderBy('distance', $direction)
                        ->addOrderBy('m.hostingInterest', 'DESC')
                        ->addOrderBy('m.lastActive', 'DESC');
                } else {
                    $qb->orderBy('m.lastActive', 'DESC');
                }
                break;
            default:
                $qb->orderBy('m.lastActive', $direction);
        }
    }
}
