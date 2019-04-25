<?php

namespace App\Controller\Admin;

use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Word;
use App\Form\CustomDataClass\Translation\CreateTranslationRequest;
use App\Form\CustomDataClass\Translation\EditTranslationRequest;
use App\Form\EditTranslationFormType;
use App\Form\TranslationFormType;
use App\Kernel;
use App\Model\TranslationModel;
use App\Pagerfanta\TranslationAdapter;
use App\Repository\MemberRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NonUniqueResultException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TranslationController.
 *
 * @SuppressWarnings(PHPMD)
 */
class TranslationController extends AbstractController
{
    /** @var TranslationModel */
    private $translationModel;

    public function __construct()
    {
        $this->translationModel = new TranslationModel();
    }

    /**
     * @Route("/admin/translations/edit/{locale}/{code}", name="translations_edit",
     *     requirements={"code"=".+"}))
     *
     * Update an existing translation for the locale
     *
     * @param Request $request
     * @param Language $language
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param mixed $code
     *
     * @return Response
     * @throws \Exception
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function editTranslationAction(
        Request $request,
        Language $language,
        KernelInterface $kernel,
        TranslatorInterface $translator,
        $code)
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
            $this->translationModel->removeCacheFile(
                $kernel,
                $language->getShortcode()
            );
            $this->addFlash('notice', $translator->trans('translation.edit'));

            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/translations/create/{locale}/{code}", name="translations_create",
     *     requirements={"code"=".+"}))
     *
     * Creates an English index and the matching translation (if locale != 'en')
     *
     * @param Request $request
     * @param Language $language
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param mixed $code
     *
     * @return Response
     * @throws \Exception
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function createTranslationAction(
        Request $request,
        Language $language,
        KernelInterface $kernel,
        TranslatorInterface $translator,
        $code)
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
            $this->translationModel->removeCacheFile(
                $kernel,
                'en'
            );
            $this->translationModel->removeCacheFile(
                $kernel,
                $language->getShortcode()
            );
            $flashMessage = $translator->trans('flash.added.translatable.item', [ '%code%' => $code ]);
            $this->addFlash('notice', $flashMessage);

            return $this->redirectToRoute('translations');
        }

        return $this->render('admin/translations/create.html.twig', [
            'form' => $createForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/translations/add/{locale}/{code}", name="translation_add",
     *     requirements={"code"=".+"}))
     *
     * Adds a missing translation for an existing english index
     *
     * @param Request $request
     * @param Language $language
     * @param KernelInterface $kernel
     * @param mixed $code
     *
     * @return Response
     * @throws \Exception
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     */
    public function addTranslationAction(Request $request, Language $language, KernelInterface $kernel, TranslatorInterface $translator, $code)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        if ('en' === $language->getShortcode()) {
            $this->addFlash('notice', $translator->trans("flash.translation.weird"));
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
            return $this->redirectToRoute('translations_edit', ['locale' => $language->getShortCode(), 'code' => $code]);
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
            $this->translationModel->removeCacheFile(
                $kernel,
                $language->getShortcode()
            );
            $this->addFlash('notice', $translator->trans('translation.edit'));

            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        return $this->render('admin/translations/edit.html.twig', [
            'form' => $addForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/translations/mode/{mode}", name="translation_mode",
     *     requirements={"mode": "on|off"}
     * )
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param $mode
     *
     * @return RedirectResponse
     */
    public function setTranslationModeAction(Request $request, TranslatorInterface $translator, $mode)
    {
        if ('on' === $mode) {
            $flashId = 'flash.translation.enabled';
        } else {
            $flashId = 'flash.translation.disabled';
        }
        $this->addFlash('notice', $translator->trans($flashId));

        $this->get('session')->set('translation_mode', $mode);
        $referrer = $request->headers->get('referer');

        return $this->redirect($referrer);
    }

    /**
     * @Route("/admin/translations/{code}", name="translations",
     *     defaults={"code":""},
     *     requirements={"code"=".+"})
     *
     * @param Request $request
     * @param string code
     *
     * @return Response
     */
    public function listTranslationsAction(Request $request, $code)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);
        $locale = $this->get('session')->get('_locale');

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
            if ($code <> $newCode) {
                $page=1;
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
            'form' => $form->createView(),
            'code' => $code,
            'translations' => $translations,
        ]);
    }

}
