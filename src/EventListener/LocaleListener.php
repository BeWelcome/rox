<?php

// src/EventSubscriber/LocaleSubscriber.php

namespace App\EventListener;

use App\Entity\Language;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use PVars;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface
{
    /** @var string */
    private $defaultLocale;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * LocaleListener constructor.
     *
     * @param string $defaultLocale
     */
    public function __construct(EntityManagerInterface $em, $defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
        $this->em = $em;
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
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $locale = $request->getSession()->get('_locale');
            if (null === $locale) {
                $locale = 'en';
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
