<?php

namespace App\Controller;

use App\Service\StaysIcal;
use App\Service\UpcomingStays;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The calendar of the member's stays, as host and as guest (See all of the Upcoming stays card on the home page).
 */
class StaysController extends AbstractController
{
    #[Route(path: '/stays', name: 'stays')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function calendar(Request $request, UpcomingStays $upcomingStays): Response
    {
        // ?month=2026-11, this month otherwise
        $month = Carbon::today()->startOfMonth();
        $asked = (string) $request->query->get('month', '');
        if (1 === preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $asked)) {
            $month = Carbon::createFromFormat('!Y-m', $asked);
        }

        return $this->render('stays/calendar.html.twig', $upcomingStays->getMonth($month) + [
            'previous' => $month->copy()->subMonth()->format('Y-m'),
            'next' => $month->copy()->addMonth()->format('Y-m'),
            'isThisMonth' => $month->format('Y-m') === Carbon::today()->format('Y-m'),
        ]);
    }

    /**
     * One stay as an .ics file (Add to calendar of a stay, Apple and other calendar apps).
     */
    #[Route(path: '/stays/{id}/calendar.ics', name: 'stay_export', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function exportStay(int $id, StaysIcal $staysIcal): Response
    {
        $calendar = $staysIcal->getStayCalendar($id);
        if (null === $calendar) {
            throw $this->createNotFoundException();
        }

        return $this->calendarFile($calendar, 'bewelcome-stay-' . $id . '.ics');
    }

    private function calendarFile(string $calendar, string $filename): Response
    {
        $response = new Response($calendar);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        ));

        return $response;
    }
}
