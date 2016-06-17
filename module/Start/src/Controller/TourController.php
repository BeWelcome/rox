<?php

namespace Rox\Start\Controller;

use Rox\Core\Controller\AbstractController;
use Rox\Member\Model\Member;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TourController extends AbstractController
{
    public function indexAction(Request $request)
    {
        $page = $request->attributes->get('page');

        $stepTemplate = '@start/tour/' . $page . '.html.twig';

        if (!$this->getEngine()->exists($stepTemplate)) {
            throw new NotFoundHttpException();
        }

        // TODO move to own function and filter out members with no photo
        $memberModel = new Member();

        $q = $memberModel->newQuery();

        $q->where('Status', 'Active');

        $q->orderByRaw('RAND()');

        $q->limit(12);

        $randomMembers = $q->get();

        return new Response($this->render('@start/tour.html.twig', [
            'randomMembers' => $randomMembers,
            'stepTemplate' => $stepTemplate,
            'step' => $page,
        ]));
    }
}
