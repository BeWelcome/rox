<?php

namespace App\Controller\Admin;

use App\Doctrine\MemberStatusType;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Preference;
use App\Logger\Logger;
use App\Model\Admin\NewMembersModel;
use App\Service\MessageSender;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\TranslatedFlashTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The members who signed up in the last 90 days: the greeting team sends them a local and a global greeting,
 * the safety team and the checkers see the unfinished signups too and change a member's status.
 */
class NewMembersController extends AbstractController
{
    use ItemsPerPageTraits;
    use TranslatedFlashTrait;

    public const string TOKEN = 'newmembers';

    /**
     * The statuses the safety team sets here.
     */
    public const array STATUSES = [
        MemberStatusType::ACTIVE,
        MemberStatusType::AWAITING_MAIL_CONFIRMATION,
        MemberStatusType::PASSED_AWAY,
        MemberStatusType::PENDING,
        MemberStatusType::BANNED,
        MemberStatusType::CHOICE_INACTIVE,
        MemberStatusType::OUT_OF_REMIND,
        MemberStatusType::ACTIVE_HIDDEN,
        MemberStatusType::SUSPENDED,
        MemberStatusType::ASKED_TO_LEAVE,
    ];

    /** The most members one standard greeting goes to */
    public const int BULK_LIMIT = 50;

    private const array GREETINGS = [
        'local' => NewMembersModel::LOCAL_GREETING,
        'global' => NewMembersModel::GLOBAL_GREETING,
    ];

    public function __construct(
        private readonly NewMembersModel $model,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly MessageSender $messageSender,
        private readonly Logger $logger,
    ) {
    }

    #[Route(path: '/admin/newmembers/{page}', name: 'newmembers', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function list(Request $request, int $page = 1): Response
    {
        $this->denyUnlessAnyGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME]);
        $all = $this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM) || $this->isGranted(Member::ROLE_ADMIN_CHECKER);

        /** @var Member $volunteer */
        $volunteer = $this->getUser();
        $locale = $request->getLocale();
        $profile = $this->profile($request);
        $countries = $this->countries($volunteer);
        $languages = $this->languages($volunteer);
        $options = $this->model->getCountries($all, $locale, $profile);
        $chosen = [];
        foreach ($countries as $id) {
            $option = array_values(array_filter($options, static fn (array $country): bool => $country['id'] === $id))[0] ?? null;
            $chosen[] = $option ?? ['id' => $id, 'name' => $this->countryName($id, $locale), 'count' => 0];
        }
        // the languages' counts are those in the chosen countries: the two filters go together
        $languageCounts = $this->model->getLanguages($all, $countries, $profile);

        return $this->render('admin/newmembers/list.html.twig', [
            'members' => $this->model->getPager($all, $locale, $page, $this->getItemsPerPage($volunteer), $countries, $profile, $languages),
            'all' => $all,
            'can_change_status' => $this->canChangeStatus(),
            'statuses' => self::STATUSES,
            'chosen' => $chosen,
            'addable' => array_values(array_filter($options, static fn (array $country): bool => !\in_array($country['id'], $countries, true))),
            'chosen_languages' => $this->languageOptions(array_fill_keys($languages, 0), $languageCounts),
            'addable_languages' => $this->languageOptions(array_diff_key($languageCounts, array_flip($languages)), $languageCounts),
            'profile' => $profile,
            'profile_counts' => $this->model->getProfileCounts($all, $countries, $languages),
            'waiting' => $this->model->countWaiting(),
            'bulk_limit' => self::BULK_LIMIT,
        ]);
    }

    /**
     * Adds a country to the volunteer's countries or removes one; kept for their next visits.
     */
    #[Route(path: '/admin/newmembers/countries', name: 'newmembers_countries', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function countriesChange(Request $request): RedirectResponse
    {
        $this->denyUnlessAnyGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME]);
        /** @var Member $volunteer */
        $volunteer = $this->getUser();

        $countries = $this->countries($volunteer);
        $add = $request->request->getInt('add');
        $remove = $request->request->getInt('remove');
        if (0 < $add && !\in_array($add, $countries, true) && null !== $this->countryName($add, 'en')) {
            $countries[] = $add;
        }
        $countries = array_values(array_filter($countries, static fn (int $id): bool => $id !== $remove));
        $this->saveFilter($volunteer, Preference::NEW_MEMBERS_COUNTRIES, $countries);

        return $this->redirectToRoute('newmembers', $this->listParameters($request, 1));
    }

    /**
     * Adds a language to the volunteer's languages or removes one; kept for their next visits.
     */
    #[Route(path: '/admin/newmembers/languages', name: 'newmembers_languages', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function languagesChange(Request $request): RedirectResponse
    {
        $this->denyUnlessAnyGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME]);
        /** @var Member $volunteer */
        $volunteer = $this->getUser();

        $languages = $this->languages($volunteer);
        $add = $request->request->getString('add');
        $remove = $request->request->getString('remove');
        if (
            '' !== $add
            && !\in_array($add, $languages, true)
            && null !== $this->entityManager->getRepository(Language::class)->findOneBy(['shortCode' => $add])
        ) {
            $languages[] = $add;
        }
        $languages = array_values(array_filter($languages, static fn (string $code): bool => $code !== $remove));
        $this->saveFilter($volunteer, Preference::NEW_MEMBERS_LANGUAGES, $languages);

        return $this->redirectToRoute('newmembers', $this->listParameters($request, 1));
    }

    /**
     * One standard greeting to the selected members, each as their own message from the volunteer; the greeting is
     * marked as sent for all of them.
     */
    #[Route(
        path: '/admin/newmembers/greeting/{greeting}',
        name: 'newmembers_greeting_bulk',
        requirements: ['greeting' => 'local|global'],
        methods: ['POST'],
    )]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function bulkGreeting(Request $request, string $greeting): RedirectResponse
    {
        $this->denyUnlessAnyGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME]);
        /** @var Member $volunteer */
        $volunteer = $this->getUser();

        $usernames = \array_slice(array_values(array_unique(array_filter(array_map('strval', $request->request->all('usernames'))))), 0, self::BULK_LIMIT);
        $subject = trim($request->request->getString('subject'));
        $text = trim($request->request->getString('text'));
        if ([] === $usernames || '' === $subject || '' === $text) {
            $this->addTranslatedFlash('error', 'admin.newmembers.bulk.incomplete');

            return $this->redirectToRoute('newmembers', $this->listParameters($request, $request->request->getInt('page', 1)));
        }

        $members = $this->entityManager->getRepository(Member::class)->findBy(['username' => $usernames]);
        foreach ($members as $member) {
            // {username} (the greeting's placeholder, a translation's parameter style), %username% as it was before
            $names = ['{username}' => $member->getUsername(), '%username%' => $member->getUsername()];
            $body = strtr(nl2br(htmlspecialchars($text), false), $names);
            $this->messageSender->send($volunteer, $member, null, strtr($subject, $names), $body);
            $this->model->markGreetingSent($member, self::GREETINGS[$greeting]);
        }
        $this->logger->write(\sprintf(
            'Standard %s greeting to %d new members: %s',
            $greeting,
            \count($members),
            implode(', ', array_map(static fn (Member $member): string => $member->getUsername(), $members)),
        ), 'NewMembers');

        $this->addTranslatedFlash('notice', 'admin.newmembers.bulk.sent.' . $greeting, ['count' => \count($members)]);

        return $this->redirectToRoute('newmembers', $this->listParameters($request, $request->request->getInt('page', 1)));
    }

    /**
     * The old URL of a page of the list.
     */
    #[Route(path: '/admin/newmembers/page/{page}', name: 'newmembers_pages_old', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function oldPage(int $page): RedirectResponse
    {
        return $this->redirectToRoute('newmembers', ['page' => $page], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Marks the greeting as sent and opens the message to the member.
     */
    #[Route(
        path: '/admin/newmembers/{username:member}/greeting/{greeting}',
        name: 'newmembers_greeting',
        requirements: ['greeting' => 'local|global'],
        methods: ['POST'],
    )]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function greeting(Member $member, string $greeting): RedirectResponse
    {
        $this->denyUnlessAnyGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME]);
        $this->model->markGreetingSent($member, self::GREETINGS[$greeting]);

        return $this->redirectToRoute('message_new', ['username' => $member->getUsername()]);
    }

    #[Route(path: '/admin/newmembers/{username:member}/status', name: 'newmembers_status', methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function status(Request $request, Member $member): RedirectResponse
    {
        if (!$this->canChangeStatus()) {
            throw $this->createAccessDeniedException();
        }

        $status = $request->request->getString('status');
        if (!\in_array($status, self::STATUSES, true)) {
            throw $this->createNotFoundException();
        }

        $member->setStatus($status);
        $this->entityManager->flush();
        $this->addTranslatedFlash('notice', 'admin.newmembers.status.changed', [
            '%username%' => $member->getUsername(),
            '%status%' => $this->translator->trans('memberstatus' . strtolower($status)),
        ]);

        return $this->redirectToRoute('newmembers', $this->listParameters($request, $request->request->getInt('page', 1)));
    }

    /**
     * @return array<string, int|string> the list's page and profile filter
     */
    private function listParameters(Request $request, int $page): array
    {
        $profile = $this->profile($request);

        return array_filter(['page' => max(1, $page), 'profile' => NewMembersModel::PROFILE_ALL === $profile ? null : $profile]);
    }

    private function profile(Request $request): string
    {
        $profile = $request->query->getString('profile', $request->request->getString('profile'));

        return \in_array($profile, NewMembersModel::PROFILES, true) ? $profile : NewMembersModel::PROFILE_ALL;
    }

    /**
     * @return list<int> the geoname ids of the volunteer's countries (Preference::NEW_MEMBERS_COUNTRIES)
     */
    private function countries(Member $volunteer): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $this->savedFilter($volunteer, Preference::NEW_MEMBERS_COUNTRIES)))));
    }

    /**
     * @return list<string> the codes of the volunteer's languages (Preference::NEW_MEMBERS_LANGUAGES)
     */
    private function languages(Member $volunteer): array
    {
        return array_values(array_unique($this->savedFilter($volunteer, Preference::NEW_MEMBERS_LANGUAGES)));
    }

    /**
     * The languages with their names in the page's language and their counts, by name.
     *
     * @param array<string, int> $languages codes as keys
     * @param array<string, int> $counts    members by code
     *
     * @return list<array{code: string, name: string, count: int}>
     */
    private function languageOptions(array $languages, array $counts): array
    {
        $options = [];
        foreach (array_keys($languages) as $code) {
            $options[] = ['code' => (string) $code, 'name' => $this->translator->trans('lang_' . $code), 'count' => $counts[$code] ?? 0];
        }
        usort($options, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

        return $options;
    }

    /**
     * A filter the volunteer keeps for their next visits: a hidden preference with comma separated values.
     *
     * @return list<string>
     */
    private function savedFilter(Member $volunteer, string $codename): array
    {
        return array_values(array_filter(explode(',', $volunteer->getMemberPreferenceValue($this->preference($codename))), static fn (string $value): bool => '' !== $value));
    }

    /**
     * @param list<int|string> $values
     */
    private function saveFilter(Member $volunteer, string $codename, array $values): void
    {
        $memberPreference = $volunteer->getMemberPreference($this->preference($codename));
        $memberPreference->setValue(implode(',', $values));
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();
    }

    private function preference(string $codename): Preference
    {
        /** @var Preference $preference */
        $preference = $this->entityManager->getRepository(Preference::class)->findOneBy(['codename' => $codename]);

        return $preference;
    }

    /** a country's name in the language, else in English (one the list has no members of any more) */
    private function countryName(int $id, string $locale): ?string
    {
        $name = $this->entityManager->getConnection()->fetchOne(
            "SELECT COALESCE((SELECT t.content FROM location_translation t WHERE t.object_id = l.geoname_id AND t.field = 'name' AND t.locale IN (?, 'en') ORDER BY t.locale = ? DESC LIMIT 1), l.name) FROM location l WHERE l.geoname_id = ?",
            [$locale, $locale, $id],
        );

        return false === $name ? null : (string) $name;
    }

    private function canChangeStatus(): bool
    {
        foreach ([Member::ROLE_ADMIN_ADMIN, Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_ACCEPTER, Member::ROLE_ADMIN_PROFILE] as $role) {
            if ($this->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $roles
     */
    private function denyUnlessAnyGranted(array $roles): void
    {
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                return;
            }
        }

        throw $this->createAccessDeniedException();
    }
}
