<?php

namespace App\Pagerfanta;

use AnthonyMartin\GeoLocation\GeoPoint;
use App\Doctrine\CommentQualityType;
use App\Doctrine\MemberStatusType;
use App\Dto\MemberSearchResult;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\MemberPhoto;
use App\Entity\MemberTranslation;
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

            // Only the comments the profile shows (CommentRepository::getCommentsCountMemberByQuality)
            $commentCountQueryBuilder = $this->entityManager->createQueryBuilder()
                ->select('COUNT(commentCountTable.id)')
                ->from('App\Entity\Comment', 'commentCountTable')
                ->join('commentCountTable.fromMember', 'commentCountAuthor')
                ->where('commentCountTable.toMember = m.id AND commentCountTable.showToOtherMembers = 1')
                ->andWhere('commentCountAuthor.status IN (' . $this->commentAuthorStatuses() . ')');

            // The comments by quality too, for the positive / neutral / negative pill of the card
            $qualityCount = fn (string $quality): string => $this->entityManager->createQueryBuilder()
                ->select('COUNT(cq' . $quality . '.id)')
                ->from('App\Entity\Comment', 'cq' . $quality)
                ->join('cq' . $quality . '.fromMember', 'cqa' . $quality)
                ->where('cq' . $quality . '.toMember = m.id AND cq' . $quality . ".quality = '" . $quality . "'")
                ->andWhere('cq' . $quality . '.showToOtherMembers = 1')
                ->andWhere('cqa' . $quality . '.status IN (' . $this->commentAuthorStatuses() . ')')
                ->getDQL();

            $qb->select(
                \sprintf(
                    'NEW %s(m, (%s), (%s), (%s), (%s), (%s), (%s))',
                    MemberSearchResult::class,
                    $senderCountQueryBuilder->getDQL(),
                    $receiverCountQueryBuilder->getDQL(),
                    $commentCountQueryBuilder->getDQL(),
                    $qualityCount(CommentQualityType::POSITIVE),
                    $qualityCount(CommentQualityType::NEUTRAL),
                    $qualityCount(CommentQualityType::NEGATIVE)
                )
            )->setParameter('currentUserId', $this->currentUser->getId());
        } else {
            $qb->select(\sprintf('NEW %s(m, 0, 0, 0, 0, 0, 0)', MemberSearchResult::class));
        }

        // Re-apply sorting to the second query to ensure the order is preserved
        $this->applySorting($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * Every member found (up to $limit), for the markers of the map: the username and where
     * they live, their own point rounded to about 1 km as on the profile, else the centre of
     * their town. Much lighter than a page of results: two small queries, no entities.
     *
     * @return array<int, array{username: string, latitude: float, longitude: float}>
     */
    public function getMapPoints(int $limit): array
    {
        $isDistanceSearch = $this->searchRequest->distance > 0 && $this->searchRequest->location_latitude && $this->searchRequest->location_longitude;

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
        $idQb->setMaxResults($limit);

        $memberIds = array_column($idQb->getQuery()->getScalarResult(), 'id');
        if (empty($memberIds)) {
            return [];
        }

        $rows = $this->entityManager->createQueryBuilder()
            ->select('m.username, m.accommodation, a.latitude AS ownLatitude, a.longitude AS ownLongitude, ST_Y(l.coordinates) AS townLatitude, ST_X(l.coordinates) AS townLongitude')
            ->from(Member::class, 'm')
            ->join('m.addresses', 'a', 'WITH', 'a.active = 1')
            ->leftJoin('a.location', 'l')
            ->where('m.id IN (:memberIds)')
            ->setParameter('memberIds', $memberIds)
            ->getQuery()
            ->getArrayResult();

        $points = [];
        foreach ($rows as $row) {
            if (isset($points[$row['username']])) {
                continue;
            }
            if (null !== $row['ownLatitude'] && null !== $row['ownLongitude']) {
                $latitude = round((float) $row['ownLatitude'], 2);
                $longitude = round((float) $row['ownLongitude'], 2);
            } elseif (null !== $row['townLatitude'] && null !== $row['townLongitude']) {
                $latitude = (float) $row['townLatitude'];
                $longitude = (float) $row['townLongitude'];
            } else {
                continue;
            }
            $points[$row['username']] = [
                'username' => $row['username'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                // yes / no: the ring around the picture on the map
                'hosting' => \in_array($row['accommodation'], ['yes', 'no'], true) ? $row['accommodation'] : null,
            ];
        }

        return array_values($points);
    }

    private function applySearchFilters(QueryBuilder $qb): void
    {
        /** @var MemberRepository $memberRepository */
        $memberRepository = $this->entityManager->getRepository(Member::class);

        $adminUnit = $this->getAdminUnit();
        if (null !== $adminUnit) {
            // A country or a region: everybody living in it, whatever the distance.
            if (!\in_array('a', $qb->getAllAliases(), true)) {
                $qb->join('m.addresses', 'a', 'WITH', 'a.active = 1');
            }
            if (!\in_array('l', $qb->getAllAliases(), true)) {
                $qb->join('a.location', 'l');
            }
            $qb->andWhere('l.countryId = :admin_country')
                ->setParameter('admin_country', $adminUnit->getCountryId());
            if ('ADM1' === $adminUnit->getFeatureCode()) {
                $qb->andWhere('l.admin1Id = :admin_region')
                    ->setParameter('admin_region', $adminUnit->getAdmin1Id());
            }
        } elseif ($this->searchRequest->distance > 0 && $this->searchRequest->location_latitude && $this->searchRequest->location_longitude) {
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

            $qb->andWhere('MBRContains(ST_Envelope(ST_GeomFromText(:lineString, 0)), l.coordinates) = 1')
                ->andWhere('ST_Distance_Sphere(l.coordinates, ST_GeomFromText(:centerPoint, 0)) <= :distanceInMeters')
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

    /**
     * The country or first-level region picked as search place, if that is what was picked.
     */
    private function getAdminUnit(): ?Location
    {
        $geonameId = $this->searchRequest->location_geoname_id ?? null;
        if (empty($this->searchRequest->location_admin_unit ?? false) || empty($geonameId)) {
            return null;
        }

        $location = $this->entityManager->getRepository(Location::class)->find((int) $geonameId);
        if (null === $location) {
            return null;
        }
        $featureCode = (string) $location->getFeatureCode();

        return str_starts_with($featureCode, 'PCL') || 'ADM1' === $featureCode ? $location : null;
    }

    /**
     * The statuses of the members whose comments count, as a DQL list of literals.
     */
    private function commentAuthorStatuses(): string
    {
        return implode(', ', array_map(
            static fn (string $status): string => "'" . $status . "'",
            MemberStatusType::MEMBER_COMMENTS_ARRAY
        ));
    }

    /**
     * 1 for a profile picture, 2 for an about me text: 3 is a complete profile.
     */
    private function profileCompletenessDql(): string
    {
        $picture = $this->entityManager->createQueryBuilder()
            ->select('orderPhoto.id')
            ->from(MemberPhoto::class, 'orderPhoto')
            ->where('orderPhoto.member = m')
            ->getDQL();
        $aboutMe = $this->entityManager->createQueryBuilder()
            ->select('orderAbout.id')
            ->from(MemberTranslation::class, 'orderAbout')
            ->where("orderAbout.object = m AND orderAbout.field = 'aboutMe' AND orderAbout.content <> ''")
            ->getDQL();

        return \sprintf(
            '(CASE WHEN EXISTS (%s) THEN 1 ELSE 0 END) + (CASE WHEN EXISTS (%s) THEN 2 ELSE 0 END)',
            $picture,
            $aboutMe
        );
    }

    private function applySorting(QueryBuilder $qb): void
    {
        $direction = (MemberRepository::DIRECTION_ASCENDING === $this->searchRequest->direction) ? 'ASC' : 'DESC';

        switch ($this->searchRequest->order) {
            case MemberRepository::ORDER_USERNAME:
                $qb->orderBy('m.username', $direction);
                break;
            case MemberRepository::ORDER_ACCOMMODATION:
                // As on main: hosts first, then the fuller profiles (picture, about me), then
                // the most willing to host, then the most recently active.
                $qb->orderBy('m.accommodation', $direction)
                    ->addOrderBy($this->profileCompletenessDql(), $direction)
                    ->addOrderBy('m.hostingInterest', $direction)
                    ->addOrderBy('m.lastActive', $direction);
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
