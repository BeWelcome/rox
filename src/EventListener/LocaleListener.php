<?php

// src/EventSubscriber/LocaleSubscriber.php

namespace App\EventListener;

use App\Entity\Language;
use Carbon\Carbon;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Negotiation\AcceptLanguage;
use Negotiation\Exception\Exception;
use Negotiation\LanguageNegotiator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The language of a request: the route's _locale, the one of the session, the one the visitor last picked (a cookie,
 * also set at login to the member's language, so it outlives the session and the logout), the browser's languages,
 * English. The first request of a visit has no session yet and gets the same (rox #206: it was English).
 */
readonly class LocaleListener implements EventSubscriberInterface
{
    public const string COOKIE = 'bw_locale';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private array $locales,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [
                ['onKernelRequest', 20],
            ],
        ];
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        // a request without a session doesn't start one here, and a stateless route leaves the session alone
        $stateless = $request->attributes->getBoolean('_stateless');
        $session = $request->hasPreviousSession() && !$stateless ? $request->getSession() : null;

        // try to see if the locale has been set as a _locale routing parameter
        $locale = $request->attributes->get('_locale');
        if ($locale) {
            $session?->set('_locale', $locale);
        } else {
            $locale = $session?->get('_locale') ?? $this->pickedLocale($request) ?? $this->browserLocale($request);
        }

        $request->setLocale($locale);
        Carbon::setLocale($locale);

        if (null === $session) {
            return;
        }

        $languageRepository = $this->entityManager->getRepository(Language::class);
        /** @var Language $language */
        $language = $languageRepository->findOneBy([
            'shortCode' => $locale,
        ]);

        if (null !== $language) {
            $session->set('lang', $language->getShortCode());
        }
    }

    /**
     * Remembers the language picked (LocaleController) or the member's (at login) for a year.
     */
    public static function cookie(string $locale, bool $secure): Cookie
    {
        return Cookie::create(self::COOKIE, $locale, new DateTimeImmutable('+1 year'), '/', null, $secure, true, false, Cookie::SAMESITE_LAX);
    }

    private function pickedLocale(Request $request): ?string
    {
        $locale = $request->cookies->get(self::COOKIE);

        return \in_array($locale, $this->locales, true) ? $locale : null;
    }

    /**
     * The first of the browser's languages (Accept-Language) the site has, English without one.
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    private function browserLocale(Request $request): string
    {
        $negotiator = new LanguageNegotiator();
        try {
            /** @var AcceptLanguage|null $bestLanguage */
            $bestLanguage = $negotiator->getBest((string) $request->server->get('HTTP_ACCEPT_LANGUAGE'), $this->locales);
        } catch (Exception) {
            return 'en';
        }

        return null !== $bestLanguage ? $bestLanguage->getBasePart() : 'en';
    }
}
