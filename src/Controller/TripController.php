<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\TripRadiusType;
use App\Form\TripType;
use App\Model\TripModel;
use App\Repository\SubtripRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class TripController extends AbstractController
{
    private TripModel $tripModel;
    private TranslatorInterface $translator;

    public function __construct(TripModel $tripModel, TranslatorInterface $translator)
    {
        $this->tripModel = $tripModel;
        $this->translator = $translator;
    }

    /**
     * @Route("/mytrips/{page}", name="mytrips",
     *     requirements={"page": "\d+"})
     *     )
     */
    public function myTrips(int $page = 1): Response
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
     */
    public function show(Trip $trip, TripModel $tripModel): Response
    {
        return $this->render('trip/show.html.twig', [
            'trip' => $trip,
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
        $trip = new Trip();
        $trip->setCreator($this->getUser());

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

                $this->addFlash('success', 'trip.created');

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
     */
    public function edit(Request $request, Trip $trip): Response
    {
        $member = $this->getUser();
        if ($trip->getCreator() !== $member) {
            throw new AccessDeniedException();
        }

        if ($this->tripModel->hasTripExpired($trip)) {
            $this->addFlash('notice', $this->translator->trans('trip.flash.expired'));

            return $this->redirectToRoute('trip_show', ['id' => $trip->getId()]);
        }

        $editForm = $this->createForm(TripType::class, $trip);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $editedTrip = $editForm->getData();

            $errors = $this->tripModel->checkTripCreateOrEditData($editedTrip);
            if (empty($errors)) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($editedTrip);
                $entityManager->flush();

                $this->addFlash('success', 'trip.edited');

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
     */
    public function remove(Trip $trip): RedirectResponse
    {
        $member = $this->getUser();
        if ($trip->getCreator() !== $member) {
            throw new AccessDeniedException();
        }

        $this->tripModel->hideTrip($trip);

        return $this->redirectToRoute('mytrips');
    }

    /**
     * Copies an existing trip (keeping all locations and sets dates in the future).
     *
     * @Route("/trip/{id}/copy", name="trip_copy",
     *     requirements={"id": "\d+"}
     * )
     */
    public function copy(Trip $trip): Response
    {
        $newTrip = $this->tripModel->copyTrip($trip);

        return $this->redirectToRoute('trip_edit', [ 'id' => $newTrip->getId()]);
    }

    /**
     * Show all trip legs that are in the vicinity of a member.
     *
     * @Route("/trip/{username}/area/{page}",
     *     requirements={"page"="\d+"},
     *     name="trip_in_area")
     *
     * @param mixed $page
     */
    public function tripsInArea(Request $request, Member $member, $page = 1): Response
    {
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
            if ($radius != $newRadius) {
                $this->tripModel->setTripsRadius($host, $newRadius);
                $radius = $newRadius;
            }
        }

        /** @var SubtripRepository $subtripRepository */
        $subtripRepository = $this->getDoctrine()->getRepository(Subtrip::class);
        $legsQuery = $subtripRepository->getLegsInAreaQuery($member, $radius);

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
                'key' => 'mytrips',
                'url' => $this->generateUrl('mytrips'),
            ],
            'trip_legs' => [
                'key' => 'trip.in.area',
                'url' => $this->generateUrl('trip_in_area', ['username' => $this->getUser()->getUsername()]),
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
                    new FormError($this->translator->trans($error['error']))
                );
            } else {
                $form->addError(
                    new FormError($this->translator->trans($error['error']))
                );
            }
        }
    }
}
