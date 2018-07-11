<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Word;
use AppBundle\Form\CustomDataClass\Translation\CreateTranslationRequest;
use AppBundle\Form\CustomDataClass\Translation\EditTranslationRequest;
use AppBundle\Form\TranslationFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslationController extends Controller
{
    /**
     * @Route("/admin/translations/{locale}", name="translations",
     *     defaults={"locale": "en"})
     *
     * @param Request $request
     * @return Response
     */
    public function listTranslationsAction(Request $request, $locale)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);
        $locale = $this->get('session')->get('locale');
        $translations = $this->getDoctrine()
            ->getRepository(Word::class)
            ->paginateTranslations($locale, $page, $limit);

        return $this->render(':admin:translations/list.html.twig', [
            'translations' => $translations,
            'is_translation_interface' => true,
        ]);
    }

    /**
     * @Route("/admin/translations/edit/{locale}/{code}", name="translation_edit")
     *
     * Update an existing translation for the locale
     *
     * @param Request $request
     * @param mixed $locale
     * @param mixed $code
     *
     * @return Response
     */
    public function editTranslationAction(Request $request, $locale, $code)
    {
/*        $editTranslationRequest = EditTranslationRequest::fromTranslation($locale, $code);
        $form = $this->createForm(TranslationFormType::class, $editTranslationRequest );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // \todo update entry in word table
            return $this->redirectToRoute('translations', [ 'locale' => $locale ]);
        }*/

        return $this->render(':admin:translations/edit.html.twig', [
            'is_translation_interface' => true,
        ]);
    }

    /**
     * @Route("/admin/translations/create/{code}/{locale}", name="translation_create")
     *
     * Creates an English index and the matching translation (if locale != 'en')
     *
     * @param Request $request
     * @param mixed $locale
     * @param mixed $code
     *
     * @return Response
     */
    public function createTranslationAction(Request $request, $locale, $code)
    {
/*        $createTranslationRequest = new CreateTranslationRequest();
        $createTranslationRequest->wordCode = $code;
        $createTranslationRequest->locale = $locale;

        $form = $this->createForm(TranslationFormType::class, $createTranslationRequest );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // \todo update entry in word table
            return $this->redirectToRoute('translations', [ 'locale' => $locale ]);
        }

        return $this->render(':admin:translations/create.html.twig', [
            'is_translation_interface' => true,
            'form' => $form->createView(),
        ]);
*/
        return $this->render(':admin:translations/create.html.twig', [
            'is_translation_interface' => true,
        ]);
    }

    /**
     * @Route("/admin/translations/add/{locale}/{code}", name="translation_add")
     *
     * Adds a missing translation for an existing english index
     *
     * @param Request $request
     * @param mixed $locale
     * @param mixed $code
     *
     * @return Response
     */
    public function addTranslationAction(Request $request, $locale, $code)
    {
        return $this->render(':admin:translations/add.html.twig', [
            'is_translation_interface' => true,
        ]);
    }
}
