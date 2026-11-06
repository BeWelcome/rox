<?php

namespace App\Controller\Admin;

use App\Doctrine\DomainType;
use App\Doctrine\TranslationAllowedType;
use App\Doctrine\TranslationFormat;
use App\Dto\RenderTranslationDto;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\RightVolunteer;
use App\Entity\Word;
use App\Form\CustomDataClass\Translation\EditTranslationRequest;
use App\Form\CustomDataClass\Translation\TranslationRequest;
use App\Form\EditTranslationFormType;
use App\Form\TranslationFormType;
use App\Model\Translation\TranslationTool;
use App\Model\TranslationModel;
use App\Repository\LanguageRepository;
use App\Service\InPlaceTranslations;
use App\Utilities\RedirectBackTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use MessageFormatter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

/**
 * Class TranslationController.
 *
 * @SuppressWarnings("PHPMD")
 */
class TranslationController extends AbstractController
{
    use RedirectBackTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** The CSRF token id of turning translation mode on or off (templates/layout/_translation_mode.html.twig). */
    public const string MODE_TOKEN = 'translation-mode';

    public function __construct(private TranslationModel $translationModel, protected EntityManagerInterface $entityManager, private array $locales)
    {
    }

    #[Route(path: '/admin/translations/edit/{locale}/{code}', name: 'translation_edit', requirements: ['code' => '.+'])] // Update an existing translation for the locale
    public function editTranslation(
        Request $request,
        #[MapEntity(mapping: ['locale' => 'shortCode'])] Language $language,
        string $code,
    ): Response {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        // Check that the volunteer has rights for this language
        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale($language->getShortCode())) {
            return $this->redirectToRoute('translations_locale_code', [
                'locale' => $language->getShortCode(),
                'code' => $code,
            ]);
        }

        // In the side panel of translation mode (?embed=1) the page stays where it is
        if (!$request->query->getBoolean('embed') && !$request->getSession()->has('originalReferrer')) {
            $request->getSession()->set('originalReferrer', $request->headers->get('referer'));
        }

        $translationRepository = $this->entityManager->getRepository(Word::class);
        /** @var Word $original */
        $original = $translationRepository->findOneBy([
            'code' => $code,
            'language' => 'en',
        ]);

        // Check that there is already a translation for this id/code
        /** @var Word $translation */
        $translation = $translationRepository->findOneBy([
            'code' => $code,
            'language' => $language->getShortCode(),
        ]);
        if (null === $translation) {
            return $this->redirectToRoute('translation_add', $this->keepEmbed($request, [
                'code' => $code,
                'locale' => $language->getShortCode(),
            ]));
        }
        $translationRequest = EditTranslationRequest::fromTranslations($original, $translation);

        // how the English text is used on the page decides the field and how the text is stored
        $format = $original->getEffectiveFormat();
        $editForm = $this->createForm(EditTranslationFormType::class, $translationRequest, [
            'format' => $format,
        ]);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            /** @var EditTranslationRequest $data */
            $data = $editForm->getData();
            $invalidMessage = $this->checkIfICUFormatIsValid($data, $data->translatedText);

            if ('' === $invalidMessage) {
                $originalDomain = $translation->getDomain();

                $em = $this->entityManager;
                // Make sure the ID of the translations match
                $translation->setCode($original->getCode());
                $translation->setDomain($data->domain);
                if ('en' === $language->getShortCode()) {
                    // the English text sets the format; not set, it is detected from the new text
                    $translation->setFormat(TranslationFormat::tryFrom((string) $data->format));
                    $format = $translation->getFormat() ?? TranslationFormat::detect((string) $data->translatedText);
                }
                $translation->setSentence($format->prepare((string) $data->translatedText));
                $translation->setUpdated(new DateTime());
                $translation->setAuthor($translator);
                if ('en' === $language->getShortCode()) {
                    $translation->setDescription($data->description);
                    if ($data->isMajorUpdate) {
                        $translation->setMajorUpdate($translation->getUpdated());
                    }
                    $translation->setIsArchived($data->isArchived);
                    $translationAllowed = $data->translationAllowed
                        ? TranslationAllowedType::TRANSLATION_ALLOWED : TranslationAllowedType::TRANSLATION_NOT_ALLOWED;
                    $translation->setTranslationAllowed($translationAllowed);
                } else {
                    // No need for a description as the English original has one
                    $translation->setDescription('');
                }
                $em->persist($translation);
                $em->flush();
                if ($originalDomain !== $translation->getDomain()) {
                    $this->translationModel->updateDomainOfTranslations($translation);
                }
                // a new domain moved the text in every language
                $this->translationModel->updateCachedText(
                    $original->getCode(),
                    $originalDomain !== $translation->getDomain() ? 'en' : $language->getShortCode()
                );
                if ($request->query->getBoolean('embed')) {
                    return $this->savedInPanel($original->getCode(), $language->getShortCode());
                }
                $this->addTranslatedFlash('notice', 'translation.edit', [
                    'translationId' => $original->getCode(),
                    'locale' => $language->getShortCode(),
                ]);

                $referrer = $request->getSession()->get('originalReferrer');
                $request->getSession()->remove('originalReferrer');

                return $this->redirectToPageOrRoute($referrer, $request, 'translations_locale_code', [
                    'locale' => $language->getShortCode(),
                    'type' => 'all',
                ]);
            }
            $editForm->get('translatedText')->addError(new FormError($invalidMessage));
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $editForm->createView(),
            'richtext' => $format->usesEditor(),
            'format' => $format,
            'isMajorUpdate' => $translationRequest->isMajorUpdate,
            // the last change of the English text, if the translation was written before it
            'englishChange' => 'en' !== $language->getShortCode() && $translation->isOlderThanTheChangeOf($original) ? [
                'before' => $original->getPreviousSentence(),
                'after' => $original->getSentence(),
                'changed' => $original->getSentenceChanged(),
            ] : null,
            'submenu' => [
                'active' => 'edit',
                'items' => $this->getSubmenuItems(
                    $language->getShortCode(),
                    'edit',
                    $code
                ),
            ],
        ]);
    }

    #[Route(path: '/admin/translations/create/{locale}/{translationId}', name: 'translation_create', requirements: ['domain' => 'messages|message+intl-icu|validators', 'translationId' => '.+'])] // Creates an English index and the matching translation (if locale != 'en')
    public function createTranslationForId(
        Request $request,
        #[MapEntity(mapping: ['locale' => 'shortCode'])] Language $language,
        string $translationId,
    ): Response {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();

        // Volunteer needs rights for this language and for English
        if (
            !$translator->hasRightsForLocale($language->getShortCode())
            && !$translator->hasRightsForLocale('en')
        ) {
            return $this->redirectToRoute('translations');
        }

        // Check if an English entry already exists
        $translationRepository = $this->entityManager->getRepository(Word::class);

        /** @var Word $englishTranslation */
        $englishTranslation = $translationRepository->findOneBy([
            'code' => $translationId,
            'language' => 'en',
        ]);

        if (null !== $englishTranslation) {
            // There already is a translation ID in the database redirect to edit
            return $this->redirectToRoute('translation_edit', $this->keepEmbed($request, [
                'locale' => 'en',
                'code' => $translationId,
            ]));
        }

        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->entityManager->getRepository(Language::class);
        /** @var Language $english */
        $english = $languageRepository->findOneBy(['shortCode' => 'en']);

        $createTranslationRequest = new TranslationRequest();
        $createTranslationRequest->wordCode = $translationId;
        $createTranslationRequest->locale = $language->getShortCode();
        if ('en' === $language->getShortCode()) {
            // to ensure form validates correctly
            $createTranslationRequest->translatedText = 'safely ignored';
        }
        $createForm = $this->createForm(TranslationFormType::class, $createTranslationRequest);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $em = $this->entityManager;
            /** @var TranslationRequest $data */
            $data = $createForm->getData();
            $invalidMessage = $this->checkIfICUFormatIsValid($data, $data->englishText);
            if ('' === $invalidMessage) {
                $original = $this->generateTranslatableItem($data, $translator, $english);
                $em->persist($original);
                if ('en' !== $createTranslationRequest->locale) {
                    // the translation if one was written, the English text else (as before)
                    $translation = $this->generateTranslatableItem($data, $translator, $language);
                    if ('' !== trim((string) $data->translatedText)) {
                        $translation->setSentence($original->getEffectiveFormat()->prepare((string) $data->translatedText));
                    }
                    $em->persist($translation);
                }
                $em->flush();
                // a new English text: in every language's catalogue (with the translation if there is one)
                $this->translationModel->updateCachedText($original->getCode(), 'en');
                if ($request->query->getBoolean('embed')) {
                    return $this->savedInPanel($translationId, $language->getShortCode());
                }
                $this->addTranslatedFlash('notice', 'flash.added.translatable.item', ['%code%' => $translationId]);

                return $this->redirectToRoute('translations');
            }

            $createForm->get('translatedText')->addError(new FormError($invalidMessage));
        }

        $template = ($english === $language) ?
            'admin/translations/create.en.html.twig' :
            'admin/translations/create.locale.html.twig';

        return $this->render($template, [
            'form' => $createForm->createView(),
            'submenu' => [
                'active' => 'missing',
                'items' => $this->getSubmenuItems($language->getShortCode()),
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/admin/translations/create', name: 'translation_create_direct')] // Creates an new English index bypassing the translation interface
    public function createTranslationDirect(Request $request): Response
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        // Volunteer needs rights for this language and for English
        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale('en')) {
            return $this->redirectToRoute('translations');
        }

        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->entityManager->getRepository(Language::class);
        /** @var Language $english */
        $english = $languageRepository->findOneBy(['shortCode' => 'en']);

        $createTranslationRequest = new TranslationRequest();
        $createTranslationRequest->locale = 'en';
        // to ensure form validates correctly (we force english locale)
        $createTranslationRequest->translatedText = 'safely ignored';
        $createForm = $this->createForm(TranslationFormType::class, $createTranslationRequest);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $em = $this->entityManager;
            /** @var TranslationRequest $data */
            $data = $createForm->getData();
            $invalidMessage = $this->checkIfICUFormatIsValid($data, $data->englishText);
            if ('' === $invalidMessage) {
                $newTranslatableItem = $this->generateTranslatableItem($data, $translator, $english);
                $em->persist($newTranslatableItem);
                $em->flush();
                $this->translationModel->updateCachedText($newTranslatableItem->getCode(), 'en');
                $this->addTranslatedFlash('notice', 'flash.added.translatable.item', ['%code%' => $data->wordCode]);

                return $this->redirectToRoute('translations');
            }
            $createForm->get('englishText')->addError(new FormError($invalidMessage));
        }

        return $this->render('admin/translations/create.en.html.twig', [
            'form' => $createForm->createView(),
            'submenu' => [
                'active' => 'create',
                'items' => $this->getSubmenuItems('en'),
            ],
        ]);
    }

    #[Route(path: '/admin/translations/add/{locale}/{code}', name: 'translation_add', requirements: ['code' => '.+'])] // Adds a missing translation for an existing english index
    public function addTranslation(
        Request $request,
        #[MapEntity(mapping: ['locale' => 'shortCode'])] Language $language,
        string $code,
    ): Response {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();

        // Check that the volunteer has rights for this language
        if (!$translator->hasRightsForLocale($language->getShortCode())) {
            return $this->redirectToRoute('translations_no_permissions', ['locale' => $language->getShortCode()]);
        }

        // We want to redirect to the page where the translation was originally missing
        // In the side panel of translation mode (?embed=1) the page stays where it is
        if (!$request->query->getBoolean('embed') && !$request->getSession()->has('originalReferrer')) {
            $request->getSession()->set('originalReferrer', $request->headers->get('referer'));
        }

        if ('en' === $language->getShortCode()) {
            $this->addTranslatedFlash('notice', 'flash.translation.weird');

            return $this->redirectToRoute('translations');
        }
        $translationRepository = $this->entityManager->getRepository(Word::class);

        $original = $translationRepository->findOneBy([
            'code' => $code,
            'language' => 'en',
        ]);
        if (null === $original) {
            throw $this->createNotFoundException();
        }

        $translation = $translationRepository->findOneBy([
            'code' => $code,
            'language' => $language->getShortCode(),
        ]);

        // Work around a problem in the database
        // Sometimes the word code do not match (case) between translations
        if (null !== $translation) {
            return $this->redirectToRoute('translation_edit', $this->keepEmbed($request, [
                'locale' => $language->getShortCode(),
                'code' => $code,
            ]));
        }

        $translation = new Word();
        $translation->setCode($original->getCode());
        $translation->setLanguage($language);

        $addTranslationRequest = EditTranslationRequest::fromTranslations($original, $translation);

        $format = $original->getEffectiveFormat();
        $addForm = $this->createForm(EditTranslationFormType::class, $addTranslationRequest, [
            'format' => $format,
        ]);

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            /** @var TranslationRequest $data */
            $data = $addForm->getData();
            $invalidMessage = $this->checkIfICUFormatIsValid($data, $data->translatedText);
            if ('' === $invalidMessage) {
                $translation->setDomain($original->getDomain());
                $translation->setSentence($format->prepare((string) $data->translatedText));
                $translation->setLanguage($language);
                $translation->setCreated(new DateTime());
                $translation->setAuthor($translator);
                // No need for a description as the English original has one
                $translation->setDescription('');
                $this->entityManager->persist($translation);
                $this->entityManager->flush();
                $this->translationModel->updateCachedText($original->getCode(), $language->getShortCode());
                if ($request->query->getBoolean('embed')) {
                    return $this->savedInPanel($original->getCode(), $language->getShortCode());
                }
                $this->addTranslatedFlash('notice', 'translation.add', [
                    'translationId' => $original->getCode(),
                    'locale' => $language->getShortCode(),
                ]);

                $referrer = $request->getSession()->get('originalReferrer');
                $request->getSession()->remove('originalReferrer');

                return $this->redirectToPageOrRoute($referrer, $request, 'translations_locale_code', [
                    'locale' => $language->getShortCode(),
                    'type' => 'all',
                ]);
            }
            $addForm->get('translatedText')->addError(new FormError($invalidMessage));
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $addForm->createView(),
            'richtext' => $format->usesEditor(),
            'format' => $format,
            'submenu' => [
                'active' => 'add',
                'items' => $this->getSubmenuItems(
                    $language->getShortCode(),
                    'add',
                    $code
                ),
            ],
        ]);
    }

    /**
     * @return RedirectResponse
     */
    /**
     * Translation mode: the texts of a translation saved in the side panel, rendered with the parameters the page
     * showed them with, so that the page can show them in place.
     */
    #[Route(path: '/admin/translations/render', name: 'translation_render', methods: ['POST'])]
    public function renderTranslation(
        #[MapRequestPayload(acceptFormat: 'json')] RenderTranslationDto $payload,
        InPlaceTranslations $inPlaceTranslations,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        foreach ($payload->parameterSets as $parameters) {
            foreach ($parameters as $value) {
                if (null !== $value && !\is_scalar($value)) {
                    return new JsonResponse(['error' => 'parameters'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }

        $texts = $inPlaceTranslations->render($payload->code, $payload->locale, $payload->parameterSets);
        if (null === $texts) {
            return new JsonResponse(['error' => 'not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['texts' => $texts]);
    }

    /**
     * Turns translation mode on or off (the volunteer menu and the translation tool): the page then lists the texts it
     * shows to translate them in place.
     */
    #[Route(path: '/admin/translations/mode/{mode}', name: 'translation_mode', requirements: ['mode' => 'on|off'], methods: ['POST'])]
    #[IsCsrfTokenValid(self::MODE_TOKEN)]
    public function setTranslationMode(Request $request, string $mode): RedirectResponse
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS);

        if ('on' === $mode) {
            $flashId = 'flash.translation.enabled';
        } else {
            $flashId = 'flash.translation.disabled';
        }
        $this->addTranslatedFlash('notice', $flashId);

        $request->getSession()->set('translation_mode', $mode);

        return $this->redirectBack($request, 'translations');
    }

    /**
     * A translator without the right for a language (?locale): back to their profile, with the languages they can
     * translate.
     */
    #[Route(path: '/admin/translation/no_permissions', name: 'translations_no_permissions')]
    public function translationNoRights(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');
        $translator = $this->getTranslator();
        $name = static fn (string $locale): string => $translator->trans('lang_' . $locale);
        $this->addFlash('notice', $translator->trans('admin.translation.no.right.locale', [
            'language' => $name($request->query->getString('locale', $request->getLocale())),
            'languages' => implode(', ', array_map($name, $this->getTranslatorLocales())),
        ]));

        /** @var Member $member */
        $member = $this->getUser();

        return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
    }

    /**
     * @return RedirectResponse
     */
    #[Route(path: '/admin/translations', name: 'translations')]
    public function translationSwitch(Request $request)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();
        $locale = $this->getToolLocale($request, $translator);

        // what is left to do first; English has nothing missing
        return $this->redirectToRoute('translations_locale_code', [
            'locale' => $locale,
            'type' => 'en' === $locale ? 'all' : 'missing',
        ]);
    }

    #[Route(path: '/admin/translations/{type}/{locale}/{code}', name: 'translations_locale_code', defaults: ['code' => ''], requirements: ['code' => '.+', 'type' => 'missing|update|all|archived|donottranslate'])]
    public function listTranslations(
        Request $request,
        #[MapEntity(mapping: ['locale' => 'shortCode'])] Language $language,
        string $type,
        string $code,
        TranslationTool $translationTool,
    ): Response {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();
        $locale = $language->getShortCode();
        if (!$translator->hasRightsForLocale($locale)) {
            return $this->redirectToRoute('translations_no_permissions', ['locale' => $locale]);
        }

        // the search field: the code goes into the address, as links to a search have it
        if ($request->query->has('q')) {
            return $this->redirectToRoute('translations_locale_code', [
                'type' => $type,
                'locale' => $locale,
                'code' => trim($request->query->getString('q')),
            ]);
        }

        $texts = $translationTool->texts($type, $locale, $code, $request->query->getInt('page', 1));
        $summary = $translationTool->summary($locale, $code);

        return $this->render('admin/translations/tool/list.html.twig', [
            'view' => $type,
            'locale' => $locale,
            'code' => $code,
            'texts' => $texts,
            'rows' => array_map(
                static fn (array $row): array => $translationTool->text($row, $type),
                iterator_to_array($texts->getCurrentPageResults(), false)
            ),
            'summary' => $summary,
            // without the search: the share of the language
            'overview' => '' === $code ? $summary : $translationTool->summary($locale),
            'languages' => $this->getToolLanguages($translationTool, $translator),
        ]);
    }

    #[Route(path: '/admin/translate/statistics', name: 'translation_statistics')]
    public function statistics(Request $request, TranslationTool $translationTool): Response
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();
        $sort = $request->query->getString('sort', 'share');
        $languages = $translationTool->statistics();
        if ('name' === $sort) {
            usort($languages, static fn (array $a, array $b): int => strcmp($a['locale'], $b['locale']));
        } elseif ('recent' === $sort) {
            usort($languages, static fn (array $a, array $b): int => ($b['date'] ?? 0) <=> ($a['date'] ?? 0));
        } else {
            $sort = 'share';
        }
        $rank = [];
        foreach ($translationTool->statistics() as $position => $statistics) {
            $rank[$statistics['locale']] = $position + 1;
        }
        $yours = $translationTool->languagesOf($translator) ?? [];

        return $this->render('admin/translations/tool/statistics.html.twig', [
            'locale' => $this->getToolLocale($request, $translator),
            'sort' => $sort,
            'languages' => $languages,
            'rank' => $rank,
            'yours' => array_values(array_filter($languages, static fn (array $language): bool => \in_array($language['locale'], $yours, true))),
            'yourLocales' => $yours,
            'total' => $translationTool->summary('en')['total'],
            'languageMenu' => $this->getToolLanguages($translationTool, $translator),
        ]);
    }

    /**
     * @param mixed|null $action
     * @param mixed|null $code
     *
     * @return array
     */
    protected function getSubmenuItems($locale, $action = null, $code = null)
    {
        /** @var Member $translator */
        $translator = $this->getUser();
        $submenuItems = [
            'all' => [
                'key' => 'label.translations.all',
                'url' => $this->generateUrl('translations_locale_code', [
                    'locale' => $locale,
                    'type' => 'all',
                ]),
            ],
            'missing' => [
                'key' => 'label.translations.missing',
                'url' => $this->generateUrl('translations_locale_code', [
                    'locale' => $locale,
                    'type' => 'missing',
                ]),
            ],
        ];
        if ('en' !== $locale) {
            $submenuItems['update'] = [
                'key' => 'label.translations.update_needed',
                'url' => $this->generateUrl('translations_locale_code', [
                    'locale' => $locale,
                    'type' => 'update',
                ]),
            ];
        }
        if ($translator->hasRightsForLocale('en')) {
            $submenuItems['archived'] = [
                'key' => 'label.translations.archived',
                'url' => $this->generateUrl('translations_locale_code', [
                    'locale' => $locale,
                    'type' => 'archived',
                ]),
            ];
            $submenuItems['donottranslate'] = [
                'key' => 'label.translations.donottranslate',
                'url' => $this->generateUrl('translations_locale_code', [
                    'locale' => $locale,
                    'type' => 'donottranslate',
                ]),
            ];
            $submenuItems['create'] = [
                'key' => 'label.translations.create',
                'url' => $this->generateUrl('translation_create_direct'),
            ];
        }
        $submenuItems['mockups'] = [
            'key' => 'label.translations.mockups',
            'url' => $this->generateUrl('translations_mockups'),
        ];
        $submenuItems['group'] = [
            'key' => 'label.translations.group',
            'url' => $this->generateUrl('group_start', ['group_id' => 60]),
        ];
        $submenuItems['wiki'] = [
            'key' => 'label.translations.wiki',
            'url' => $this->generateUrl('group_wiki_page', ['id' => 60]),
        ];
        $submenuItems['statistics'] = [
            'key' => 'label.translations.statistics',
            'url' => $this->generateUrl('translation_statistics'),
        ];
        if ($action && 'create' !== $action && 'mockup' !== $action) {
            $submenuItems[$action] = [
                'key' => 'label.translations.' . $action,
                'url' => $this->generateUrl('translation_' . $action, [
                    'locale' => $locale,
                    'code' => $code,
                ]),
            ];
        }

        return $submenuItems;
    }

    /**
     * The language the tool opens in: the site's language if the member translates it, else their first one.
     */
    protected function getToolLocale(Request $request, Member $translator): string
    {
        $locale = $request->getLocale();
        if ($translator->hasRightsForLocale($locale)) {
            return $locale;
        }

        foreach ($this->locales as $candidate) {
            if ($translator->hasRightsForLocale($candidate)) {
                return $candidate;
            }
        }

        return $locale;
    }

    /**
     * The languages of the language menu: the member's, with the share translated.
     *
     * @return list<array{locale: string, share: float}>
     */
    protected function getToolLanguages(TranslationTool $translationTool, Member $translator): array
    {
        $languages = [];
        foreach ($translationTool->statistics() as $statistics) {
            if ($translator->hasRightsForLocale($statistics['locale'])) {
                $languages[] = ['locale' => $statistics['locale'], 'share' => $statistics['share']];
            }
        }

        return $languages;
    }

    /**
     * Returns the locales that the user is allowed to translate.
     *
     * @return string[]
     */
    private function getTranslatorLocales(): array
    {
        /** @var Member $volunteer */
        $volunteer = $this->getUser();

        /** @var RightVolunteer $wordRight */
        $wordRight = $volunteer->getVolunteerRights()->filter(static function (RightVolunteer $volunteerRight) {
            return 'Words' === $volunteerRight->getRight()->getName();
        })->first();

        $scope = array_values(array_filter(array_map(
            'trim',
            preg_split('/[,;]/', str_replace('"', '', $wordRight->getScope())) ?: [],
        )));
        if (\in_array('All', $scope, true)) {
            return ['this', 'should', 'never', 'happen'];
        }

        return $scope;
    }

    /**
     * A new text for the code in the language with the English text; the English one gets the format.
     */
    private function generateTranslatableItem(TranslationRequest $data, Member $translator, Language $language): Word
    {
        $format = TranslationFormat::tryFrom((string) $data->format);
        $original = new Word();
        $original->setCode($data->wordCode);
        $original->setDomain($data->domain);
        $original->setDescription($data->description);
        $original->setSentence(($format ?? TranslationFormat::detect((string) $data->englishText))->prepare((string) $data->englishText));
        if ('en' === $language->getShortCode()) {
            $original->setFormat($format);
        }
        $original->setCreated(new DateTime());
        $original->setAuthor($translator);
        $original->setLanguage($language);

        return $original;
    }

    private function checkIfICUFormatIsValid(TranslationRequest $data, $message): string
    {
        $invalidMessage = '';
        if (DomainType::ICU_MESSAGES === $data->domain) {
            try {
                new MessageFormatter('en', $message);
            } catch (Exception $exception) {
                $invalidMessage = $exception->getMessage();
            }
        }

        return $invalidMessage;
    }

    /**
     * The route parameters, with ?embed=1 when the page is in the side panel of translation mode.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    private function keepEmbed(Request $request, array $parameters): array
    {
        if ($request->query->getBoolean('embed')) {
            $parameters['embed'] = 1;
        }

        return $parameters;
    }

    /**
     * Saved in the side panel of translation mode: tells the page which text changed.
     */
    private function savedInPanel(string $code, string $locale): Response
    {
        return $this->render('admin/translations/embed_saved.html.twig', [
            'code' => $code,
            'locale' => $locale,
        ]);
    }
}
