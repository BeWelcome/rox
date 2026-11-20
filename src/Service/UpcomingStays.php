<?php

namespace App\Service;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Attribute\AsTwigFunction;

/**
 * The stays coming up for the logged-in member, as host or as guest: accepted hosting requests and invitations
 * that aren't over yet, soonest first (the Upcoming stays card of the home page), and the stays calendar (/stays).
 */
class UpcomingStays
{
    /** Stays shown as bars on the same week of the calendar, one under the other; the others count as "+N" */
    private const int MAX_LANES = 3;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    /**
     * Twig: upcoming_stays(limit).
     *
     * @return list<array{message: Message, request: HostingRequest, other: Member, hosting: bool}> the first message
     *     of each request (what opens the conversation), the request, the other member, and whether the member hosts
     */
    #[AsTwigFunction('upcoming_stays')]
    public function getUpcomingStays(int $limit = 5): array
    {
        return $this->getStays(Carbon::today(), null, $limit);
    }

    /**
     * The month of the stays calendar (/stays): its weeks, Monday first, each with its days, the stays that cross it
     * as bars (the column of their first day in that week, how many days, and their line) and per day how many more
     * didn't fit, and the stays of the month.
     *
     * @return array{month: Carbon, weeks: list<array{days: list<Carbon>, bars: list<array{stay: array, column: int, span: int, before: bool, after: bool, lane: int}>, more: array<int, int>}>, stays: list<array>}
     */
    public function getMonth(Carbon $month): array
    {
        $first = $month->copy()->startOfMonth();
        $last = $month->copy()->endOfMonth()->startOfDay();
        $start = $first->copy()->startOfWeek(Carbon::MONDAY);
        $end = $last->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay();
        $stays = $this->getStays($start, $end, 200);

        $weeks = [];
        for ($weekStart = $start->copy(); $weekStart <= $end; $weekStart->addWeek()) {
            $weekEnd = $weekStart->copy()->addDays(6);
            $days = [];
            for ($day = 0; $day < 7; ++$day) {
                $days[] = $weekStart->copy()->addDays($day);
            }
            $bars = [];
            foreach ($stays as $stay) {
                $arrival = Carbon::instance($stay['request']->getArrival())->startOfDay();
                $departure = Carbon::instance($stay['request']->getDeparture() ?? $stay['request']->getArrival())->startOfDay();
                if ($arrival > $weekEnd || $departure < $weekStart) {
                    continue;
                }
                $from = $arrival->max($weekStart);
                $to = $departure->min($weekEnd);
                $bars[] = [
                    'stay' => $stay,
                    'column' => (int) $weekStart->diffInDays($from) + 1,
                    'span' => (int) $from->diffInDays($to) + 1,
                    'before' => $arrival < $weekStart,
                    'after' => $departure > $weekEnd,
                ];
            }
            // Each bar on the first line free on all its days; past MAX_LANES lines, the day says how many more
            usort($bars, static fn (array $a, array $b): int => [$a['column'], -$a['span']] <=> [$b['column'], -$b['span']]);
            $laneEnds = [];
            $more = array_fill(1, 7, 0);
            $shown = [];
            foreach ($bars as $bar) {
                $lane = 0;
                while (isset($laneEnds[$lane]) && $laneEnds[$lane] >= $bar['column']) {
                    ++$lane;
                }
                $laneEnds[$lane] = $bar['column'] + $bar['span'] - 1;
                if ($lane < self::MAX_LANES) {
                    $shown[] = $bar + ['lane' => $lane + 1];
                } else {
                    for ($column = $bar['column']; $column <= $laneEnds[$lane]; ++$column) {
                        ++$more[$column];
                    }
                }
            }
            $weeks[] = ['days' => $days, 'bars' => $shown, 'more' => $more];
        }

        $inMonth = array_values(array_filter($stays, static function (array $stay) use ($first, $last): bool {
            $arrival = Carbon::instance($stay['request']->getArrival())->startOfDay();
            $departure = Carbon::instance($stay['request']->getDeparture() ?? $stay['request']->getArrival())->startOfDay();

            return $arrival <= $last && $departure >= $first;
        }));

        return ['month' => $first, 'weeks' => $weeks, 'stays' => $inMonth];
    }

    /**
     * The member's accepted stays that aren't over before $from (and start by $to when given), soonest first.
     *
     * @return list<array{message: Message, request: HostingRequest, other: Member, hosting: bool}>
     */
    private function getStays(Carbon $from, ?Carbon $to, int $limit): array
    {
        $member = $this->security->getUser();
        if (!$member instanceof Member) {
            return [];
        }

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('m', 'r')
            ->from(Message::class, 'm')
            ->join('m.request', 'r')
            ->where('m.id IN (SELECT MIN(f.id) FROM ' . Message::class . ' f'
                . ' WHERE f.request IS NOT NULL AND (f.sender = :member OR f.receiver = :member) GROUP BY f.request)')
            ->andWhere('r.status = :accepted')
            ->andWhere('r.departure >= :from OR (r.departure IS NULL AND r.arrival >= :from)')
            ->setParameter('member', $member)
            ->setParameter('accepted', HostingRequest::REQUEST_ACCEPTED)
            ->setParameter('from', $from->copy()->startOfDay())
            ->orderBy('r.arrival', 'ASC')
            ->setMaxResults($limit);
        if (null !== $to) {
            $queryBuilder
                ->andWhere('r.arrival < :to')
                ->setParameter('to', $to->copy()->addDay()->startOfDay());
        }
        /** @var list<Message> $messages */
        $messages = $queryBuilder->getQuery()->getResult();

        $stays = [];
        foreach ($messages as $message) {
            $request = $message->getRequest();
            // A request is sent by the guest, an invitation by the host
            $initiatorHosts = null !== $request->getInviteForLeg();
            $memberStarted = $message->getInitiator()->getId() === $member->getId();
            $other = $message->getSender()->getId() === $member->getId() ? $message->getReceiver() : $message->getSender();
            $stays[] = [
                'message' => $message,
                'request' => $request,
                'other' => $other,
                'hosting' => $memberStarted === $initiatorHosts,
            ];
        }

        return $stays;
    }
}
