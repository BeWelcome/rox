<?php

use Rox\Tools\RoxMigration;
use Symfony\Component\Debug\Debug;

class MigrateOldTrips extends RoxMigration
{
    public function up()
    {
        Debug::enable();
        $kernel = new AppKernel('dev', true);
        $kernel->boot();

        $container = $kernel->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        // Migrate as much as possible of the old trips
        // this needs to be done in code to avoid missing geonameID, or locations like mountains or countries
        $trips = $this->fetchAll('
            SELECT
                b.IdMember,
                t.trip_id,
                td.trip_name,
                td.trip_descr,
                bd.blog_start,
                bd.blog_end,
                bd.blog_geonameId
            FROM
                blog b
            LEFT JOIN trip t ON b.trip_id_foreign = t.trip_id
            LEFT JOIN trip_data td ON t.trip_id = td.trip_id
            LEFT JOIN blog_data bd ON b.blog_id = bd.blog_id
            WHERE
                NOT (trip_id_foreign IS NULL)
                AND NOT (bd.blog_geonameID IS NULL)
                AND NOT(bd.blog_start IS NULL AND bd.blog_end IS NULL)
            ORDER BY
                b.trip_id_foreign, b.blog_id, bd.blog_start, bd.blog_end
        ');

        $lastTrip = -1;
        $trip = null;
        $memberRepository = $em->getRepository(\AppBundle\Entity\Member::class);

        foreach ($trips as $tripRaw) {
            $curTrip = $tripRaw["trip_id"];
            if ($lastTrip <> $curTrip) {
                $trip = new \AppBundle\Entity\Trip();
                $trip->setSummary($tripRaw["trip_name"]);
                $trip->setDescription($tripRaw["trip_descr"]);
                $createdBy = $memberRepository->find($tripRaw["IdMember"]);
                $trip->setCreatedBy( $createdBy );
                $trip->setCreatedAt( new \DateTime($tripRaw["blog_start"]));
                $trip->setUpdatedAt( new \DateTime($tripRaw["blog_start"]));
                $trip->setCountoftravellers(1);
                $em->persist($trip);
                $em->flush();
                $lastTrip = $curTrip;
            }
            $subTrip = new \AppBundle\Entity\SubTrip();
            $subTrip->setTrip($trip);
            $subTrip->setGeonameid($tripRaw["blog_geonameId"])
                ->setArrival(new \DateTime($tripRaw["blog_start"]));
            if ($tripRaw["blog_end"]) {
                $subTrip->setDeparture(new \DateTime($tripRaw["blog_end"]));
            }
            $em->persist($subTrip);
            $em->persist($trip);
            $em->flush();
        }
    }

    public function down()
    {
        // Nothing to be done here
    }
}
