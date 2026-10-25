<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Dto\ActivityDto;
use App\Entity\Activity;
use App\Entity\Member;
use App\Form\ActivityAttendType;
use App\Form\ActivityRadiusType;
use App\Form\ActivitySearchType;
use App\Form\ActivityType;
use App\Model\ActivityModel;
use App\Repository\ActivityRepository;
use App\Security\ActivityVoter;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The activities pages (formerly build/activities). The route names and URLs are the legacy ones, so
 * existing links keep working.
 */
class ActivityController extends AbstractController
{
    use ItemsPerPageTraits;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private readonly ActivityRepository $activityRepository;

    public function __construct(
        private readonly ActivityModel $activityModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
        /** @var ActivityRepository $repository */
        $repository = $entityManager->getRepository(Activity::class);
        $this->activityRepository = $repository;
    }

    #[Route(path: '/activities', name: 'activities')]
    public function activities(): RedirectResponse
    {
        return $this->redirectToRoute('activities_near_me');
    }

    #[Route(path: '/activities/nearme', name: 'activities_near_me')]
    #[Route(path: '/activities/nearme/page/{page}', name: 'activities_near_me_pages', requirements: ['page' => '\d+'])]
    public function nearMe(Request $request, int $page = 1): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $radius = $this->activityModel->getRadius($member);
        $radiusForm = $this->createForm(ActivityRadiusType::class, ['radius' => $radius]);
        $radiusForm->handleRequest($request);
        if ($radiusForm->isSubmitted() && $radiusForm->isValid()) {
            $this->activityModel->setRadius($member, (int) $radiusForm->get('radius')->getData());

            return $this->redirectToRoute('activities_near_me');
        }

        $address = $member->getActiveAddress();

        return $this->renderList('activitiesnearme', $request, $page, fn (int $page) => $this->activityRepository
            ->findNearMember($member, $radius, $page, $this->activitiesPerPage()), [
                'radiusForm' => $radiusForm->createView(),
                'radius' => $radius,
                'place' => $address ? $address->getLocation()->getName() : null,
            ]);
    }

    #[Route(path: '/activities/myactivities', name: 'activities_my_activities')]
    #[Route(path: '/activities/myactivities/page/{page}', name: 'activities_my_activities_pages', requirements: ['page' => '\d+'])]
    public function myActivities(Request $request, int $page = 1): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->renderList('myactivities', $request, $page, fn (int $page) => $this->activityRepository
            ->findForMember($member, $page, $this->activitiesPerPage()));
    }

    #[Route(path: '/activities/upcoming', name: 'activities_upcoming_activities')]
    #[Route(path: '/activities/upcoming/page/{page}', name: 'activities_upcoming_activities_pages', requirements: ['page' => '\d+'])]
    public function upcoming(Request $request, int $page = 1): Response
    {
        return $this->renderList('upcomingactivities', $request, $page, fn (int $page) => $this->activityRepository
            ->findUpcoming(false, $page, $this->activitiesPerPage()));
    }

    #[Route(path: '/activities/online', name: 'activities_upcoming_online_activities')]
    #[Route(path: '/activities/online/page/{page}', name: 'activities_upcoming_online_activities_pages', requirements: ['page' => '\d+'])]
    public function online(Request $request, int $page = 1): Response
    {
        return $this->renderList('upcomingonlineactivities', $request, $page, fn (int $page) => $this->activityRepository
            ->findUpcoming(true, $page, $this->activitiesPerPage()));
    }

    #[Route(path: '/activities/past', name: 'activities_past_activities')]
    #[Route(path: '/activities/past/page/{page}', name: 'activities_past_activities_pages', requirements: ['page' => '\d+'])]
    public function past(Request $request, int $page = 1): Response
    {
        return $this->renderList('pastactivities', $request, $page, fn (int $page) => $this->activityRepository
            ->findPast($page, $this->activitiesPerPage()));
    }

    #[Route(path: '/activities/search', name: 'activities_search')]
    #[Route(path: '/activities/search/{keyword}', name: 'activities_search_results')]
    #[Route(path: '/activities/search/{keyword}/page/{page}', name: 'activities_search_results_pages', requirements: ['page' => '\d+'])]
    public function search(Request $request, string $keyword = '', int $page = 1): Response
    {
        $searchForm = $this->createForm(ActivitySearchType::class, ['keyword' => $keyword]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('activities_search_results', [
                'keyword' => trim((string) $searchForm->get('keyword')->getData()),
            ]);
        }

        $keyword = trim($keyword);
        $results = static fn () => null;
        if ('' !== $keyword) {
            $results = fn (int $page) => $this->activityRepository->search($keyword, $page, $this->activitiesPerPage());
        }

        return $this->renderList('search', $request, $page, $results, [
            'searchForm' => $searchForm->createView(),
            'keyword' => $keyword,
        ]);
    }

    #[Route(path: '/activities/rules', name: 'activities_rules')]
    public function rules(): Response
    {
        return $this->render('activity/rules.html.twig', [
            'submenu' => $this->getSubmenu('rules'),
        ]);
    }

    #[Route(path: '/activities/create', name: 'activities_create')]
    public function create(Request $request): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        if (MemberStatusType::ACCOUNT_ACTIVATED === $member->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.activity.not.confirmed');

            return $this->redirectToRoute('activities_my_activities');
        }

        $data = new ActivityDto();
        $address = $member->getActiveAddress();
        if ($address) {
            $data->location = $address->getLocation();
        }

        $form = $this->createForm(ActivityType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $this->activityModel->create($data, $member);
            $this->addTranslatedFlash('success', 'activity.created.flash');

            return $this->redirectToRoute('activity_created', ['id' => $activity->getId()]);
        }

        return $this->render('activity/edit.html.twig', [
            'form' => $form,
            'activity' => null,
            'submenu' => $this->getSubmenu('createactivities'),
        ]);
    }

    #[Route(path: '/activities/{id}/edit', name: 'activities_edit', requirements: ['id' => '\d+'])]
    #[IsGranted(ActivityVoter::EDIT, subject: 'activity')]
    public function edit(Request $request, Activity $activity): Response
    {
        $data = ActivityDto::fromEntity($activity);
        $form = $this->createForm(ActivityType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->activityModel->update($activity, $data);
            $this->addTranslatedFlash('success', 'activity.updated.flash');

            return $this->redirectToRoute('activities_show', ['id' => $activity->getId()]);
        }

        return $this->render('activity/edit.html.twig', [
            'form' => $form,
            'activity' => $activity,
            'submenu' => $this->getSubmenu('createactivities'),
        ]);
    }

    #[Route(path: '/activities/{id}', name: 'activities_show', requirements: ['id' => '\d+'])]
    public function show(Activity $activity): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $attendee = $activity->getAttendeeFor($member);

        $answerForm = null;
        if ($this->isGranted(ActivityVoter::ATTEND, $activity)) {
            $answerForm = $this->createForm(ActivityAttendType::class, [
                'status' => $attendee?->getStatus(),
                'comment' => $attendee?->getComment() ?? '',
            ], [
                'action' => $this->generateUrl('activity_answer', ['id' => $activity->getId()]),
            ])->createView();
        }

        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
            'attendee' => $attendee,
            'attendees' => $this->activityModel->getAttendeesByAnswer($activity),
            'answerForm' => $answerForm,
            'submenu' => $this->getSubmenu(null),
        ]);
    }

    /**
     * The legacy attendee pages; the activity page lists everyone now.
     */
    #[Route(path: '/activities/{id}/attendees/page/{page}', name: 'activities_show_attendees', requirements: ['id' => '\d+'])]
    public function attendees(Activity $activity): RedirectResponse
    {
        return $this->redirectToRoute('activities_show', ['id' => $activity->getId()], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route(path: '/activities/{id}/answer', name: 'activity_answer', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(ActivityVoter::ATTEND, subject: 'activity')]
    public function answer(Request $request, Activity $activity): RedirectResponse
    {
        $form = $this->createForm(ActivityAttendType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Member $member */
            $member = $this->getUser();
            $this->activityModel->answer(
                $activity,
                $member,
                (int) $form->get('status')->getData(),
                (string) $form->get('comment')->getData()
            );
            $this->addTranslatedFlash('success', 'activity.answer.saved');
        } else {
            $this->addTranslatedFlash('error', 'activity.answer.empty');
        }

        return $this->redirectToRoute('activities_show', ['id' => $activity->getId()]);
    }

    #[Route(path: '/activities/{id}/leave', name: 'activity_leave', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('activity_leave')]
    public function leave(Activity $activity): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();
        if ($this->activityModel->leave($activity, $member)) {
            $this->addTranslatedFlash('success', 'activity.left');
        }

        return $this->redirectToRoute('activities_show', ['id' => $activity->getId()]);
    }

    #[Route(path: '/activities/{id}/cancel', name: 'activity_cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(ActivityVoter::CANCEL, subject: 'activity')]
    #[IsCsrfTokenValid('activity_cancel')]
    public function cancel(Activity $activity): RedirectResponse
    {
        if (!$activity->isCancelled()) {
            $this->activityModel->cancel($activity);
            $this->addTranslatedFlash('notice', 'activityhasbeencancelled');
        }

        return $this->redirectToRoute('activities_show', ['id' => $activity->getId()]);
    }

    #[Route(path: '/activities/{id}/reactivate', name: 'activity_reactivate', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(ActivityVoter::CANCEL, subject: 'activity')]
    #[IsCsrfTokenValid('activity_reactivate')]
    public function reactivate(Activity $activity): RedirectResponse
    {
        if ($activity->isCancelled()) {
            $this->activityModel->reactivate($activity);
            $this->addTranslatedFlash('success', 'activity.reactivated');
        }

        return $this->redirectToRoute('activities_show', ['id' => $activity->getId()]);
    }

    #[Route(path: '/activity/{id}/created', name: 'activity_created', requirements: ['id' => '\d+'])]
    public function created(Activity $activity): Response
    {
        return $this->render('activity/created.html.twig', [
            'activity' => $activity,
            'submenu' => $this->getSubmenu('createactivities'),
        ]);
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    #[Route(path: '/activity/{id}/download', name: 'activity_download', requirements: ['id' => '\d+'])]
    public function download(Activity $activity): Response
    {
        $start = new DateTime(DateTimeImmutable::createFromInterface($activity->getStarts()), false);
        $end = new DateTime(DateTimeImmutable::createFromInterface($activity->getEnds()), false);
        $occurrence = new TimeSpan($start, $end);
        $location = new Location((string) $activity->getAddress());
        $strippedDescription = strip_tags(str_replace('<br>', "\r\n", $activity->getDescription()));
        $description = str_replace("\r\n", "\n", $strippedDescription);
        $event = new Event();
        $event
            ->setOccurrence($occurrence)
            ->setSummary($activity->getTitle())
            ->setDescription($description)
            ->setLocation($location)
        ;

        $calendar = new Calendar([$event]);
        $calendarComponent = new CalendarFactory()->createCalendar($calendar);

        $response = new Response((string) $calendarComponent);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'activity' . $activity->getId() . '.ics'
        ));

        return $response;
    }

    /**
     * Renders one of the activity lists. $find returns the page of activities, or null when there is
     * nothing to look for (a search without keyword).
     *
     * @param callable(int): ?Pagerfanta $find
     */
    private function renderList(string $tab, Request $request, int $page, callable $find, array $parameters = []): Response
    {
        try {
            $activities = $find(max(1, $page));
        } catch (OutOfRangeCurrentPageException) {
            throw $this->createNotFoundException();
        }

        // The pager links use the /page/{page} variant of the current list (activities_upcoming_activities_pages, ...)
        $route = (string) $request->attributes->get('_route');
        $routeParams = $request->attributes->get('_route_params', []);
        unset($routeParams['page']);

        return $this->render('activity/list.html.twig', array_merge([
            'tab' => $tab,
            'activities' => $activities,
            'pagerRoute' => str_ends_with($route, '_pages') ? $route : $route . '_pages',
            'pagerParams' => $routeParams,
            'submenu' => $this->getSubmenu($tab),
        ], $parameters));
    }

    /**
     * As many activities per page as the member chose for lists in the preferences.
     */
    private function activitiesPerPage(): int
    {
        /** @var Member $member */
        $member = $this->getUser();

        return max(1, $this->getItemsPerPage($member));
    }

    /**
     * The tabs of the activities pages, in the order of the legacy menu, and Create next to the title.
     */
    private function getSubmenu(?string $active): array
    {
        $items = [
            'activitiesnearme' => ['key' => 'activities.tab.near.me', 'url' => $this->generateUrl('activities_near_me')],
            'myactivities' => ['key' => 'activitiesmyactivities', 'url' => $this->generateUrl('activities_my_activities')],
            'upcomingactivities' => ['key' => 'activitiesupcoming', 'url' => $this->generateUrl('activities_upcoming_activities')],
            'upcomingonlineactivities' => ['key' => 'activitiesupcomingonline', 'url' => $this->generateUrl('activities_upcoming_online_activities')],
            'pastactivities' => ['key' => 'activitiespastactivities', 'url' => $this->generateUrl('activities_past_activities')],
            'search' => ['key' => 'activitiessearchresult', 'url' => $this->generateUrl('activities_search')],
            'rules' => ['key' => 'activities.rules', 'url' => $this->generateUrl('activities_rules')],
            'createactivities' => ['key' => 'activitiescreate', 'url' => $this->generateUrl('activities_create')],
        ];

        return ['active' => $active, 'items' => $items];
    }
}
