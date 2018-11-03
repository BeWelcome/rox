<?php

namespace App\Controller\Admin;

use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Word;
use App\Form\CustomDataClass\Translation\CreateTranslationRequest;
use App\Form\CustomDataClass\Translation\EditTranslationRequest;
use App\Form\EditTranslationFormType;
use App\Form\TranslationFormType;
use App\Model\TranslationModel;
use App\Repository\WordRepository;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TranslationController.
 *
 * @SuppressWarnings(PHPMD)
 */
class TranslationController extends Controller
{
    /** @var TranslationModel */
    private $translationModel;

    public function __construct()
    {
        $this->translationModel = new TranslationModel();
    }

    /**
     * @Route("/admin/translations", name="translations")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listTranslationsAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);
        $locale = $this->get('session')->get('locale');
        /** @var WordRepository $translationRepository */
        $translationRepository = $this->getDoctrine()
            ->getRepository(Word::class);
        $translations = $translationRepository
            ->paginateTranslations($locale, $page, $limit);

        return $this->render('admin/translations/list.html.twig', [
            'translations' => $translations,
        ]);
    }

    /**
     * @Route("/admin/translations/{locale}/{code}/edit", name="translation_edit")
     *
     * Update an existing translation for the locale
     *
     * @param Request  $request
     * @param Language $language
     * @param mixed    $code
     *
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     *
     * @return Response
     */
    public function editTranslationAction(Request $request, Language $language, $code)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $translationRepository = $this->getDoctrine()
            ->getRepository(Word::class);
        /** @var Word $original */
        $original = $translationRepository->findOneBy([
            'code' => $code,
            'shortCode' => 'en',
        ]);
        /** @var Word $translation */
        $translation = $translationRepository->findOneBy([
           'code' => $code,
           'shortCode' => $language->getShortCode(),
        ]);
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
            // No need for a description as the English original has one
            $translation->setDescription('');
            $em->persist($translation);
            $em->flush();
            $this->translationModel->removeCacheFile($this->getParameter('kernel.cache_dir'), $language->getShortcode());
            $this->addFlash('notice', 'translation.edit');
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/translations/create/{code}/{locale}", name="translation_create")
     *
     * Creates an English index and the matching translation (if locale != 'en')
     *
     * @param Request  $request
     * @param Language $language
     * @param mixed    $code
     *
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     *
     * @return Response
     */
    public function createTranslationAction(Request $request, $language, $code)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $languageRepository = $em->getRepository(Language::class);
        /** @var Language $english */
        $english = $languageRepository->findOneBy(['shortcode' => 'en']);

        $createTranslationRequest = new CreateTranslationRequest();
        $createTranslationRequest->wordCode = $code;
        $createTranslationRequest->locale = $language->getShortcode();
        $createTranslationRequest->translatedText = ('en' === $createTranslationRequest->locale) ? 'not needed' : '';

        $createForm = $this->createForm(TranslationFormType::class, $createTranslationRequest);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            /** @var CreateTranslationRequest $data */
            $data = $createForm->getData();
            $original = new Word();
            $original->setCode($data->wordCode);
            $original->setDescription($data->description);
            $original->setSentence($data->englishText);
            $original->setCreated(new DateTime());
            $original->setAuthor($user);
            $original->setLanguage($english);
            $em->persist($original);
            if ('en' !== $createTranslationRequest->locale) {
                $translation = new Word();
                $translation->setCode($data->wordCode);
                $translation->setDescription($data->description);
                $translation->setSentence($data->translatedText);
                $translation->setCreated(new DateTime());
                $translation->setAuthor($user);
                $translation->setLanguage($language);
                $em->persist($translation);
            }
            $em->flush();
            $this->translationModel->removeCacheFile($this->getParameter('kernel.cache_dir'), 'en');
            $this->translationModel->removeCacheFile($this->getParameter('kernel.cache_dir'), $language->getShortcode());
            $this->addFlash('notice', 'Added translatable item '.$code);

            return $this->redirectToRoute('translations');
        }

        return $this->render('admin/translations/create.html.twig', [
            'form' => $createForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/translations/{locale}/{code}/add", name="translation_add")
     *
     * Adds a missing translation for an existing english index
     *
     * @param Request  $request
     * @param Language $language
     * @param mixed    $code
     *
     * @return Response
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function addTranslationAction(Request $request, Language $language, $code)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        if ('en' === $language->getShortcode()) {
            $this->addFlash('notice', "Something's weird");
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
            return $this->redirectToRoute('translation_edit', ['locale' => $language->getShortCode(), 'code' => $code]);
        }

        $translation = new Word();
        $translation->setCode($original->getCode());

        $addTranslationRequest = EditTranslationRequest::fromTranslations($original, $translation);

        $addForm = $this->createForm(EditTranslationFormType::class, $addTranslationRequest);

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $data = $addForm->getData();
            $em = $this->getDoctrine()->getManager();
            $translation->setSentence($data->translatedText);
            $translation->setLanguage($language);
            $translation->setCreated(new DateTime());
            $translation->setAuthor($this->getUser());
            // No need for a description as the English original has one
            $translation->setDescription('');
            $em->persist($translation);
            $em->flush();
            $this->translationModel->removeCacheFile($this->getParameter('kernel.cache_dir'), $language->getShortcode());
            $this->addFlash('notice', 'translation.edit');
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $addForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/translations/{mode}", name="translation_mode",
     *     requirements={"mode": "on|off"}
     * )
     *
     * @param Request $request
     * @param $mode
     *
     * @return RedirectResponse
     */
    public function setTranslationModeAction(Request $request, $mode)
    {
        if ('on' === $mode) {
            $this->addFlash('notice', 'Enabled translation mode');
        } else {
            $this->addFlash('notice', 'Disabled translation mode.');
        }
        $this->get('session')->set('translation_mode', $mode);
        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }
}
