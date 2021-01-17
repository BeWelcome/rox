<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\TripType;
use App\Form\TripTypeB;
use App\Model\TripModel;
use App\Repository\SubtripRepository;
use Carbon\CarbonImmutable;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TripController extends AbstractController
{
    /**
     * @Route("/mytrips/{page}", name="mytrips",
     *     requirements={"page": "\d+"})
     *     )
     */
    public function myTrips(Request $request, TripModel $tripModel, int $page = 1): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $trips = $tripModel->paginateTripsOfMember($member, $page);

        return $this->render('trip/my.html.twig', [
            'trips' => $trips,
            'submenu' => [
                'active' => 'trip_mytrips',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * @Route("/trip/{id}/show", name="trip_show",
     *     requirements={"id": "\d+"})
     */
    public function show(Trip $trip): Response
    {
        return $this->render('trip/show.html.twig', [
            'trip' => $trip,
            'submenu' => [
                'active' => 'trip_show',
                'items' => $this->getSubmenuItems([
                    'trip' => $trip,
                    'show' => true,
                ]),
            ],
        ]);
    }

    /**
     * Create a new trip.
     *
     * @Route("/trip/create/a", name="trip_create_a")
     */
    public function createA(Request $request): Response
    {
        $createForm = $this->createForm(TripType::class);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            /** @var Member $creator */
            $creator = $this->getUser();

            /** @var Trip $data */
            $data = $createForm->getData();
            $data->setCreator($creator);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'trip.created');

            return $this->redirectToRoute('mytrips');
        }

        return $this->render('trip/create_edit_a.html.twig', [
            'create' => true,
            'form' => $createForm->createView(),
            'submenu' => [
                'active' => 'trip_create_a',
                'items' => $this->getSubmenuItems([
                    'create' => true,
                ]),
            ],
        ]);
    }

    /**
     * Create a new trip.
     *
     * @Route("/trip/create/b", name="trip_create_b")
     */
    public function createB(Request $request): Response
    {
        $createForm = $this->createForm(TripTypeB::class);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            /** @var Member $creator */
            $creator = $this->getUser();

            /** @var Trip $data */
            $data = $createForm->getData();

            $trip = new Trip();
            $trip->setCreator($creator);
            $trip->setSummary($data['summary']);
            $trip->setDescription($data['description']);
            $trip->setCountOfTravellers($data['countoftravellers']);
            $trip->setAdditionalInfo('none' /* $data['additionalinfo'] */);

            $departure = CarbonImmutable::instance($data['startdate']);
            $rawSubtrips = $data['subtrips'];
            foreach($rawSubtrips as $rawSubtrip)
            {
                $subtrip = new Subtrip();
                $arrival = clone($departure);
                $subtrip->setArrival($arrival->toDateTime());
                $departure= $arrival->addDays( $rawSubtrip->days);
                $subtrip->setDeparture($departure->toDateTime());
                $subtrip->setOptions($rawSubtrip->options);
                $subtrip->setLocation($rawSubtrip->location);
                $trip->addSubtrip($subtrip);

            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trip);
            $entityManager->flush();

            $this->addFlash('success', 'trip.created');

            return $this->redirectToRoute('mytrips');
        }

        return $this->render('trip/create_edit_b.html.twig', [
            'create' => true,
            'form' => $createForm->createView(),
            'submenu' => [
                'active' => 'trip_create_a',
                'items' => $this->getSubmenuItems([
                    'create' => true,
                ]),
            ],
        ]);
    }

    /**
     * Edit an existing trip.
     *
     * @Route("/trip/{id}/edit", name="trip_edit",
     *     requirements={"id": "\d+"})
     */
    public function edit(Request $request, Trip $trip): Response
    {
        $member = $this->getUser();
        if ($trip->getCreator() !== $member) {
            throw new AccessDeniedException();
        }

        $updateForm = $this->createForm(TripType::class, $trip);

        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trip);
            $entityManager->flush();

            $this->addFlash('success', 'trip.edited');

            return $this->redirectToRoute('mytrips');
        }

        return $this->render('trip/create_edit_a.html.twig', [
            'create' => false,
            'form' => $updateForm->createView(),
            'submenu' => [
                'active' => 'trip_edit',
                'items' => $this->getSubmenuItems([
                    'trip' => $trip,
                    'edit' => true,
                ]),
            ],
        ]);
    }

    /**
     * Remove an existing trip.
     *
     * @Route("/trip/{id}/remove", name="trip_remove",
     *     requirements={"id": "\d+"})
     */
    public function remove(Trip $trip): Response
    {
        return new Response('Älabätsch.');
    }

    /**
     * Show all trip legs that are in the vicinity of a member
     *
     * @Route("/trip/{username}/area/{page}",
     *     requirements={"page"="\d+"},
     *     name="trip_in_area")
     */
    public function tripsInArea(Member $member, $page = 1): Response
    {
        /** @var SubtripRepository $subtripRepository */
        $subtripRepository = $this->getDoctrine()->getRepository(Subtrip::class);

        $legsQuery = $subtripRepository->getLegsInAreaQuery($member);
        $legsAdapter = new QueryAdapter($legsQuery);
        $tripLegs = new Pagerfanta($legsAdapter);
        $tripLegs->setCurrentPage($page);

        return $this->render('trip/area.html.twig', [
            'legs' => $tripLegs,
            'submenu' => [
                'active' => 'trip_legs',
                'items' => $this->getSubmenuItems([
                    'legs' => true,
                ]),
            ],
        ]);
    }

    private function getSubMenuItems(array $params = null): array
    {
        $submenu = [];
        $submenu['trip_mytrips'] = [
            'key' => 'mytrips',
            'url' => $this->generateUrl('mytrips'),
        ];
        /** @var Trip $trip */
        $trip = $params['trip'] ?? null;
        $show = $params['show'] ?? false;
        $edit = $params['edit'] ?? false;
        if (null !== $trip) {
            if ($edit) {
                $submenu['trip_edit'] = [
                    'key' => 'trip.edit',
                    'url' => $this->generateUrl('trip_edit', ['id' => $trip->getId()]),
                ];
            }
            if ($show) {
                $submenu['trip_show'] = [
                    'key' => 'trip.show',
                    'url' => $this->generateUrl('trip_show', ['id' => $trip->getId()]),
                ];
            }
        }
        $legs = $params['legs'] ?? false;
        if ($legs) {
            $submenu['trip_legs'] = [
                'key' => 'trip.in.area',
                'url' => $this->generateUrl('trip_in_area', ['username' => $this->getUser()->getUsername()]),
            ];
        }
        $submenu['trip_create_a'] = [
            'key' => 'trip.create.a',
            'url' => $this->generateUrl('trip_create_a'),
        ];
        $submenu['trip_create_b'] = [
            'key' => 'trip.create.b',
            'url' => $this->generateUrl('trip_create_b'),
        ];

        return $submenu;
    }
}
