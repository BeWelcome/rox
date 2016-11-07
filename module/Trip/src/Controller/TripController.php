<?php

namespace Rox\Trip\Controller;

use Rox\Core\Entity\SubTrip;
use Rox\Core\Entity\Trip;
use Rox\Core\Exception\NotFoundException;
use Rox\Trip\Form\TripFormType;
use Rox\Trip\Form\TripType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TripController extends Controller
{
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $trips = $this->getDoctrine()->getRepository(Trip::class)->findLatest($page);

        $content = $this->render('@trip/list.html.twig', [
            'trips' => $trips,
            'filter' => $request->query->all(),
            'page' => $page,
            'pages' => 2,
        ]);

        return new Response($content);
    }

    public function showAction($id)
    {
        $tripRepository = new Trip();
        $trip = $tripRepository->getById($id);

        $content = $this->render('@trip/show.html.twig', [
            'trip' => $trip,
        ]);

        return new Response($content);
    }

    /**
     * @param $request
     * @param int $id Switch between create (id = 0) and edit.
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws NotFoundException
     * @internal param $create
     */
    private function handleEditCreateAction($request, $id = 0)
    {
        try {
            $trip = new Trip();
            $flashText = 'Trip created.';
            if ($id !== 0) {
                $flashText = 'Trip updated.';
                $tripRepository = new Trip();
                $trip = $tripRepository->getById($id);
            }
        } catch (NotFoundException $e) {
            throw $e;
        }

        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $this->getUser();
            if ($id === 0) {
                $data->created_by = $user->id;
            }
            if ($id !== 0) {
                $data->updated_by = $user->id;
            }

            $data->save();

            $this->addFlash('notice', $flashText);

            return $this->redirectToRoute('my_trips');
        }

        return new Response(
            $this->render('@trip/edit.html.twig', [
                'form' => $form->createView(),
            ])
        );
    }

    /***
     * Create a new community news
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

        return $this->render('@trip/edit.html.twig', [
            'form' => $editForm->createView(),
        ]);
    }

    /***
     * @param Request $request
     * @param Trip $trip The trip to edit
     * @return Response
     */
    public function editAction(Request $request, Trip $trip, $id)
    {
        $editForm = $this->createForm(TripType::class, $trip);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->flush();

            $this->addFlash('success', 'trip.updated_successfully');

            return $this->redirectToRoute('trip', ['id' => $trip->getId()]);
        }

        return $this->render('@trip/edit.html.twig', [
            'form' => $editForm->createView(),
        ]);
    }

}
