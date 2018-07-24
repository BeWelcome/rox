<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Word;
use AppBundle\Form\CustomDataClass\TranslationRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
        $translations = $this->getDoctrine()
            ->getRepository(Word::class)
            ->paginateTranslations($locale, $page, $limit);

        return $this->render(':admin:translations/list.html.twig', [
            'translations' => $translations,
        ]);
    }

    /**
     * @Route("/admin/translations/{locale}/{code}/edit", name="translation_edit")
     *
     * @param Request $request
     * @param mixed   $locale
     * @param mixed   $code
     *
     * @return Response
     */
    public function editTranslationAction(Request $request, $locale, $code)
    {
        $translationRepository = $this->getDoctrine()
            ->getRepository(Word::class);
        $original = $translationRepository->findOneBy([
            'code' => $code,
            'ShortCode' => 'en',
        ]);
        $translation = $translationRepository->findOneBy([
           'code' => $code,
           'ShortCode' => $locale,
        ]);
        $translationRequest = TranslationRequest::fromTranslations($original, $translation);

        $editForm = $this->createForm(TranslationFormType::class, $translationRequest);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
        }

        return $this->render(':admin:translations/edit.html.twig', [
            'form' => $editForm->createView(),
            'locale' => $locale,
            'code' => $code,
        ]);
    }

    /**
     * @Route("/admin/translations/{locale}/{code}/add", name="translation_create")
     *
     * Creates an English index and the matching translation (if locale != 'en')
     *
     * @param mixed $locale
     * @param mixed $code
     *
     * @return Response
     */
    public function createTranslationAction($locale, $code)
    {
        return $this->render(':admin:translations/add.html.twig', [
            'locale' => $locale,
            'code' => $code,
        ]);
    }

    /**
     * @Route("/admin/translations/{locale}/{code}/create", name="translation_add")
     *
     * Adds a missing translation for an existing english index
     *
     * @param Request $request
     * @param mixed   $locale
     * @param mixed   $code
     *
     * @return Response
     */
    public function addTranslationAction(Request $request, $locale, $code)
    {
        $translationRepository = $this->getDoctrine()
            ->getRepository(Word::class);
        $original = $translationRepository->findOneBy([
            'code' => $code,
            'ShortCode' => 'en',
        ]);
        $translation = new Word();
        $translation->setShortCode($locale);
        $translation->setCode($code);

        $translationRequest = TranslationRequest::fromTranslations($original, $translation);

        $addForm = $this->createFormBuilder()
            ->add('sortOrder', HiddenType::class)
            ->getForm();

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
        }

        return $this->render(':admin:translations/create.html.twig', [
            'form' => $addForm->createView(),
            'locale' => $locale,
            'code' => $code,
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
