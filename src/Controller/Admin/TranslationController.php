<?php

namespace App\Controller\Admin;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Activity;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\HostingRequest;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\RightVolunteer;
use App\Entity\Word;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\CustomDataClass\Translation\EditTranslationRequest;
use App\Form\CustomDataClass\Translation\TranslationRequest;
use App\Form\EditTranslationFormType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Form\SearchFormType;
use App\Form\TranslationFormType;
use App\Model\TranslationModel;
use App\Repository\LanguageRepository;
use App\Repository\WordRepository;
use App\Twig\MockupExtension;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Exception;
use Mockery;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TranslationController.
 *
 * @SuppressWarnings(PHPMD)
 */
class TranslationController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** @var TranslationModel */
    private $translationModel;

    public function __construct(TranslationModel $translationModel)
    {
        $this->translationModel = $translationModel;
    }

    /**
     * @Route("/admin/translations/edit/{locale}/{code}", name="translation_edit",
     *     requirements={"code"=".+"}))
     *
     * Update an existing translation for the locale
     *
     * @param mixed $code
     *
     * @throws Exception
     *
     * @return Response
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function editTranslation(
        Request $request,
        Language $language,
        $code
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        // Check that the volunteer has rights for this language
        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale($language->getShortcode())) {
            return $this->redirectToRoute('translations_locale_code', [
                'locale' => $language->getShortcode(),
                'code' => $code,
            ]);
        }

        if (!$request->getSession()->has('originalReferrer')) {
            $request->getSession()->set('originalReferrer', $request->headers->get('referer'));
        }

        $translationRepository = $this->getDoctrine()->getRepository(Word::class);
        /** @var Word $original */
        $original = $translationRepository->findOneBy([
            'code' => $code,
            'shortCode' => 'en',
        ]);

        // Check that there is already a translation for this id/code
        /** @var Word $translation */
        $translation = $translationRepository->findOneBy([
           'code' => $code,
           'shortCode' => $language->getShortCode(),
        ]);
        if (null === $translation) {
            return $this->redirectToRoute('translation_add', [
                'code' => $code,
                'locale' => $language->getShortcode(),
            ]);
        }
        $translationRequest = EditTranslationRequest::fromTranslations($original, $translation);

        $richtext = ($original->getSentence() !== strip_tags($original->getSentence()));
        $editForm = $this->createForm(EditTranslationFormType::class, $translationRequest, [
            'richtext' => $richtext,
        ]);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            /** @var EditTranslationRequest $data */
            $data = $editForm->getData();
            $originalDomain = $translation->getDomain();

            $em = $this->getDoctrine()->getManager();
            // Make sure the ID of the translations match
            $translation->setCode($original->getCode());
            $translation->setDomain($data->domain);
            $translation->setSentence($data->translatedText);
            $translation->setUpdated(new DateTime());
            $translation->setAuthor($translator);
            if ('en' === $language->getShortcode()) {
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
            if ('en' === $language->getShortcode()) {
                $this->translationModel->refreshTranslationsCache();
            } else {
                $this->translationModel->refreshTranslationsCacheForLocale($language->getShortCode());
            }
            $this->addTranslatedFlash('notice', 'translation.edit', [
                'translationId' => $original->getCode(),
                'locale' => $language->getShortcode(),
            ]);

            $referrer = $request->getSession()->get('originalReferrer');
            $request->getSession()->remove('originalReferrer');

            return $this->redirect($referrer);
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $editForm->createView(),
            'richtext' => $richtext,
            'isMajorUpdate' => $translationRequest->isMajorUpdate,
            'submenu' => [
                'active' => 'edit',
                'items' => $this->getSubmenuItems(
                    $language->getShortcode(),
                    'edit',
                    $code
                ),
            ],
        ]);
    }

    /**
     * @Route("/admin/translations/create/{locale}/{translationId}", name="translation_create",
     *     requirements={"domain"="messages|message+intl-icu|validators", "translationId"=".+"}))
     *
     * Creates an English index and the matching translation (if locale != 'en')
     *
     * @param mixed $translationId
     *
     * @throws Exception
     *
     * @return Response
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function createTranslationForId(
        Request $request,
        Language $language,
        $translationId
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();

        // Volunteer needs rights for this language and for English
        if (
            !$translator->hasRightsForLocale($language->getShortcode())
            && !$translator->hasRightsForLocale('en')
        ) {
            return $this->redirectToRoute('translations');
        }

        // Check if an English entry already exists
        $translationRepository = $this->getDoctrine()->getRepository(Word::class);
        /** @var Word $original */
        $englishTranslation = $translationRepository->findOneBy([
            'code' => $translationId,
            'shortCode' => 'en',
        ]);

        if (null !== $englishTranslation) {
            // There already is a translation ID in the database redirect to edit
            return $this->redirectToRoute('translation_edit', [
                'locale' => 'en',
                'code' => $translationId,
            ]);
        }

        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->getDoctrine()->getRepository(Language::class);
        /** @var Language $english */
        $english = $languageRepository->findOneBy(['shortcode' => 'en']);

        $createTranslationRequest = new TranslationRequest();
        $createTranslationRequest->wordCode = $translationId;
        $createTranslationRequest->locale = $language->getShortcode();
        if ('en' === $language->getShortcode()) {
            // to ensure form validates correctly
            $createTranslationRequest->translatedText = 'safely ignored';
        }
        $createForm = $this->createForm(TranslationFormType::class, $createTranslationRequest);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var TranslationRequest $data */
            $data = $createForm->getData();
            $original = $this->generateTranslatableItem($data, $translator, $english);
            $em->persist($original);
            if ('en' !== $createTranslationRequest->locale) {
                $translation = $this->generateTranslatableItem($data, $translator, $language);
                $em->persist($translation);
            }
            $em->flush();
            $this->translationModel->refreshTranslationsCacheForLocale($language->getShortCode());
            $this->addTranslatedFlash('notice', 'flash.added.translatable.item', ['%code%' => $translationId]);

            return $this->redirectToRoute('translations');
        }

        $template = ($english === $language) ?
            'admin/translations/create.en.html.twig' :
            'admin/translations/create.locale.html.twig';

        return $this->render($template, [
            'form' => $createForm->createView(),
            'submenu' => [
                'active' => 'missing',
                'items' => $this->getSubmenuItems($language->getShortcode()),
            ],
        ]);
    }

    /**
     * @Route("/admin/translations/create", name="translation_create_direct")
     *
     * Creates an new English index bypassing the translation interface
     *
     * @throws Exception
     *
     * @return Response
     */
    public function createTranslationDirect(
        Request $request
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        // Volunteer needs rights for this language and for English
        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale('en')) {
            return $this->redirectToRoute('translations');
        }

        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->getDoctrine()->getRepository(Language::class);
        /** @var Language $english */
        $english = $languageRepository->findOneBy(['shortcode' => 'en']);

        $createTranslationRequest = new TranslationRequest();
        $createTranslationRequest->locale = 'en';
        // to ensure form validates correctly (we force english locale)
        $createTranslationRequest->translatedText = 'safely ignored';
        $createForm = $this->createForm(TranslationFormType::class, $createTranslationRequest);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var TranslationRequest $data */
            $data = $createForm->getData();
            $newTranslatableItem = $this->generateTranslatableItem($data, $translator, $english);
            $em->persist($newTranslatableItem);
            $em->flush();
            $this->translationModel->refreshTranslationsCacheForLocale('en');
            $this->addTranslatedFlash('notice', 'flash.added.translatable.item', ['%code%' => $data->wordCode]);

            return $this->redirectToRoute('translations');
        }

        return $this->render('admin/translations/create.en.html.twig', [
            'form' => $createForm->createView(),
            'submenu' => [
                'active' => 'create',
                'items' => $this->getSubmenuItems('en'),
            ],
        ]);
    }

    /**
     * @Route("/admin/translations/add/{locale}/{code}", name="translation_add",
     *     requirements={"code"=".+"}))
     *
     * Adds a missing translation for an existing english index
     *
     * @param mixed $code
     *
     * @throws Exception
     *
     * @return Response
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function addTranslation(Request $request, Language $language, $code)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        if (!$request->getSession()->has('originalReferrer')) {
            $request->getSession()->set('originalReferrer', $request->headers->get('referer'));
        }

        /** @var Member $translator */
        $translator = $this->getUser();

        // Check that the volunteer has rights for this language
        if (!$translator->hasRightsForLocale($language->getShortcode())) {
            return $this->redirectToRoute('translations_no_permissions');
        }

        if ('en' === $language->getShortcode()) {
            $this->addTranslatedFlash('notice', 'flash.translation.weird');
            $this->redirectToRoute('translations');
        }
        $translationRepository = $this->getDoctrine()->getRepository(Word::class);

        /** @var Word $original */
        $original = $translationRepository->findOneBy([
            'code' => $code,
            'shortCode' => 'en',
        ]);
        $translation = $translationRepository->findOneBy([
            'code' => $code,
            'shortCode' => $language->getShortcode(),
        ]);

        // Work around a problem in the database
        // Sometimes the word code do not match between translations
        if (null !== $translation) {
            return $this->redirectToRoute('translation_edit', [
                'locale' => $language->getShortCode(),
                'code' => $code,
            ]);
        }

        $translation = new Word();
        $translation->setCode($original->getCode());
        $translation->setLanguage($language);

        $addTranslationRequest = EditTranslationRequest::fromTranslations($original, $translation);

        $addForm = $this->createForm(EditTranslationFormType::class, $addTranslationRequest);

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $data = $addForm->getData();
            $em = $this->getDoctrine()->getManager();
            $translation->setDomain($original->getDomain());
            $translation->setSentence($data->translatedText);
            $translation->setLanguage($language);
            $translation->setCreated(new DateTime());
            $translation->setAuthor($translator);
            // No need for a description as the English original has one
            $translation->setDescription('');
            $em->persist($translation);
            $em->flush();
            $this->translationModel->refreshTranslationsCacheForLocale($language->getShortCode());
            $this->addTranslatedFlash('notice', 'translation.add', [
                'translationId' => $original->getCode(),
                'locale' => $language->getShortcode(),
            ]);

            $referrer = $request->getSession()->get('originalReferrer');
            $request->getSession()->remove('originalReferrer');

            return $this->redirect($referrer);
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $addForm->createView(),
            'submenu' => [
                'active' => 'add',
                'items' => $this->getSubmenuItems(
                    $language->getShortcode(),
                    'add',
                    $code
                ),
            ],
        ]);
    }

    /**
     * @Route("/admin/translations/mode/{mode}", name="translation_mode",
     *     requirements={"mode": "on|off"}
     * )
     *
     * @param $mode
     *
     * @return RedirectResponse
     */
    public function setTranslationMode(Request $request, $mode)
    {
        if ('on' === $mode) {
            $flashId = 'flash.translation.enabled';
        } else {
            $flashId = 'flash.translation.disabled';
        }
        $this->addTranslatedFlash('notice', $flashId);

        $this->get('session')->set('translation_mode', $mode);
        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * @Route("/admin/translation/no_permissions", name="translations_no_permissions")
     *
     * @return Response
     */
    public function translationNoRights(Request $request)
    {
        $locale = $request->getLocale();
        $locales = $this->getTranslatorLocales();

        return $this->render('admin/translations/no.right.html.twig', [
            'locale' => $locale,
            'locales' => $locales,
        ]);
    }

    /**
     * @Route("/admin/translations", name="translations")
     *
     * @return RedirectResponse
     */
    public function translationSwitch(Request $request)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $locale = $request->getLocale();

        return $this->redirectToRoute('translations_locale_code', [
            'locale' => $locale,
            'type' => 'all',
        ]);
    }

    /**
     * @Route("/admin/translations/{type}/{locale}/{code}", name="translations_locale_code",
     *     defaults={"code":""},
     *     requirements={"code"=".+", "type"="missing|update|all|archived|donottranslate"})
     *
     * @param $code
     * @param mixed $type
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     *
     * @return RedirectResponse|Response
     */
    public function listTranslations(Request $request, Language $language, $type, $code)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale($language->getShortcode())) {
            return $this->redirectToRoute('translations_no_permissions');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);

        $locale = $language->getShortcode();

        $form = $this->createFormBuilder(['wordCode' => $code])
            ->setMethod('GET')
            ->add('wordCode', TextType::class, [
                'label' => 'translation.id',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('search', SubmitType::class, [
                'label' => 'search',
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newCode = $data['wordCode'];
            if ($code !== $newCode) {
                $page = 1;
                $code = $newCode;
            }
        }

        $translationAdapter = $this->translationModel->getAdapter($type, $locale, $code);

        $translations = new Pagerfanta($translationAdapter);
        $translations->setMaxPerPage($limit);
        $translations->setCurrentPage($page);

        /** @var WordRepository $translationRepository */
        $translationRepository = $this->getDoctrine()->getRepository(Word::class);
        $countAll = $translationRepository->getTranslatableItemsCount('en');
        $countTranslated = $translationRepository->getTranslatableItemsCount($language->getShortcode());

        return $this->render('admin/translations/list.html.twig', [
            'type' => $type,
            'form' => $form->createView(),
            'code' => $code,
            'locale' => $locale,
            'count_all' => $countAll,
            'count_translated' => $countTranslated,
            'routeName' => 'translations_locale_code',
            'routeParams' => array_merge(
                ['type' => $type, 'code' => $code, 'locale' => $locale],
                $request->query->all()
            ),
            'translations' => $translations,
            'submenu' => [
                'active' => $type,
                'items' => $this->getSubmenuItems($locale),
            ],
        ]);
    }

    /**
     * @Route("/admin/translate/statistics", name="translation_statistics")
     *
     * @return Response
     */
    public function statistics(Request $request)
    {
        /** @var WordRepository $translationRepository */
        $translationRepository = $this->getDoctrine()->getRepository(Word::class);
        $countAll = $translationRepository->getTranslatableItemsCount('en');
        $translationDetails = $translationRepository->getTranslationDetails();

        return $this->render('admin/translations/statistics.html.twig', [
                'count_all' => $countAll,
                'details' => $translationDetails,
                'submenu' => [
                    'active' => 'statistics',
                    'items' => $this->getSubmenuItems($request->getLocale(), 'statistics'),
                ],
            ]);
    }

    /**
     * @param $locale
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
            $submenuItems['needs_update'] = [
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
     * Returns the locales that the user is allowed to translate.
     *
     * @return string[]
     */
    private function getTranslatorLocales(): array
    {
        $volunteer = $this->getUser();

        /** @var RightVolunteer $wordRight */
        $wordRight = $volunteer->getVolunteerRights()->filter(function (RightVolunteer $volunteerRight) {
            return 'Words' === $volunteerRight->getRight()->getName();
        })->first();

        $scope = preg_split('/[,;]/', str_replace('"', '', $wordRight->getScope()));
        if (\in_array('All', $scope, true)) {
            return ['this', 'should', 'never', 'happen'];
        }

        return $scope;
    }

    private function generateTranslatableItem(TranslationRequest $data, Member $translator, Language $english): Word
    {
        $original = new Word();
        $original->setCode($data->wordCode);
        $original->setDomain($data->domain);
        $original->setDescription($data->description);
        $original->setSentence($data->englishText);
        $original->setCreated(new DateTime());
        $original->setAuthor($translator);
        $original->setLanguage($english);

        return $original;
    }
}
