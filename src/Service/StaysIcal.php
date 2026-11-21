<?php

namespace App\Service;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\MultiDay;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * One stay of the member as an iCalendar file (Add to calendar of a stay, for Apple and the other calendar apps):
 * an all-day event from arrival to departure, with a link to the conversation. The stay keeps its UID, so adding it
 * again updates it.
 */
class StaysIcal
{
    public function __construct(
        private readonly UpcomingStays $upcomingStays,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * One stay of the member, by its request, or null when it isn't one of their accepted stays.
     */
    public function getStayCalendar(int $requestId): ?string
    {
        $stay = $this->upcomingStays->getStay($requestId);

        return null === $stay ? null : $this->render([$stay]);
    }

    /**
     * @param list<array{message: Message, request: HostingRequest, other: Member, hosting: bool}> $stays
     */
    private function render(array $stays): string
    {
        $events = [];
        foreach ($stays as $stay) {
            $request = $stay['request'];
            $first = DateTimeImmutable::createFromInterface($request->getArrival());
            $last = DateTimeImmutable::createFromInterface($request->getDeparture() ?? $request->getArrival());
            $occurrence = $last->format('Y-m-d') === $first->format('Y-m-d')
                ? new SingleDay(new Date($first))
                : new MultiDay(new Date($first), new Date($last));
            $summary = $this->translator->trans(
                $stay['hosting'] ? 'stays.export.hosting' : 'stays.export.guest',
                ['username' => $stay['other']->getUsername()]
            );

            $events[] = new Event(new UniqueIdentifier('stay-' . $request->getId() . '@bewelcome.org'))
                ->setOccurrence($occurrence)
                ->setSummary($summary)
                ->setUrl(new Uri($this->urlGenerator->generate(
                    'conversation_view',
                    ['id' => $stay['message']->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )));
        }

        return (string) new CalendarFactory()->createCalendar(new Calendar($events));
    }
}
