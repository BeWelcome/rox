<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SubTrip;
use AppBundle\Entity\Trip;
use AppBundle\Model\TripModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Form\TripType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TripController extends Controller
{
    /**
     * @Route("/trip", name="trip")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request, $page = 1, $items = 10)
    {
        $page = $request->query->get('page', 1);
        $tripModel = new TripModel($this->getDoctrine());

        $trips = $tripModel->findLatest($page, $items);

        $content = $this->render(':trip:list.html.twig', [
            'trips' => $trips,
            'filter' => $request->query->all(),
            'page' => $page,
            'pages' => 2,
        ]);

        return new Response($content);
    }

    /***
     * @Route("/trip/{id}", name="trip_show")
     *
     * @param Trip $trip The trip to show
     * @return Response
     */
    public function showAction(Trip $trip)
    {
        $content = $this->render(':trip:show.html.twig', [
            'trip' => $trip,
        ]);

        return new Response($content);
    }

    /**
     * Create a new trip
     *
     * @Route("/trip/create", name="trip_create")
     *
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $trip = new Trip();
        $subtrips = $trip->getSubtrips();
        $subtrips->add(new SubTrip());

        $editForm = $this->createForm(TripType::class, $trip);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->flush();

            $this->addFlash('success', 'trip.updated_successfully');

            return $this->redirectToRoute('trip', ['id' => $trip->getId()]);
        }

        return $this->render(':trip:edit.html.twig', [
            'form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/trip/{id}/edit", name="trip_edit")
     * @param Request $request
     * @param Trip $trip The trip to edit
     * @return Response
     */
    public function editAction(Request $request, Trip $trip)
    {
        $editForm = $this->createForm(TripType::class, $trip);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->flush();

            $this->addFlash('success', 'trip.updated_successfully');

            return $this->redirectToRoute('trip', ['id' => $trip->getId()]);
        }

        return $this->render(':trip:edit.html.twig', [
            'form' => $editForm->createView(),
        ]);
    }
}
