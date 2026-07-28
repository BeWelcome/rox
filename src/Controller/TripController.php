<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Dto\LegDto;
use App\Dto\TripDto;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TripController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(
        private readonly TripModel $tripModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/trips/{page}', name: 'trips', requirements: ['page' => '\d+'])]
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

    #[Route(path: '/trip/{id}', name: 'trip_show', requirements: ['id' => '\d+'])]
    #[IsGranted('TRIP_VIEW', subject: 'trip')]
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

    #[Route(path: '/new/trip', name: 'new_trip')]
    public function create(Request $request): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        if (MemberStatusType::ACCOUNT_ACTIVATED === $member->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.trip.not.confirmed');

            return $this->redirectToRoute('trips');
        }

        $tripDto = new TripDto();
        $tripDto->creator = $member;
        $tripDto->legs->add(new LegDto());

        $createForm = $this->createForm(TripType::class, $tripDto);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $errors = $this->tripModel->checkTripCreateOrEditData($tripDto);
            if (empty($errors)) {
                $trip = new Trip();
                $trip->setCreator($member);
                $tripDto->toEntity($trip);

                $this->tripModel->orderTripLegs($tripDto);
                $this->tripModel->syncLegs($trip, $tripDto);

                $this->entityManager->persist($trip);
                $this->entityManager->flush();
                $this->tripModel->notifyHostsAboutTrip($trip);

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

    #[Route(path: '/trip/{id}/edit', name: 'trip_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('TRIP_EDIT', subject: 'trip')]
    public function edit(Request $request, Trip $trip): Response
    {
        if ($this->tripModel->hasTripExpired($trip)) {
            $this->addTranslatedFlash('notice', 'trip.flash.expired');

            return $this->redirectToRoute('trip_show', ['id' => $trip->getId()]);
        }

        $tripDto = TripDto::fromEntity($trip);
        $editForm = $this->createForm(TripType::class, $tripDto);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $errors = $this->tripModel->checkTripCreateOrEditData($tripDto);
            if (empty($errors)) {
                $tripDto->toEntity($trip);

                $this->tripModel->orderTripLegs($tripDto);
                // \todo Check for deleted legs and take care to remove link to invitation
                $this->tripModel->syncLegs($trip, $tripDto);

                $this->entityManager->persist($trip);
                $this->entityManager->flush();
                $this->tripModel->notifyHostsAboutTrip($trip);

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

    #[Route(path: '/trip/{id}/remove', name: 'trip_remove', requirements: ['id' => '\d+'])]
    #[IsGranted('TRIP_REMOVE', subject: 'trip')]
    public function remove(Trip $trip): RedirectResponse
    {
        $this->tripModel->hideTrip($trip);

        return $this->redirectToRoute('trips');
    }

    #[Route(path: '/trip/{id}/copy', name: 'trip_copy', requirements: ['id' => '\d+'])]
    #[IsGranted('TRIP_COPY', subject: 'trip')]
    public function copy(Trip $trip): Response
    {
        $newTrip = $this->tripModel->copyTrip($trip);

        return $this->redirectToRoute('trip_edit', ['id' => $newTrip->getId()]);
    }

    #[Route(path: '/trip/leg/{id}/hide', name: 'trip_hide', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('hide_leg')]
    public function hideLeg(Request $request, Subtrip $leg): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();
        $this->tripModel->markLegAsHidden($member, $leg);

        if ('homepage' === $request->request->get('redirectTo')) {
            return $this->redirectToRoute('homepage', ['_fragment' => 'visitors']);
        }

        return $this->redirectToRoute('visitors');
    }

    /**
     * Show all trip legs that are in the vicinity of a member.
     */
    #[Route(path: '/visitors/{page}', name: 'visitors', requirements: ['page' => '\d+'])]
    public function tripsInArea(
        Request $request,
        int $page = 1,
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

        /** @var SubtripRepository $legRepository */
        $legRepository = $this->entityManager->getRepository(Subtrip::class);
        $legsQuery = $legRepository->getLegsInAreaQuery($host, $radius);

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
        $subMenu = [
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

        return $subMenu;
    }

    private function handleErrors(FormInterface &$form, array $errors): void
    {
        foreach ($errors as $error) {
            if (isset($error['leg'])) {
                $form->get('legs')->get($error['leg'])->get($error['field'])->addError(
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
