<?php

// src/EventSubscriber/LocaleSubscriber.php

namespace App\EventListener;

use App\Entity\Language;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Negotiation\Exception\Exception;
use Negotiation\LanguageNegotiator;
use PVars;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface
{
    /** @var array */
    private $locales;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * LocaleListener constructor.
     *
     * @param string $locales
     */
    public function __construct(EntityManagerInterface $em, $locales)
    {
        $this->em = $em;
        $this->locales = explode(',', $locales);
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [
                ['onKernelRequest', 20],
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        $locale = $request->attributes->get('_locale');
        if ($locale) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $locale = $request->getSession()->get('_locale');
            if (null === $locale) {
                // still no locale, get the ones set in the HTTP_ACCEPT_LANGUAGE header
                // and check if a translation exists
                $locale = 'en';
                $negotiator = new LanguageNegotiator();
                try {
                    $bestLanguage = $negotiator->getBest($request->server->get('HTTP_ACCEPT_LANGUAGE'), $this->locales);
                    if (null !== $bestLanguage) {
                        $locale = $bestLanguage->getType();
                    }
                } catch (Exception $exception) {
                    $locale = 'en';
                }
            }
        }

        $request->setLocale($locale);
        Carbon::setLocale($locale);

        $languageRepository = $this->em->getRepository(Language::class);
        /** @var Language $language */
        $language = $languageRepository->findOneBy([
            'shortcode' => $locale,
        ]);

        if (null !== $language) {
            $request->getSession()->set('lang', $language->getShortcode());
            $request->getSession()->set('IdLanguage', $language->getId());
        }
        PVars::register('lang', $locale);
    }
}
