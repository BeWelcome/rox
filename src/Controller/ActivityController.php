<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Model\ActivityModel;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ActivityController extends AbstractController
{
    /**
     * @Route("/activity", name="activity")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request, ActivityModel $activityModel)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 15);

        $activities = $activityModel->getLatest($page, $limit);

        return $this->render('activity/list.html.twig', [
            'active' => 'ActivitiesNearMe',
            'activities' => $activities,
        ]);
    }

    /**
     * @Route("/activity/{id}", name="activity_show",
     *     requirements={"id": "\d+"})
     *
     * @return Response
     */
    public function showAction(Activity $activity)
    {
        $content = $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);

        return new Response($content);
    }

    /**
     * @Route("/activity/{id}/edit", name="activity_edit",
     *     requirements={"id": "\d+"})
     *
     * @return Response
     */
    public function editAction(Activity $activity)
    {
        $content = $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);

        return new Response($content);
    }

    /**
     * @Route("/activity/{id}/download", name="activity_download",
     *     requirements={"id": "\d+"})
     */
    public function download(Activity $activity): Response
    {
        $start = new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $activity->getStarts()), false);
        $end = new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $activity->getEnds()), false);
        $occurrence = new TimeSpan($start, $end);
        $location = new Location($activity->getAddress());
        $strippedDescription = strip_tags(str_replace("<br>", "\r\n", $activity->getDescription()));
        $description = str_replace("\r\n", "\n", $strippedDescription);
        $event = new Event();
        $event
            ->setOccurrence($occurrence)
            ->setSummary($activity->getTitle())
            ->setDescription($description)
            ->setLocation($location)
        ;

        // 2. Create Calendar domain entity
        $calendar = new Calendar([$event]);

        // 3. Transform domain entity into an iCalendar component
        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        // 5. Output
        $response = new Response($calendarComponent);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'activity.ics'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
