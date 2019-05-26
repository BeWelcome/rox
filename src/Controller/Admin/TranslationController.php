<?php

namespace App\Controller\Admin;

use App\Entity\Language;
use App\Entity\Member;
use App\Entity\RightVolunteer;
use App\Entity\Word;
use App\Form\CustomDataClass\Translation\CreateTranslationRequest;
use App\Form\CustomDataClass\Translation\EditTranslationRequest;
use App\Form\EditTranslationFormType;
use App\Form\TranslationFormType;
use App\Kernel;
use App\Model\TranslationModel;
use App\Pagerfanta\TranslationAdapter;
use App\Repository\LanguageRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;

/**
 * Class TranslationController.
 *
 * @SuppressWarnings(PHPMD)
 */
class TranslationController extends AbstractController
{
    /** @var TranslationModel */
    private $translationModel;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator, TranslationModel $translationModel)
    {
        $this->translationModel = $translationModel;
        $this->translator = $translator;
    }

    /**
     * @Route("/admin/translations/edit/{locale}/{code}", name="translation_edit",
     *     requirements={"code"=".+"}))
     *
     * Update an existing translation for the locale
     *
     * @param Request         $request
     * @param Language        $language
     * @param KernelInterface $kernel
     * @param mixed           $code
     *
     * @throws Exception
     *
     * @return Response
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function editTranslationAction(
        Request $request,
        Language $language,
        KernelInterface $kernel,
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

        $editForm = $this->createForm(EditTranslationFormType::class, $translationRequest);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $data = $editForm->getData();
            $em = $this->getDoctrine()->getManager();
            // Make sure the ID of the translations match
            $translation->setCode($original->getCode());
            $translation->setSentence($data->translatedText);
            $translation->setUpdated(new DateTime());
            $translation->setAuthor($this->getUser());
            if ('en' === $language->getShortcode()) {
                $translation->setDescription($data->description);
            } else {
                // No need for a description as the English original has one
                $translation->setDescription('');
            }
            $em->persist($translation);
            $em->flush();
            $this->translationModel->removeCacheFiles($kernel);
            $this->addFlash('notice', $this->translator->trans('translation.edit'));

            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $editForm->createView(),
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
     * @Route("/admin/translations/create/{locale}/{code}", name="translation_create",
     *     requirements={"code"=".+"}))
     *
     * Creates an English index and the matching translation (if locale != 'en')
     *
     * @param Request             $request
     * @param Language            $language
     * @param KernelInterface     $kernel
     * @param TranslatorInterface $translator
     * @param mixed               $code
     *
     * @throws Exception
     *
     * @return Response
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function createTranslationAction(
        Request $request,
        Language $language,
        KernelInterface $kernel,
        TranslatorInterface $translator,
        $code
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $translator = $this->getUser();

        // Volunteer needs rights for this language and for English
        if (!$translator->hasRightsForLocale($language->getShortcode())
            && !$translator->hasRightsForLocale('en')) {
            return $this->redirectToRoute('translations');
        }

        // Check if an English entry already exists
        $translationRepository = $this->getDoctrine()->getRepository(Word::class);
        /** @var Word $original */
        $englishTranslation = $translationRepository->findOneBy([
            'code' => $code,
            'shortCode' => 'en',
        ]);

        if (null !== $englishTranslation) {
            // There already is a translation ID in the database redirect to edit
            return $this->redirectToRoute('translation_edit', [
                'locale' => 'en',
                'code' => $code,
            ]);
        }

        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->getDoctrine()->getRepository(Language::class);
        /** @var Language $english */
        $english = $languageRepository->findOneBy(['shortcode' => 'en']);

        $createTranslationRequest = new CreateTranslationRequest();
        $createTranslationRequest->wordCode = $code;
        $createTranslationRequest->locale = $language->getShortcode();
        if ('en' === $language->getShortcode()) {
            // to ensure form validates correctly
            $createTranslationRequest->translatedText = 'safely ignored';
        }
        $createForm = $this->createForm(TranslationFormType::class, $createTranslationRequest);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var CreateTranslationRequest $data */
            $data = $createForm->getData();
            $original = new Word();
            $original->setCode($data->wordCode);
            $original->setDescription($data->description);
            $original->setSentence($data->englishText);
            $original->setCreated(new DateTime());
            $original->setAuthor($translator);
            $original->setLanguage($english);
            $em->persist($original);
            if ('en' !== $createTranslationRequest->locale) {
                $translation = new Word();
                $translation->setCode($data->wordCode);
                $translation->setDescription($data->description);
                $translation->setSentence($data->translatedText);
                $translation->setCreated(new DateTime());
                $translation->setAuthor($translator);
                $translation->setLanguage($language);
                $em->persist($translation);
            }
            $em->flush();
            $this->translationModel->removeCacheFiles($kernel);
            $flashMessage = $this->translator->trans('flash.added.translatable.item', ['%code%' => $code]);
            $this->addFlash('notice', $flashMessage);

            return $this->redirectToRoute('translations');
        }

        return $this->render('admin/translations/create.html.twig', [
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
     * @param Request             $request
     * @param KernelInterface     $kernel
     * @param TranslatorInterface $translator
     * @param mixed               $code
     *
     * @throws Exception
     *
     * @return Response
     */
    public function createTranslationDirectAction(
        Request $request,
        KernelInterface $kernel
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        // Volunteer needs rights for this language and for English
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale('en')) {
            return $this->redirectToRoute('translations');
        }

        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->getDoctrine()->getRepository(Language::class);
        /** @var Language $english */
        $english = $languageRepository->findOneBy(['shortcode' => 'en']);

        $createTranslationRequest = new CreateTranslationRequest();
        $createTranslationRequest->locale = 'en';
        // to ensure form validates correctly (we force english locale)
        $createTranslationRequest->translatedText = 'safely ignored';
        $createForm = $this->createForm(TranslationFormType::class, $createTranslationRequest);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var CreateTranslationRequest $data */
            $data = $createForm->getData();
            $newTranslatableItem = new Word();
            $newTranslatableItem->setCode($data->wordCode);
            $newTranslatableItem->setDescription($data->description);
            $newTranslatableItem->setSentence($data->englishText);
            $newTranslatableItem->setCreated(new DateTime());
            $newTranslatableItem->setAuthor($translator);
            $newTranslatableItem->setLanguage($english);
            $em->persist($newTranslatableItem);
            $em->flush();
            $this->translationModel->removeCacheFiles($kernel);
            $flashMessage = $this->translator->trans('flash.added.translatable.item', ['%code%' => $data->wordCode]);
            $this->addFlash('notice', $flashMessage);

            return $this->redirectToRoute('translations');
        }

        return $this->render('admin/translations/create.html.twig', [
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
     * @param Request         $request
     * @param Language        $language
     * @param KernelInterface $kernel
     * @param mixed           $code
     *
     * @throws Exception
     *
     * @return Response
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function addTranslationAction(Request $request, Language $language, KernelInterface $kernel, $code)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $ranslator */
        $translator = $this->getUser();

        // Check that the volunteer has rights for this language
        if (!$translator->hasRightsForLocale($language->getShortcode())) {
            return $this->redirectToRoute('translations_no_rights');
        }

        if ('en' === $language->getShortcode()) {
            $this->addFlash('notice', $translator->trans('flash.translation.weird'));
            $this->redirectToRoute('translations');
        }
        $translationRepository = $this->getDoctrine()
            ->getRepository(Word::class);
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
            $translation->setSentence($data->translatedText);
            $translation->setLanguage($language);
            $translation->setCreated(new DateTime());
            $translation->setAuthor($translator);
            // No need for a description as the English original has one
            $translation->setDescription('');
            $em->persist($translation);
            $em->flush();
            $this->translationModel->removeCacheFiles($kernel);
            $this->addFlash('notice', $this->translator->trans('translation.edit'));

            $referrer = $request->headers->get('referer');

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
     * @param Request             $request
     * @param TranslatorInterface $translator
     * @param $mode
     *
     * @return RedirectResponse
     */
    public function setTranslationModeAction(Request $request, $mode)
    {
        if ('on' === $mode) {
            $flashId = 'flash.translation.enabled';
        } else {
            $flashId = 'flash.translation.disabled';
        }
        $this->addFlash('notice', $this->translator->trans($flashId));

        $this->get('session')->set('translation_mode', $mode);
        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * @Route("/admin/translation/no_permissions", name="translations_no_rights")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function translationNoRightsAction(Request $request)
    {
        $locale = $request->getLocale();
        $locales = $this->getUserLocales();

        return $this->render('admin/translations/no.right.html.twig', [
            'locale' => $locale,
            'locales' => $locales,
        ]);
    }

    /**
     * @Route("/admin/translations", name="translations")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function translationSwitchAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $locale = $request->getLocale();

        return $this->redirectToRoute('translations_locale_code', [
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("/admin/translations/{type}/{locale}/{code}", name="translations_locale",
     *     defaults={"code":""},
     *     requirements={"code"=".+", "type"="missing|all"})
     *
     * @param Request  $request
     * @param Language $language
     * @param $code
     * @param mixed $type
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     *
     * @return RedirectResponse|Response
     */
    public function listTranslationsAction(Request $request, Language $language, $type, $code)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale($language->getShortcode())) {
            return $this->redirectToRoute('translations_no_rights');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);
        $locale = $language->getShortcode();

        $form = $this->createFormBuilder(['wordCode' => $code])
            ->add('wordCode', TextType::class, [
                'label' => 'translation.id',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('search', SubmitType::class)
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

        /** @var Connection $connection */
        $connection = $this->getDoctrine()->getConnection();
        $translationAdapter = new TranslationAdapter($connection, $locale, $code);
        $translations = new Pagerfanta($translationAdapter);
        $translations->setMaxPerPage($limit);
        $translations->setCurrentPage($page);

        return $this->render('admin/translations/list.html.twig', [
            'type' => $type,
            'form' => $form->createView(),
            'code' => $code,
            'locale' => $locale,
            'translations' => $translations,
            'submenu' => [
                'active' => 'all',
                'items' => $this->getSubmenuItems($locale),
            ],
        ]);
    }

    /**
     * Returns the locales that the user is allowed to translate.
     *
     * @return string[]
     */
    private function getUserLocales()
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

    /**
     * @param $locale
     * @param mixed|null $action
     * @param mixed|null $code
     *
     * @return array
     */
    private function getSubmenuItems($locale, $action = null, $code = null)
    {
        /** @var Member $translator */
        $translator = $this->getUser();
        $submenuItems = [
            'all' => [
                'key' => 'label.translations.all',
                'url' => $this->generateUrl('translations_locale_code', ['locale' => $locale]),
            ],
            'missing' => [
                'key' => 'label.translations.missing',
                'url' => $this->generateUrl('translations_locale_missing', ['locale' => $locale]),
            ],
        ];
        if ($translator->hasRightsForLocale('en')) {
            $submenuItems['create'] = [
                'key' => 'label.translations.create',
                'url' => $this->generateUrl('translation_create_direct'),
            ];
        }
        if ($action && 'create' !== $action) {
            $submenuItems[$action] = [
                'key' => 'label.translations.'.$action,
                'url' => $this->generateUrl('translation_'.$action, [
                    'locale' => $locale,
                    'code' => $code,
                ]),
            ];
        }

        return $submenuItems;
    }
}
