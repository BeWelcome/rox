<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\TripRadiusType;
use App\Form\TripType;
use App\Model\TripModel;
use App\Repository\SubtripRepository;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TripController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private TripModel $tripModel;

    public function __construct(TripModel $tripModel)
    {
        $this->tripModel = $tripModel;
    }

    /**
     * @Route("/trips/{page}", name="trips",
     *     requirements={"page": "\d+"})
     *     )
     */
    public function trips(int $page = 1): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $trips = $this->tripModel->paginateTripsOfMember($member, $page);

        return $this->render('trip/my.html.twig', [
            'trips' => $trips,
            'submenu' => [
                'active' => 'trip_mytrips',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * @Route("/trip/{id}", name="trip_show",
     *     requirements={"id": "\d+"})
     *
     * @IsGranted("TRIP_VIEW", subject="trip")
     */
    public function show(Trip $trip, TripModel $tripModel): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $searchRadius = $tripModel->getTripsRadius($member);

        return $this->render('trip/show.html.twig', [
            'trip' => $trip,
            'searchRadius' => $searchRadius,
            'expired' => $tripModel->hasTripExpired($trip),
            'submenu' => [
                'active' => 'trip_show',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * Create a new trip.
     *
     * @Route("/new/trip", name="new_trip")
     */
    public function create(Request $request): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $trip = new Trip();
        $trip->setCreator($member);

        $leg = new Subtrip();
        $trip->addSubtrip($leg);

        $createForm = $this->createForm(TripType::class, $trip);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $trip = $createForm->getData();

            $errors = $this->tripModel->checkTripCreateOrEditData($trip);
            if (empty($errors)) {
                $this->tripModel->orderTripLegs($trip);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($trip);
                $entityManager->flush();

                $this->addTranslatedFlash('success', 'trip.created');

                return $this->redirectToRoute('trip_show', ['id' => $trip->getId()]);
            }

            $this->handleErrors($createForm, $errors);
        }

        return $this->render('trip/create_edit.html.twig', [
            'create' => true,
            'form' => $createForm->createView(),
            'submenu' => [
                'active' => 'new_trip',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * Edit an existing trip.
     *
     * @Route("/trip/{id}/edit", name="trip_edit",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("TRIP_EDIT", subject="trip")
     */
    public function edit(Request $request, Trip $trip, EntityManagerInterface $entityManager): Response
    {
        if ($this->tripModel->hasTripExpired($trip)) {
            $this->addTranslatedFlash('notice', 'trip.flash.expired');

            return $this->redirectToRoute('trip_show', ['id' => $trip->getId()]);
        }

        $editForm = $this->createForm(TripType::class, $trip);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $editedTrip = $editForm->getData();

            $errors = $this->tripModel->checkTripCreateOrEditData($editedTrip);
            if (empty($errors)) {
                // \todo Check for deleted legs and take care to remove link to invitation

                $entityManager->persist($editedTrip);
                $entityManager->flush();

                $this->addTranslatedFlash('success', 'trip.edited');

                return $this->redirectToRoute('trip_show', ['id' => $trip->getId()]);
            }

            $this->handleErrors($editForm, $errors);
        }

        return $this->render('trip/create_edit.html.twig', [
            'create' => false,
            'form' => $editForm->createView(),
            'submenu' => [
                'active' => 'trip_edit',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * Remove an existing trip.
     *
     * @Route("/trip/{id}/remove", name="trip_remove",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("TRIP_REMOVE", subject="trip")
     */
    public function remove(Trip $trip): RedirectResponse
    {
        $this->tripModel->hideTrip($trip);

        return $this->redirectToRoute('trips');
    }

    /**
     * Copies an existing trip (keeping all locations and sets dates in the future).
     *
     * @Route("/trip/{id}/copy", name="trip_copy",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("TRIP_COPY", subject="trip")
     */
    public function copy(Trip $trip): Response
    {
        $member = $this->getUser();
        if ($trip->getCreator() !== $member) {
            throw new AccessDeniedException();
        }

        $newTrip = $this->tripModel->copyTrip($trip);

        return $this->redirectToRoute('trip_edit', ['id' => $newTrip->getId()]);
    }

    /**
     * Show all trip legs that are in the vicinity of a member.
     *
     * @Route("/visitors/{page}",
     *     requirements={"page"="\d+"},
     *     name="visitors")
     */
    public function tripsInArea(
        Request $request,
        EntityManagerInterface $entityManager,
        int $page = 1
    ): Response {
        /** @var Member $host */
        $host = $this->getUser();

        $radius = $this->tripModel->getTripsRadius($host);
        $radiusForm = $this->createForm(TripRadiusType::class, [
            'radius' => $radius,
        ]);
        $radiusForm->handleRequest($request);

        if ($radiusForm->isSubmitted() && $radiusForm->isValid()) {
            $data = $radiusForm->getData();
            $newRadius = $data['radius'];
            if ($radius !== $newRadius) {
                $this->tripModel->setTripsRadius($host, $newRadius);
                $radius = $newRadius;
            }
        }

        /** @var SubtripRepository $subtripRepository */
        $subtripRepository = $entityManager->getRepository(Subtrip::class);
        $legsQuery = $subtripRepository->getLegsInAreaQuery($host, $radius);

        $legsAdapter = new QueryAdapter($legsQuery);
        $tripLegs = new Pagerfanta($legsAdapter);
        $tripLegs->setMaxPerPage(10);
        $tripLegs->setCurrentPage($page);

        return $this->render('trip/area.html.twig', [
            'radiusForm' => $radiusForm->createView(),
            'legs' => $tripLegs,
            'submenu' => [
                'active' => 'trip_legs',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    private function getSubMenuItems(): array
    {
        $submenu = [
            'trip_mytrips' => [
                'key' => 'trips.mytrips',
                'url' => $this->generateUrl('trips'),
            ],
            'trip_legs' => [
                'key' => 'trips.visitors',
                'url' => $this->generateUrl('visitors'),
            ],
            'new_trip' => [
                'key' => 'trip.create',
                'url' => $this->generateUrl('new_trip'),
            ],
        ];

        return $submenu;
    }

    private function handleErrors(FormInterface &$form, array $errors)
    {
        foreach ($errors as $error) {
            if (isset($error['leg'])) {
                $form->get('subtrips')->get($error['leg'])->get($error['field'])->addError(
                    new FormError($this->getTranslator()->trans($error['error']))
                );
            } else {
                $form->addError(
                    new FormError($this->getTranslator()->trans($error['error']))
                );
            }
        }
    }
}
